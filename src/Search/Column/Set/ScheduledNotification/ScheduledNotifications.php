<?php

namespace WebApp\Search\Column\Set\ScheduledNotification;

use Concrete\Core\Search\Column\Set;
use Concrete\Core\Search\Column\Column;

class ScheduledNotifications extends Set
{
    public function __construct()
    {
        $this->addColumn(new Column(
            'psn.id',
            t('ID'),
            'getID',
            true,
        ));

        $this->addColumn(new Column(
            'psn.type',
            t('Type'),
            'getType',
            false,
        ));

        $this->addColumn(new Column(
            '',
            t('Link'),
            'getLinkUrl',
            false,
        ));

        $this->addColumn(new Column(
            'psn.sendDate',
            t('Send Date'),
            'getSendDateString',
            true,
        ));

        $defaultSortColumn = $this->getColumnByKey('psn.sendDate');
        $this->setDefaultSortColumn($defaultSortColumn, 'desc');
    }
}
