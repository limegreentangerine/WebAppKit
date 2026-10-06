<?php

namespace Concrete\Package\WebApp\Controller\SinglePage\Dashboard\PushNotifications;

use Core;
use WebApp\Entity\PushKey;
use Minishlink\WebPush\VAPID;
use Doctrine\ORM\EntityManagerInterface;
use Concrete\Core\Package\PackageService;
use Symfony\Component\HttpFoundation\Response;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Page\Controller\DashboardPageController;

class Settings extends DashboardPageController
{
    public function view(): void
    {
        $pkg = Core::make(PackageService::class)->getByHandle('web_app');
        $config = $pkg->getFileConfig();
        $keyId = $config->get('push_notifications.key_id');
        if ($keyId) {
            $vapidKeys = PushKey::getByID($keyId);
            $this->set('vapidKeys', $vapidKeys);
        }
    }

    public function generate_keys(): Response
    {
        $vapid = VAPID::createVapidKeys();

        $em = Core::make(EntityManagerInterface::class);
        $currentKeys = new PushKey();
        $currentKeys->setPublicKey($vapid['publicKey']);
        $currentKeys->setPrivateKey($vapid['privateKey']);

        $em->persist($currentKeys);
        $em->flush();

        $pkg = Core::make(PackageService::class)->getByHandle('web_app');
        $config = $pkg->getFileConfig();
        $config->save('push_notifications.key_id', $currentKeys->getID());

        $this->flash('success', t('VAPID keys generated (ID: %s)', $currentKeys->getID()));
        return $this->buildRedirect('/dashboard/push_notifications/settings');
    }

    public function regenerate_keys(): Response
    {
        $rf = $this->app->make(ResponseFactoryInterface::class);

        if (!$this->token->validate('regenerate-vapid-keys')) {
            $this->error->add($this->token->getErrorMessage());
        } else {
            $vapid = VAPID::createVapidKeys();

            $em = Core::make(EntityManagerInterface::class);
            $currentKeys = new PushKey();
            $currentKeys->setPublicKey($vapid['publicKey']);
            $currentKeys->setPrivateKey($vapid['privateKey']);

            $em->persist($currentKeys);
            $em->flush();

            $pkg = Core::make(PackageService::class)->getByHandle('web_app');
            $config = $pkg->getFileConfig();
            $config->save('push_notifications.key_id', $currentKeys->getID());

            $this->flash('success', t('VAPID keys regenerated (ID: %s)', $currentKeys->getID()));
            return $rf->json(true);

        }

        return $rf->json($this->error->jsonSerialize());
    }
}
