<?php

namespace WebApp\Search\ItemList\PushSubscription;

use Pagerfanta\Doctrine\DBAL\QueryAdapter;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Search\ItemList\Database\ItemList;
use Concrete\Core\Application\ApplicationAwareInterface;
use WebApp\Entity\PushSubscription as PushSubscriptionEntity;

class PushSubscriptions extends ItemList implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    protected $autoSortColumns = [
        'ps.endpoint',
    ];

    protected function createPaginationObject()
    {
        $adapter = new QueryAdapter(
            $this->deliverQueryObject(),
            function (\Doctrine\DBAL\Query\QueryBuilder $query) {
                $query
                    ->resetQueryParts(['groupBy', 'orderBy'])
                    ->select('count(distinct ps.id)')
                    ->setMaxResults(1);
            },
        );
        $pagination = new Pagination($this, $adapter);

        return $pagination;
    }

    public function createQuery()
    {
        $this->query->select('ps.id')
            ->from('pushSubscriptions', 'ps')
        ;
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();
        $query
            ->resetQueryParts(['groupBy', 'orderBy'])
            ->select('count(distinct ps.id)')
            ->setMaxResults(1);
        $result = $query->execute()->fetchOne();

        return (int) $result;
    }

    public function getResult(mixed $queryRow)
    {
        return PushSubscriptionEntity::getByID($queryRow['id']);
    }
}
