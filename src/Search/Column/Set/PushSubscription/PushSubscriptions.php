<?php

namespace WebApp\Search\Column\Set\PushSubscription;

use Concrete\Core\Search\Column\Set;
use Concrete\Core\Search\Column\Column;

class PushSubscriptions extends Set
{
    public function __construct()
    {
        $this->addColumn(new Column(
            'ps.id',
            t('ID'),
            'getID',
            true,
        ));

        $this->addColumn(new Column(
            'ps.endpoint',
            t('Endpoint'),
            'getEndpoint',
            false,
        ));

        $this->addColumn(new Column(
            'ps.subscription',
            t('Subscription'),
            'getSubscription',
            false,
        ));

        $defaultSortColumn = $this->getColumnByKey('ps.endpoint');
        $this->setDefaultSortColumn($defaultSortColumn, 'desc');
    }
}
