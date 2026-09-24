<?php

namespace WebApp\Command\Task\Controller;

use DateTime;
use Concrete\Core\Command\Batch\Batch;
use Concrete\Core\Command\Task\TaskInterface;
use Concrete\Core\Command\Task\Input\InputInterface;
use WebApp\Command\SendScheduledNotificationCommand;
use Concrete\Core\Command\Task\Controller\AbstractController;
use Concrete\Core\Command\Task\Runner\BatchProcessTaskRunner;
use WebApp\Search\ItemList\CustomNotification\CustomNotifications as CustomNotificationList;
use WebApp\Search\ItemList\ScheduledNotification\ScheduledNotifications as ScheduledNotificationList;

class SendScheduledNotifications extends AbstractController
{
    public function getName(): string
    {
        return t('Send Scheduled Notifications');
    }

    public function getDescription(): string
    {
        return t('Send any push notifications stored in the system for future publication.');
    }

    public function getTaskRunner(TaskInterface $task, InputInterface $input): BatchProcessTaskRunner
    {
        $batch = Batch::create();

        $now = new DateTime();
        $snl = new ScheduledNotificationList();
        $snl->filterByDate($now);
        $notifications = $snl->get();

        foreach ($notifications as $n) {
            $batch->add(new SendScheduledNotificationCommand($n->getID(), $n->getType(), $n->getReferenceId()));
        }

        $cnl = new CustomNotificationList();
        $cnl->filterByDate($now);
        $custom = $cnl->get();

        foreach ($custom as $c) {
            $batch->add(new SendScheduledNotificationCommand($c->getID(), 'custom', $c->getID()));
        }

        return new BatchProcessTaskRunner($task, $batch, $input, t('Sending notifcations...'));
    }
}
