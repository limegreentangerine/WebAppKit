<?php

namespace WebApp\Command;

use Concrete\Core\Foundation\Command\Command;

class SendScheduledNotificationCommand extends Command
{
    protected int $notificationId;

    protected string $type;

    protected int $referenceId;

    public function __construct(
        int $notificationId,
        string $type,
        int $referenceId,
    ) {
        $this->setNotificationId($notificationId);
        $this->setType($type);
        $this->setReferenceId($referenceId);
    }


    public static function getHandler(): string
    {
        return SendScheduledNotificationCommandHandler::class;
    }

    /**
     * Get the value of type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of referenceId
     */
    public function getReferenceId(): int
    {
        return $this->referenceId;
    }

    /**
     * Set the value of referenceId
     *
     * @return self
     */
    public function setReferenceId(int $referenceId): self
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    /**
     * Get the value of notificationId
     *
     * @return int
     */
    public function getNotificationId(): int
    {
        return $this->notificationId;
    }

    /**
     * Set the value of notificationId
     *
     * @param int $notificationId
     *
     * @return self
     */
    public function setNotificationId(int $notificationId): self
    {
        $this->notificationId = $notificationId;

        return $this;
    }
}
