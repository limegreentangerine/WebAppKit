<?php

namespace WebApp\Search\Result\ScheduledNotification;

use ClassKit\Search\Result as SearchResult;

class ScheduledNotifications extends SearchResult
{
    public function getItemDetails(mixed $result)
    {
        return new Item\ScheduledNotifications($this, $this->listColumns, $result);
    }
}
