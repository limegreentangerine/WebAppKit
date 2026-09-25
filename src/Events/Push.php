<?php

namespace WebApp\Events;

use Core;
use File;
use Exception;
use Monolog\Logger;
use WebApp\Entity\PushKey;
use Concrete\Core\Page\Page;
use GuzzleHttp\TransferStats;
use Minishlink\WebPush\WebPush;
use Illuminate\Support\Facades\URL;
use WebApp\Log\PushNotificationLog;
use GuzzleHttp\Client as HttpClient;
use Minishlink\WebPush\Subscription;
use WebApp\Entity\CustomNotification;
use Concrete\Core\Package\PackageService;
use GuzzleHttp\Exception\RequestException;
use Concrete\Core\Support\Facade\Application;
use WebApp\Events\Subscription as WebAppSubscription;
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

    public function broadcast()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $body = json_decode(file_get_contents('php://input'));

        $response = [
            'method' => $method,
            'request' => json_encode($body),
        ];

        $vapidKeys = $this->getVapidKeys();
        if (!$vapidKeys) {
            throw new Exception(t('No VAPID keys'));
        }

        if ($body) {
            $notifications = [];
            $sl = new SubscriptionList();
            $subscriptions = $sl->get();

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
                'subject' => 'mailto:devrow@limegreentangerine.co.uk',
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
        $app = Application::getFacadeApplication();
        $site = $app->make('site')->getSite();
        $config = $site->getConfigRepository();
        $appleIconFID = (int) $config->get('misc.iphone_home_screen_thumbnail_fid');
        $appleIconFile = File::getByID($appleIconFID);

        if ($appleIconFile) {
            $appleIconFileVersion = $appleIconFile->getApprovedVersion();
            $appIcon = $appleIconFileVersion->getURL();
        } else {
            $appIcon = false;
        }

        return $appIcon;
    }

    /**
     * Send Push Notification for news article publish
     *
     * @param Page $page
     */
    public static function sendNewsNotification($page)
    {
        $payload = [
            'topic' => t('News'),
            'title' => $page->getCollectionName(),
            'body' => $page->getCollectionDescription(),
            'data' => [
                'link_url' => $page->getCollectionLink(),
            ],
        ];

        if ($icon = self::getIcon()) {
            $payload['icon'] = $icon;
        }

        self::makeRequest('/push/broadcast', $payload);
    }

    /**
     * Send Push Notification for Pre Game Information
     *
     * @param string $title
     * @param string $description
     * @param string $url
     */
    public static function sendPreGameNotification($title, $description, $url)
    {
        $payload = [
            'topic' => t('PreGame'),
            'title' => $title,
            'body' => $description,
            'data' => [
                'link_url' => $url,
            ],
        ];

        if ($icon = self::getIcon()) {
            $payload['icon'] = $icon;
        }

        Core::make(PushNotificationLog::class)
            ->getLogger()
            ->addDebug(json_encode($payload));

        $response = self::makeRequest('/push/broadcast', $payload);
        Core::make(PushNotificationLog::class)
            ->getLogger()
            ->addDebug(json_encode($response->getBody()));
    }

    /**
     * Send Push Notification for Game Report
     *
     * @param string $title
     * @param string $description
     * @param string $url
     */
    public static function sendGameReportNotification($title, $description, $url)
    {
        $payload = [
            'topic' => t('GameReport'),
            'title' => $title,
            'body' => $description,
            'data' => [
                'link_url' => $url,
            ],
        ];

        if ($icon = self::getIcon()) {
            $payload['icon'] = $icon;
        }

        Core::make(PushNotificationLog::class)
            ->getLogger()
            ->addDebug(json_encode($payload));

        $response = self::makeRequest('/push/broadcast', $payload);
        Core::make(PushNotificationLog::class)
            ->getLogger()
            ->addDebug(json_encode($response->getBody()));
    }

    /**
     * Send Custom Notification
     *
     * @param CustomNotification $notification
     */
    public static function sendCustomNotification($notification)
    {
        $payload = [
            'topic' => t('Custom'),
            'title' => $notification->getTitle(),
            'body' => $notification->getDescription(),
            'data' => [
                'link_url' => $notification->getLinkUrl(),
            ],
        ];

        if ($icon = self::getIcon()) {
            $payload['icon'] = $icon;
        }

        Core::make(PushNotificationLog::class)
            ->getLogger()
            ->addDebug(json_encode($payload));

        $response = self::makeRequest('/push/broadcast', $payload);
        Core::make(PushNotificationLog::class)
            ->getLogger()
            ->addDebug(json_encode($response->getBody()));
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
        $fullUrl = URL::to($apiUrl);

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
                'on_stats' => function (TransferStats $stats) use (&$url) {
                    $url = $stats->getEffectiveUri();
                },
            ];

            if (!empty($data)) {
                $options['body'] = json_encode($data);
            }

            $res = $client->request('post', $fullUrl, $options);
        } catch (RequestException $e) {
            $resp = new PushNotificationResponse();
            $resp->setUrl($fullUrl);
            $resp->setStatusCode($e->getResponse()->getStatusCode());
            $resp->setBody($e->getResponse()->getBody());
            return $resp;
        }

        $resp = new PushNotificationResponse();
        $resp->setUrl($fullUrl);
        $resp->setStatusCode($res->getStatusCode());
        $resp->setBody($res->getBody());

        return $resp;
    }
}
