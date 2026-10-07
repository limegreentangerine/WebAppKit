<?php

namespace WebApp\Entity;

use DateTime;
use Concrete\Core\Page\Page;
use Doctrine\ORM\Mapping as ORM;
use ClassKit\Entity\Core\BaseEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="pushScheduled"
 * )
 */
class ScheduledNotification extends BaseEntity
{
    /**
     * @ORM\Column(type="integer", length=55, unique=false, nullable=false)
     */
    protected int $type;

    /**
     * @ORM\Column(type="integer", length=55, unique=false, nullable=false)
     */
    protected int $referenceId;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected DateTime $sendDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected DateTime $sentAt;

    /**
     * Get the value of type
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return self
     */
    public function setType(int $type): self
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
     * Get the value of sendDate
     */
    public function getSendDate(string|bool $format = false): DateTime|string
    {
        if ($format !== false && is_string($format)) {
            return $this->sendDate->format($format);
        }

        return $this->sendDate;
    }

    public function getSendDateString(): string
    {
        return $this->getSendDate('d/m/Y H:i');
    }

    /**
     * Set the value of sendDate
     *
     * @return self
     */
    public function setSendDate(DateTime $sendDate): self
    {
        $this->sendDate = $sendDate;

        return $this;
    }

    public function getLinkUrl(): ?string
    {
        $page = Page::getByID($this->getReferenceId());
        if (!$page->isError()) {
            return $page->getCollectionLink();
        }

        return null;
    }

    /**
     * Get the value of sendAt
     */
    public function getSendAt(): DateTime
    {
        return $this->sentAt;
    }

    public function getSendAtString(): string
    {
        return $this->sentAt->format('d/m/Y H:i');
    }

    /**
     * Set the value of sendAt
     *
     * @return self
     */
    public function setSendAt(DateTime $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }
}
