<?php

namespace WebApp\Search\Result\CustomNotification;

use ClassKit\Search\Result as SearchResult;

class CustomNotifications extends SearchResult
{
    public function getItemDetails(mixed $result)
    {
        return new Item\CustomNotifications($this, $this->listColumns, $result);
    }
}
