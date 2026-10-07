<?php

namespace WebApp\Tests;

use DateTime;
use WebApp\Entity\PushKey;
use PHPUnit\Framework\TestCase;
use WebApp\Entity\PushSubscription;
use WebApp\Entity\CustomNotification;
use WebApp\Entity\ScheduledNotification;

class EntityTest extends TestCase
{
    public function testPushKeyAccessorsAreFluentAndPreserveValues(): void
    {
        $pushKey = new PushKey();

        $this->assertSame($pushKey, $pushKey->setPublicKey('public-key'));
        $this->assertSame($pushKey, $pushKey->setPrivateKey('private-key'));
        $this->assertSame('public-key', $pushKey->getPublicKey());
        $this->assertSame('private-key', $pushKey->getPrivateKey());
    }

    public function testPushSubscriptionPreservesJsonForRoundTripDecoding(): void
    {
        $subscriptionJson = '{"endpoint":"https://push.example/subscription","keys":{"p256dh":"key"}}';
        $subscription = new PushSubscription();

        $this->assertSame($subscription, $subscription->setEndpoint('https://push.example/subscription'));
        $this->assertSame($subscription, $subscription->setSubscription($subscriptionJson));
        $this->assertSame('https://push.example/subscription', $subscription->getEndpoint());
        $this->assertSame($subscriptionJson, $subscription->getSubscription());
        $this->assertSame(
            ['endpoint' => 'https://push.example/subscription', 'keys' => ['p256dh' => 'key']],
            json_decode($subscription->getSubscription(), true, 512, JSON_THROW_ON_ERROR),
        );
    }

    public function testCustomNotificationAccessorsAndDateFormatting(): void
    {
        $sendDate = new DateTime('2026-10-07 15:30:00');
        $sentAt = new DateTime('2026-10-07 16:45:00');
        $notification = new CustomNotification();

        $this->assertSame($notification, $notification->setTitle('Release'));
        $this->assertSame($notification, $notification->setDescription('New release available'));
        $this->assertSame($notification, $notification->setLink(42));
        $this->assertSame($notification, $notification->setSendDate($sendDate));
        $this->assertSame($notification, $notification->setSentAt($sentAt));
        $this->assertSame('Release', $notification->getTitle());
        $this->assertSame('New release available', $notification->getDescription());
        $this->assertSame(42, $notification->getLink());
        $this->assertSame($sendDate, $notification->getSendDate());
        $this->assertSame('07/10/2026 15:30', $notification->getSendDateString());
        $this->assertSame($sentAt, $notification->getSentAt());
        $this->assertSame('07/10/2026 16:45', $notification->getSendAtString());
    }

    public function testScheduledNotificationAccessorsAndDateFormatting(): void
    {
        $sendDate = new DateTime('2026-10-07 15:30:00');
        $sentAt = new DateTime('2026-10-07 16:45:00');
        $notification = new ScheduledNotification();

        $this->assertSame($notification, $notification->setType(3));
        $this->assertSame($notification, $notification->setReferenceId(42));
        $this->assertSame($notification, $notification->setSendDate($sendDate));
        $this->assertSame($notification, $notification->setSentAt($sentAt));
        $this->assertSame(3, $notification->getType());
        $this->assertSame(42, $notification->getReferenceId());
        $this->assertSame($sendDate, $notification->getSendDate());
        $this->assertSame('2026-10-07 15:30:00', $notification->getSendDate('Y-m-d H:i:s'));
        $this->assertSame('07/10/2026 15:30', $notification->getSendDateString());
        $this->assertSame($sentAt, $notification->getSendAt());
        $this->assertSame('07/10/2026 16:45', $notification->getSendAtString());
    }
}
