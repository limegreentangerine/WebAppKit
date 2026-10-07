<?php

namespace Concrete\Package\WebApp\Controller\SinglePage\Dashboard\PushNotifications;

use Core;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Page\Controller\DashboardPageController;

class Settings extends DashboardPageController
{
    protected function validateSubmit(array $post): void
    {
        $vstrings = $this->app->make('helper/validation/strings');

        if (!$vstrings->email($post['subject'])) {
            $this->error->add('Please enter a subject', 'subject');
        }
    }
    public function on_start()
    {
        parent::on_start();

        $pkg = Core::make(PackageService::class)->getByHandle('web_app');
        $config = $pkg->getFileConfig();

        $this->set('config', $config);
    }

    public function save()
    {
        $post = $this->post();

        if (!$this->token->validate('push_notification_settings')) {
            $this->error->add($this->token->getErrorMessage());
        }

        $this->validateSubmit($post);

        if (!$this->error->has()) {
            $pkg = Core::make(PackageService::class)->getByHandle('web_app');
            $config = $pkg->getFileConfig();
            $config->save('push_notifications.activate', ($post['activate']) ? true : false);
            $config->save('push_notifications.subject', $post['subject']);

            return $this->buildRedirect('/dashboard/push_notifications/settings');
        }

        $this->set('errors', $this->error);
        $this->set('formContent', $post);
    }
}
