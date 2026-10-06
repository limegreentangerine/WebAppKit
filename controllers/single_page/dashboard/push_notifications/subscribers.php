<?php

namespace Concrete\Package\WebApp\Controller\SinglePage\Dashboard\PushNotifications;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Package\WebApp\Controller\Search\Subscribers\Subscribers as SearchController;

class Subscribers extends DashboardPageController
{
    public function view(): void
    {
        $reset = false;

        if ($this->request->isPost()) {
            if (!$this->token->validate('notification-subscriber-search')) {
                $this->error->add($this->token->getErrorMessage());
            } else {
                $reset = true;
            }
        }

        $search = $this->app->make(SearchController::class);
        $search->search($reset);

        $result = $search->getSearchResultObject();

        $this->set('result', $result);
        $this->set('items', $result->getItems());
        $this->set('pagination', $result->getPaginationHTML());

        $allowed_num_results = array_combine(
            $search->getAllowedPaginationSizes(),
            $search->getAllowedPaginationSizes(),
        );

        $params = $search->getStickyRequest()->getSearchRequest();
        $this->set('name', isset($params['name']) ? $params['name'] : '');

        if (isset($params['num_results']) && isset($allowed_num_results[$params['num_results']])) {
            $num_results = (int) $params['num_results'];
        } else {
            $num_results = $search->getDefaultPaginationSize();
        }

        $this->set('num_results', $num_results);
        $this->set('params', $params);
        $this->set('allowed_num_results', $allowed_num_results);
    }
}
