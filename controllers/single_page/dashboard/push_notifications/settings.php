<?php

namespace Concrete\Package\WebApp\Controller\SinglePage\Dashboard\PushNotifications;

use Core;
use WebApp\Entity\PushKey;
use Minishlink\WebPush\VAPID;
use Doctrine\ORM\EntityManagerInterface;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Page\Controller\DashboardPageController;

class Settings extends DashboardPageController
{
    public function view()
    {
        $pkg = Core::make(PackageService::class)->getByHandle('web_app');
        $config = $pkg->getFileConfig();
        $vapidKeys = PushKey::getByID($config->get('push_notifications.key_id'));
        $this->set('vapidKeys', $vapidKeys);
    }

    public function generate_keys()
    {
        $vapid = VAPID::createVapidKeys();

        $em = Core::make(EntityManagerInterface::class);
        $currentKeys = new PushKey();
        $currentKeys->setPublicKey($vapid['publicKey']);
        $currentKeys->setPrivateKey($vapid['privateKey']);

        $em->persist($currentKeys);
        $em->flush();

        return $this->buildRedirect('/dashboard/push_notifications/settings');
    }
}
