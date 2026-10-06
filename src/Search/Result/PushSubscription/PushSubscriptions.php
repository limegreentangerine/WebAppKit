<?php

namespace WebApp\Search\Result\PushSubscription;

use ClassKit\Search\Result as SearchResult;

class PushSubscriptions extends SearchResult
{
    public function getItemDetails(mixed $result)
    {
        return new Item\PushSubscriptions($this, $this->listColumns, $result);
    }
}
