<?php

namespace WebApp\Search\ItemList\ScheduledNotification;

use DateTime;
use Pagerfanta\Doctrine\DBAL\QueryAdapter;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Search\ItemList\Database\ItemList;
use Concrete\Core\Application\ApplicationAwareInterface;
use WebApp\Entity\ScheduledNotification as ScheduledNotificationEntity;

class ScheduledNotifications extends ItemList implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    protected $autoSortColumns = [
        'psn.sendDate',
    ];

    protected function createPaginationObject()
    {
        $adapter = new QueryAdapter(
            $this->deliverQueryObject(),
            function (\Doctrine\DBAL\Query\QueryBuilder $query) {
                $query
                    ->resetQueryParts(['groupBy', 'orderBy'])
                    ->select('count(distinct psn.id)')
                    ->setMaxResults(1);
            },
        );
        $pagination = new Pagination($this, $adapter);

        return $pagination;
    }

    public function createQuery()
    {
        $this->query->select('psn.id')
            ->from('pushScheduled', 'psn')
        ;
    }

    public function filterByDate(DateTime $date, $comparison = '<=')
    {
        $this->query->andWhere('psn.sendDate ' . $comparison . ' "' . $date->format('Y-m-d H:i:s') . '"');
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();
        $query
            ->resetQueryParts(['groupBy', 'orderBy'])
            ->select('count(distinct psn.id)')
            ->setMaxResults(1);
        $result = $query->execute()->fetchOne();

        return (int) $result;
    }

    public function getResult(mixed $queryRow)
    {
        return ScheduledNotificationEntity::getByID($queryRow['id']);
    }
}
