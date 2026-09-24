<?php

namespace WebApp\Search\Column\Set\CustomNotification;

use Concrete\Core\Search\Column\Set;
use Concrete\Core\Search\Column\Column;

class CustomNotifications extends Set
{
    public function __construct()
    {
        $this->addColumn(new Column(
            'pcn.id',
            t('ID'),
            'getID',
            true,
        ));

        $this->addColumn(new Column(
            'pcn.title',
            t('Title'),
            'getTitle',
            false,
        ));

        $this->addColumn(new Column(
            'pcn.description',
            t('Description'),
            'getDescription',
            false,
        ));

        $this->addColumn(new Column(
            'pcn.sendDate',
            t('Send Date'),
            'getSendDateString',
            true,
        ));

        $defaultSortColumn = $this->getColumnByKey('pcn.sendDate');
        $this->setDefaultSortColumn($defaultSortColumn, 'desc');
    }
}
