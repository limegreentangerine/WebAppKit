<?php

namespace Concrete\Package\WebApp\Controller\Search\Subscribers;

use Concrete\Core\Search\StickyRequest;
use Concrete\Core\Controller\AbstractController;
use WebApp\Search\ItemList\PushSubscription\PushSubscriptions as SearchList;
use WebApp\Search\Result\PushSubscription\PushSubscriptions as SearchResult;
use WebApp\Search\Column\Set\PushSubscription\PushSubscriptions as ColumnSet;

class Subscribers extends AbstractController
{
    private StickyRequest $stickyRequest;

    private SearchList $searchList;

    private SearchResult $searchResult;

    protected function getSearchList(): ?SearchList
    {
        $this->searchList = $this->app->make(SearchList::class, [$this->getStickyRequest()]);
        return $this->searchList;
    }

    public function getStickyRequest(): ?StickyRequest
    {
        $this->stickyRequest = new StickyRequest('pn.subscribers');
        return $this->stickyRequest;
    }

    /**
     * @return array<int>
     */
    public function getAllowedPaginationSizes()
    {
        return [
            10,
            20,
            50,
            100,
        ];
    }

    public function getDefaultPaginationSize(): int
    {
        $allAllowed = $this->getAllowedPaginationSizes();

        return $allAllowed[1];
    }

    public function search(bool $reset = false): void
    {
        $stickyRequest = $this->getStickyRequest();
        $searchList = $this->getSearchList();

        if ($reset) {
            $stickyRequest->resetSearchRequest();
        }

        $req = $stickyRequest->getSearchRequest();

        $columnSet = new ColumnSet();

        if (!$searchList->getActiveSortColumn()) {
            $sortColumn = $columnSet->getDefaultSortColumn();
            $searchList->sanitizedSortBy($sortColumn->getColumnKey(), $sortColumn->getColumnDefaultSortDirection());
        }

        $vnumbers = $this->app->make('helper/validation/numbers');
        $req = $stickyRequest->getSearchRequest();

        // $q = isset($req['name']) ? $req['name'] : null;
        // if (is_string($q) && $q !== '') {
        //     $searchList->filterByName($q);
        // }

        $paginationSize = null;
        $q = isset($req['num_results']) ? $req['num_results'] : null;
        if ($q && $vnumbers->integer($q)) {
            $q = (int) $q;
            $paginationSizes = $this->getAllowedPaginationSizes();
            if (in_array($q, $paginationSizes, true)) {
                $paginationSize = (int) $q;
            }
        }

        if ($paginationSize === null) {
            $paginationSize = $this->getDefaultPaginationSize();
        }

        $searchList->setItemsPerPage($paginationSize);

        $this->searchResult = new SearchResult(
            $columnSet,
            $searchList,
            $this->app->make('url/manager')->resolve(['dashboard/push_notifications/subscribers/']),
        );
    }

    /**
     * Get the search result (once the search() method has been called).
     *
     * @return SearchResult|null
     */
    public function getSearchResultObject()
    {
        return $this->searchResult;
    }
}
