<?php

namespace PushNotifications\Search\Result\ScheduledNotification\Item;

use Concrete\Core\Search\Result\Item;
use ClassKit\Search\Result\Item\ItemTrait;

class ScheduledNotifications extends Item
{
    use ItemTrait;

    public function getViewUrl()
    {
        return $this->entity->getLinkUrl();
    }
}
