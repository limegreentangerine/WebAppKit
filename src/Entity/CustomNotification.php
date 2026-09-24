<?php

namespace WebApp\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use ClassKit\Entity\Core\BaseEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="pushCustomNotifications"
 * )
 */
class CustomNotification extends BaseEntity
{
    /**
     * @ORM\Column(type="string", length=255, unique=false, nullable=false)
     */
    protected string $title;

    /**
     * @ORM\Column(type="string", length=255, unique=false, nullable=false)
     */
    protected string $description;

    /**
     * @ORM\Column(type="integer", length=55, unique=false, nullable=false)
     */
    protected int $link;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected DateTime $sendDate;

    /**
     * Get the value of title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of linkUrl
     */
    public function getLink(): string
    {
        return $this->link;
    }

    public function getLinkUrl(): ?string
    {
        $page = \Page::getByID($this->link);
        return ($page) ? $page->getCollectionLink() : null;
    }

    /**
     * Set the value of linkUrl
     *
     * @return self
     */
    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get the value of sendDate
     */
    public function getSendDate(): DateTime
    {
        return $this->sendDate;
    }

    public function getSendDateString(): string
    {
        return $this->sendDate->format('d/m/Y H:i');
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
}
