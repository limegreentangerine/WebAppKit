<?php

namespace Concrete\Package\WebApp\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;

class PushNotifications extends DashboardPageController
{
    public function view()
    {
        $this->buildRedirect('/dashboard/push_notifications/custom');
    }
}
