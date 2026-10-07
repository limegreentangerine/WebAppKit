<?php

namespace Concrete\Package\WebApp\Controller\SinglePage\Dashboard\PushNotifications\Settings;

use Core;
use PageType;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Page\Controller\DashboardPageController;

class Publish extends DashboardPageController
{
    public function view()
    {
        $pkg = Core::make(PackageService::class)->getByHandle('web_app');
        $config = $pkg->getFileConfig();

        $this->set('config', $config);

        $this->set('publish', explode(',', $config->get('push_notifications.publish')));
        $this->set('types', PageType::getList());
    }

    public function save()
    {
        $post = $this->post();

        if (!$this->token->validate('set_publish_notification_types')) {
            $this->error->add($this->token->getErrorMessage());
        }

        if (!$this->error->has()) {
            if (isset($post['types'])) {
                $pkg = Core::make(PackageService::class)->getByHandle('web_app');
                $config = $pkg->getFileConfig();
                $config->save('push_notifications.publish', implode(',', $post['types']));
            }

            return $this->buildRedirect('/dashboard/push_notifications/settings/publish');
        }

        $this->set('types', PageType::getList());
        $this->set('formContent', $post);
    }
}
