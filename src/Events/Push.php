<?php

namespace WebApp\Events;

use Core;
use File;
use DateTime;
use Exception;
use Monolog\Logger;
use WebApp\Entity\PushKey;
use Concrete\Core\Page\Page;
use Minishlink\WebPush\WebPush;
use WebApp\Log\PushNotificationLog;
use GuzzleHttp\Client as HttpClient;
use Minishlink\WebPush\Subscription;
use ClassKit\Environment\Environment;
use WebApp\Entity\CustomNotification;
use Doctrine\ORM\EntityManagerInterface;
use WebApp\Entity\ScheduledNotification;
use Concrete\Core\Package\PackageService;
use GuzzleHttp\Exception\RequestException;
use WebApp\Response\PushNotificationError;
use WebApp\Response\PushNotificationResponse;
use Concrete\Core\Page\Collection\Version\Event as PageVersionEvent;
use WebApp\Events\Subscription as WebAppSubscription;
use Concrete\Core\Entity\Page\Template as PageTemplate;
use WebApp\Search\ItemList\PushSubscription\PushSubscriptions as SubscriptionList;

class Push
{
    protected Logger $logger;

    public function __construct()
    {
        $this->logger = Core::make(PushNotificationLog::class)->getLogger();
    }

    protected function getVapidKeys()
    {
        $pkg = Core::make(PackageService::class)->getByHandle('web_app');
        $config = $pkg->getFileConfig();
        $vapidKeys = PushKey::getByID($config->get('push_notifications.key_id'));
        return $vapidKeys;
    }

    protected function getSubject()
    {
        $pkg = Core::make(PackageService::class)->getByHandle('web_app');
        $config = $pkg->getFileConfig();
        return $config->get('push_notifications.subject');
    }

    public function broadcast()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $body = json_decode(file_get_contents('php://input'));

        $this->logger->addDebug(json_encode($body));

        $response = [
            'method' => $method,
            'request' => json_encode($body),
        ];

        $vapidKeys = $this->getVapidKeys();
        if (!$vapidKeys) {
            throw new Exception(t('No VAPID keys'));
        }

        $this->logger->addDebug(json_encode($vapidKeys));

        if ($body) {
            $notifications = [];
            $sl = new SubscriptionList();
            $subscriptions = $sl->getResults();

            if (count($subscriptions) > 0) {
                foreach ($subscriptions as $sub) {
                    $subscription = Subscription::create(json_decode($sub->getSubscription(), true));
                    $notifications[] = [
                        'subscription' => $subscription,
                        'payload' => $body,
                    ];
                }
            }
        }

        $auth = [
            'VAPID' => [
                'subject' => 'mailto:' . $this->getSubject() ?? 'devrow@limegreentangerine.co.uk',
                'publicKey' => $vapidKeys->getPublicKey(),
                'privateKey' => $vapidKeys->getPrivateKey(),
            ],
        ];

        $webPush = new WebPush($auth);
        $webPush->setReuseVAPIDHeaders(true);

        if (isset($notifications) && count($notifications) > 0) {
            foreach ($notifications as $notification) {
                $webPush->queueNotification(
                    $notification['subscription'],
                    json_encode($notification['payload']),
                );
            }
        }

        $success = [];
        $errors = [];
        $toRemove = [];
        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            $this->logger->addDebug(json_encode($report->getRequest()));

            if (!$report->isSuccess()) {
                $errors[] = "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
            } else {
                $success[] = "[v] Message sent successfully for subscription {$endpoint}.";
            }

            if ($report->getResponse()->getStatusCode() === 410) {
                $toRemove[] = $endpoint;
            }
        }

        foreach ($toRemove as $endpoint) {
            WebAppSubscription::unsubscribe($endpoint);
        }

        $response['success'] = $success;
        $response['errors'] = $errors;

        $this->logger->addDebug(json_encode($response));

        echo json_encode($response);
        exit;
    }

    public static function getIcon()
    {
        $pkg = Core::make(PackageService::class)->getByHandle('web_app');
        $config = $pkg->getFileConfig();
        $iconFile = File::getByID($config->get('web_app.iconFile'));

        if ($iconFile) {
            $iconFileVersion = $iconFile->getApprovedVersion();
            $appIcon = $iconFileVersion->getURL();
        } else {
            $appIcon = false;
        }

        return $appIcon;
    }

    /**
     * Send Push Notification for news article publish
     */
    public static function sendPageNotification(Page $page)
    {
        $payload = [
            'topic' => $page->getPageTypeHandle(),
            'title' => $page->getCollectionName(),
            'body' => $page->getCollectionDescription(),
            'data' => [
                'link_url' => $page->getCollectionLink(),
            ],
        ];

        if ($icon = static::getIcon()) {
            $payload['icon'] = $icon;
        }

        static::makeRequest('/push/broadcast', $payload);
    }

    /**
     * Send Custom Notification
     */
    public static function sendCustomNotification(CustomNotification $notification)
    {
        $payload = [
            'topic' => t('Custom'),
            'title' => $notification->getTitle(),
            'body' => $notification->getDescription(),
            'data' => [
                'link_url' => $notification->getLinkUrl(),
            ],
        ];

        if ($icon = static::getIcon()) {
            $payload['icon'] = $icon;
        }

        try {
            static::makeRequest('/push/broadcast', $payload);

            $em = Core::make(EntityManagerInterface::class);
            $notification->setSentAt(new DateTime());
            $em->persist($notification);
            $em->flush();
        } catch (PushNotificationError $e) {
            Core::make(PushNotificationLog::class)
                ->getLogger()
                ->addDebug(t('(Code: %s) %s', $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * Make Request
     *
     * @param  string                   $apiUrl
     * @param  array                    $data
     * @return PushNotificationResponse
     */
    public static function makeRequest(string $apiUrl, array $data = []): PushNotificationResponse
    {
        $client = new HttpClient();
        $fullUrl = \URL::to($apiUrl);

        Core::make(PushNotificationLog::class)
            ->getLogger()
            ->addDebug($fullUrl);

        try {
            $options = [
                'debug' => false,
                'headers' => [
                    'Cache-Control' => 'no-cache',
                    'Content-Type' => 'application/json',
                ],
                'idn_conversion' => false,
            ];

            if (!empty($data)) {
                $options['body'] = json_encode($data);
            }

            if (Environment::isLocal()) {
                $options['verify'] = false;

                Core::make(PushNotificationLog::class)
                    ->getLogger()
                    ->addDebug(json_encode($options));
            }

            $res = $client->request('post', $fullUrl->__toString(), $options);
        } catch (RequestException $e) {
            throw new PushNotificationError($e->getMessage(), $e->getRequest(), $e->getResponse(), $e->getPrevious(), $e->getHandlerContext());
        }

        $resp = new PushNotificationResponse();
        $resp->setUrl($fullUrl);
        $resp->setStatusCode($res->getStatusCode());
        $resp->setBody($res->getBody());

        return $resp;
    }

    public static function schedulePublishNotification(PageVersionEvent $event)
    {
        // helpers
        $pkg = Core::make(PackageService::class)->getByHandle('web_app');
        $config = $pkg->getFileConfig();
        $types = explode(',', $config->get('push_notifications.publish'));
        $logger = Core::make(PushNotificationLog::class)->getLogger();
        $em = Core::make(EntityManagerInterface::class);

        // objects
        $version = $event->getCollectionVersionObject();
        $page = Page::getByID($version->getCollectionID());
        $now = new DateTime();
        $publishDate = new DateTime($version->getPublishDate());

        if ($page instanceof Page && !$page->isError()) {
            $pageType = $page->getPageTypeObject();

            if (in_array($pageType->getPageTypeID(), $types)) {
                $pageTemplate = $pageType->getPageTypeDefaultPageTemplateObject();

                if ($page->getPageTemplateHandle() === $pageTemplate->getPageTemplateHandle()) {
                    $logger->addDebug('Push::schedulePublishNotification() -> page template is the default');
                    $notification = ScheduledNotification::getByColumnAndValue('referenceId', $version->getCollectionID());

                    $isMostRecentVersion = $version->isMostRecent();
                    $logger->addDebug(json_encode([ 'isMostRecentVersion' => $isMostRecentVersion ]));

                    if ($isMostRecentVersion) {
                        if ($publishDate->format('U') > $now->format('U')) {
                            if ($notification) {
                                /**
                                 * If there is already a notification scheduled
                                 */
                                $logger->addDebug(t('Updated scheduled notification for %s', $page->getCollectionName()));
                                $notification->setSendDate($publishDate);
                            } else {
                                /**
                                 * No existing notifications and pages public date is in the future, schedule it
                                 */
                                $logger->addDebug(t('New scheduled notification for %s', $page->getCollectionName()));
                                $notification = new ScheduledNotification();
                                $notification->setType($pageType->getPageTypeID());
                                $notification->setReferenceId($page->getCollectionID());
                                $notification->setSendDate($publishDate);
                            }

                            $em->persist($notification);
                            $em->flush();
                        } else {
                            /**
                             * Page date is now or in the past send it now
                             */
                            Push::sendPageNotification($page);
                        }
                    } else {
                        $logger->addDebug('Push::schedulePublishNotification() -> page version is not the most recent or a republication, no notification sent or scheduled');
                        // NOTE: This is a republication of an old page version, for now let's do nothing but it could be useful in the future.
                    }
                }
            } else {
                $logger->addDebug(t('Push::schedulePublishNotification() -> page type (%s) not cleared for push notifications', $pageType->getPageTypeHandle()));
            }
        }
    }
}
