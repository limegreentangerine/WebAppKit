<?php

namespace Concrete\Package\WebApp\Controller\SinglePage\Dashboard;

use Symfony\Component\HttpFoundation\Response;
use Concrete\Core\Page\Controller\DashboardPageController;

class PushNotifications extends DashboardPageController
{
    public function view(): ?Response
    {
        return $this->buildRedirect('/dashboard/push_notifications/custom');
    }
}
