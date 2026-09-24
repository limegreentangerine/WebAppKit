<?php

namespace WebApp\Log;

use ClassKit\Log\Logger;

class PushNotificationLog extends Logger
{
    public function __construct()
    {
        parent::__construct('push_notifications');
    }
}
