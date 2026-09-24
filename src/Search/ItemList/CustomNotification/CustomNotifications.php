<?php

namespace WebApp\Search\ItemList\CustomNotification;

use DateTime;
use Pagerfanta\Doctrine\DBAL\QueryAdapter;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Search\ItemList\Database\ItemList;
use Concrete\Core\Application\ApplicationAwareInterface;
use WebApp\Entity\CustomNotification as CustomNotificationEntity;

class CustomNotifications extends ItemList implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    protected $autoSortColumns = [
        'pcn.sendDate',
    ];

    protected function createPaginationObject()
    {
        $adapter = new QueryAdapter(
            $this->deliverQueryObject(),
            function (\Doctrine\DBAL\Query\QueryBuilder $query) {
                $query
                    ->resetQueryParts(['groupBy', 'orderBy'])
                    ->select('count(distinct pcn.id)')
                    ->setMaxResults(1);
            },
        );
        $pagination = new Pagination($this, $adapter);

        return $pagination;
    }

    public function createQuery()
    {
        $this->query->select('pcn.id')
            ->from('pushCustomNotifications', 'pcn')
        ;
    }

    public function filterByDate(DateTime $date, $comparison = '<=')
    {
        $this->query->andWhere('pcn.sendDate ' . $comparison . ' "' . $date->format('Y-m-d H:i:s') . '"');
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();
        $query
            ->resetQueryParts(['groupBy', 'orderBy'])
            ->select('count(distinct pcn.id)')
            ->setMaxResults(1);
        $result = $query->execute()->fetchOne();

        return (int) $result;
    }

    public function getResult(mixed $queryRow)
    {
        return CustomNotificationEntity::getByID($queryRow['id']);
    }
}
