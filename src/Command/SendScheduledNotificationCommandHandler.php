<?php

namespace WebApp\Command;

use Core;
use Events;
use WebApp\Log\PushNotificationLog;
use WebApp\Entity\CustomNotification;
use WebApp\Entity\ScheduledNotification;
use Concrete\Core\Package\PackageService;
use Symfony\Component\EventDispatcher\GenericEvent;
use Concrete\Core\Command\Task\Output\OutputAwareTrait;
use Concrete\Core\Command\Task\Output\OutputAwareInterface;

class SendScheduledNotificationCommandHandler implements OutputAwareInterface
{
    use OutputAwareTrait;

    public function __invoke(SendScheduledNotificationCommand $command)
    {
        $logger = Core::make(PushNotificationLog::class)->getLogger();
        $type = strtolower($command->getType());
        $logger->addDebug($type);

        $pkg = Core::make(PackageService::class)->getByHandle('web_app');
        $config = $pkg->getFileConfig();
        $types = explode(',', $config->get('push_notifications.publish'));

        match (true) {
            $type === 'custom' => (function () use ($logger, $command) {
                $custom = CustomNotification::getByID($command->getReferenceId()); // HACK: use reference ID here so we don't have to build more jobs and classes
                if ($custom instanceof CustomNotification) {
                    $event = new GenericEvent();
                    $event->setArgument('notification', $custom);
                    Events::dispatch('send_custom_notification', $event);
                } else {
                    $logger->addError(t('Custom notification not sent, notification not found (Reference ID: %s)', $command->getReferenceId()));
                }
            })(),
            in_array($type, $types, true) => (function () use ($logger, $command) {
                $scheduled = ScheduledNotification::getByID($command->getNotificationId());

                if ($scheduled instanceof ScheduledNotification) {
                    $event = new GenericEvent();
                    $event->setArgument('notification', $scheduled);
                    Events::dispatch('send_sheduled_notification', $event);
                } else {
                    $logger->addError(t('Custom notification not sent, notification not found (Reference ID: %s)', $command->getReferenceId()));
                }
            })(),
            default => (function () use ($logger, $command) {
                $logger->addDebug(t('Unknown type, notification not sent (Reference ID: %s)', $command->getReferenceId()));
            })()
        };
    }
}
