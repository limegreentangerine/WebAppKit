<?php defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\View\View;

View::element(
    'dashboard/push_notifications/scheduled/all',
    [
        'token'         => $token ?? null,
        'params'        => $params ?? null,
        'items'         => $items ?? null,
        'result'        => $result ?? null,
        'pagination'    => $pagination ?? null
    ],
    'web_app'
);
