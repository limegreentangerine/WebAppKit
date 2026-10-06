<?php

namespace Concrete\Package\WebApp\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;
use Symfony\Component\HttpFoundation\Response;

class PushNotifications extends DashboardPageController
{
    public function view(): ?Response
    {
        return $this->buildRedirect('/dashboard/push_notifications/custom');
    }
}
