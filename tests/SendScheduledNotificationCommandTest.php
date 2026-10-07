<?php

namespace WebApp\Tests;

use PHPUnit\Framework\TestCase;
use WebApp\Command\SendScheduledNotificationCommand;
use WebApp\Command\SendScheduledNotificationCommandHandler;

class SendScheduledNotificationCommandTest extends TestCase
{
    public function testConstructorSetsNotificationValuesAndHandler(): void
    {
        $command = new SendScheduledNotificationCommand(12, 'Article', 34);

        $this->assertSame(12, $command->getNotificationId());
        $this->assertSame('Article', $command->getType());
        $this->assertSame(34, $command->getReferenceId());
        $this->assertSame(SendScheduledNotificationCommandHandler::class, $command::getHandler());
    }

    public function testSettersUpdateValuesAndReturnCommand(): void
    {
        $command = new SendScheduledNotificationCommand(1, 'old', 2);

        $this->assertSame($command, $command->setNotificationId(3));
        $this->assertSame($command, $command->setType('custom'));
        $this->assertSame($command, $command->setReferenceId(4));
        $this->assertSame(3, $command->getNotificationId());
        $this->assertSame('custom', $command->getType());
        $this->assertSame(4, $command->getReferenceId());
    }
}
