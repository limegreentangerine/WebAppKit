<?php

namespace WebApp\Events;

use Core;
use WebApp\Entity\PushSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use WebApp\Log\PushNotificationLog;

class Subscription
{
    protected Logger $logger;

    public function __construct()
    {
        $this->logger = Core::make(PushNotificationLog::class)->getLogger();
    }

    public function subscribe()
    {
        $success = false;
        $message = '';
        $method = $_SERVER['REQUEST_METHOD'];
        $body = json_decode(file_get_contents('php://input'));

        $response = [
            'method' => $method,
            'request' => json_encode($body),
        ];

        $subscription = PushSubscription::getByColumnAndValue('endpoint', $body->endpoint);
        if ($subscription) {
            $success = false;
            $message = t('Client already subscribed');
        } else {
            $em = Core::make(EntityManagerInterface::class);
            $subscription = new PushSubscription();
            $subscription->setEndpoint($body->endpoint);
            $subscription->setSubscription($body);
            $em->persist($subscription);
            $em->flush();

            $success = true;
            $message = t('Client subscribed successfully');
        }

        $response['success'] = $success;
        $response['message'] = $message;

        echo json_encode($response);
        exit;
    }

    public static function unsubscribe(string $endpoint)
    {
        $em = Core::make(EntityManagerInterface::class);
        $subscription = PushSubscription::getByColumnAndValue('endpoint', $endpoint);
        $em->remove($subscription);
        $em->flush($subscription);

        Core::make(PushNotificationLog::class)
            ->getLogger()
            ->addDebug(t('%s removed', $endpoint));
    }

    /**
     * Get the value of logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
