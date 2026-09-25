<?php

namespace Concrete\Package\WebApp\Controller\SinglePage\Dashboard\PushNotifications;

use Core;
use Concrete\Core\Page\Controller\DashboardPageController;
use WebApp\Entity\CustomNotification as CustomNotificationEntity;
use Concrete\Package\WebApp\Controller\Search\CustomNotification\CustomNotifications as SearchController;

class Custom extends DashboardPageController
{
    public $helpers = [
        'form',
        'form/date_time',
        'form/page_selector',
    ];

    /**
     * @param array<mixed> $args
     */
    protected function validate(array $args): void
    {
        $vstrings = $this->app->make('helper/validation/strings');

        if (!$vstrings->notempty($args['title'])) {
            $this->error->add(t('Please enter a title'));
        }

        if (!$vstrings->notempty($args['description'])) {
            $this->error->add(t('Please enter a description'));
        }
    }

    public function view(): void
    {
        $reset = false;

        if ($this->request->isPost()) {
            if (!$this->token->validate('custom-notification-search')) {
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

    public function add(): void {}

    public function details(int $id): void
    {
        if (!$id) {
            $this->buildRedirect('/dashboard/push_notifications/custom/');
        }

        $entity = CustomNotificationEntity::getByID($id);

        if (!$entity) {
            $this->buildRedirect('/dashboard/push_notifications/custom/');
        }

        $this->set('entity', $entity);
    }

    public function save(): void
    {
        if ($this->post()) {
            $post = $this->post();

            if (!$this->token->validate('submit')) {
                $this->error->add($this->token->getErrorMessage());
            }

            $this->validate($post);

            $post['sendDate'] = Core::make('helper/form/date_time')->translate('sendDate', $post, true);

            if (!$this->error->has()) {
                $entity = new CustomNotificationEntity();

                if (isset($post['id'])) {
                    $existingEntity = CustomNotificationEntity::getByID((int) $post['id']);

                    if ($existingEntity instanceof CustomNotificationEntity) {
                        $entity = $existingEntity;
                    } else {
                        $this->error->add(t('The custom notification could not be found.'));
                    }
                }

                if (!$this->error->has()) {
                    $entity->setTitle($post['title']);
                    $entity->setDescription($post['description']);
                    $entity->setLink($post['link']);
                    $entity->setSendDate($post['sendDate']);

                    $this->entityManager->persist($entity);
                    $this->entityManager->flush();

                    $this->flash('success', t('Custom Notification Saved.'));

                    $this->buildRedirect('/dashboard/push_notifications/custom/');
                }
            }

            $this->set('formContent', $post);
        }
    }
}
