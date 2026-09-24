<?php

namespace PushNotifications\Command;

use Core;
use Page;
use Events;
use WebApp\Log\PushNotificationLog;
use WebApp\Entity\CustomNotification;
use Doctrine\ORM\EntityManagerInterface;
use WebApp\Entity\ScheduledNotification;
use Symfony\Component\EventDispatcher\GenericEvent;
use WebApp\Command\SendScheduledNotificationCommand;
use Concrete\Core\Command\Task\Output\OutputAwareTrait;
use Concrete\Core\Command\Task\Output\OutputAwareInterface;

class SendScheduledNotificationCommandHandler implements OutputAwareInterface
{
    use OutputAwareTrait;

    public function __invoke(SendScheduledNotificationCommand $command)
    {
        $logger = Core::make(PushNotificationLog::class)->getLogger();

        $type = strtolower($command->getType());
        switch ($type) {
            case 'news':
                $page = Page::getByID($command->getReferenceId());
                if (is_object($page)) {
                    // publish event to send notification
                    $event = new GenericEvent();
                    $event->setArgument('page', $page);
                    Events::dispatch('send_scheduled_news', $event);

                    // remove scheduled notification key to avoid repeats
                    $n = ScheduledNotification::getByID($command->getNotificationId());
                    if (is_object($n)) {
                        $em = Core::make(EntityManagerInterface::class);
                        $em->remove($n);
                        $em->flush($n);
                    } else {
                        $logger->addError(t('News notification not sent, article not found (Reference ID: %s)', $command->getReferenceId()));
                    }
                }
                break;
            case 'custom':
                $custom = CustomNotification::getByID($command->getReferenceId());
                if (is_object($custom)) {
                    $event = new GenericEvent();
                    $event->setArgument('notification', $custom);
                    Events::dispatch('send_custom_notification', $event);

                    // remove custom notification key to avoid repeats
                    $em = Core::make(EntityManagerInterface::class);
                    $em->remove($custom);
                    $em->flush($custom);
                } else {
                    $logger->addError(t('Custom notification not sent, notification not found (Reference ID: %s)', $command->getReferenceId()));
                }
                break;
            default:
                $logger->addDebug(t('Unknown type, notification not sent (Reference ID: %s)', $command->getReferenceId()));
                break;
        }
    }
}
