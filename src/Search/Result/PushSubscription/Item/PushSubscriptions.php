<?php

namespace WebApp\Search\Result\PushSubscription\Item;

use Concrete\Core\Search\Result\Item;
use ClassKit\Search\Result\Item\ItemTrait;

class PushSubscriptions extends Item
{
    use ItemTrait;

    public function getViewUrl()
    {
        return $this->entity->getLinkUrl();
    }
}
