<?php defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\View\View;

if (isset($controller) && in_array($controller->getTask(), ['add', 'details', 'save'])) {
    View::element(
        'dashboard/push_notifications/custom/details',
        [
            'token'     => $token ?? null,
            'form'      => $form ?? null,
            'entity'    => $entity ?? null,
            'h'         => [
                'dth'   => $form_date_time ?? null,
                'ps'    => $form_page_selector ?? null
            ],
            'v'         => [
                'id'            => (isset($entity)) ? $entity->getID() : ((isset($formContent) && isset($formContent['id'])) ? $formContent['id'] : ''),
                'title'         => (isset($entity)) ? $entity->getTitle() : ((isset($formContent) && isset($formContent['title'])) ? $formContent['title'] : ''),
                'description'   => (isset($entity)) ? $entity->getDescription() : ((isset($formContent) && isset($formContent['description'])) ? $formContent['description'] : ''),
                'link'          => (isset($entity)) ? $entity->getLink() : ((isset($formContent) && isset($formContent['link'])) ? $formContent['link'] : false),
                'sendDate'      => (isset($entity)) ? $entity->getSendDate() : ((isset($formContent) && isset($formContent['sendDate'])) ? $formContent['sendDate'] : new \DateTime()),
            ]
        ],
        'web_app'
    );
} else {
    View::element(
        'dashboard/push_notifications/custom/all',
        [
            'token'         => $token ?? null,
            'params'        => $params ?? null,
            'items'         => $items ?? null,
            'result'        => $result ?? null,
            'pagination'    => $pagination ?? null
        ],
        'web_app'
    );
}
