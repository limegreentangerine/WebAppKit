<?php

namespace WebApp\Search\Result\CustomNotification\Item;

use URL;
use Concrete\Core\Search\Result\Item;
use ClassKit\Search\Result\Item\ItemTrait;

class CustomNotifications extends Item
{
    use ItemTrait;

    public function getViewUrl()
    {
        return URL::to('/dashboard/web_app/push_notifications/custom/details', $this->entity->getID());
    }
}
