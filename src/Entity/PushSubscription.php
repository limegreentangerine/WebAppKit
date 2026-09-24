<?php

namespace WebApp\Entity;

use Doctrine\ORM\Mapping as ORM;
use ClassKit\Entity\Core\BaseEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="pushSubscriptions"
 * )
 */
class PushSubscription extends BaseEntity
{
    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected string $endpoint;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected string $subscription;

    /**
     * Get the value of endpoint
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * Set the value of endpoint
     *
     * @return self
     */
    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * Get the value of subscription
     */
    public function getSubscription(): string
    {
        return $this->subscription;
    }

    /**
     * Set the value of subscription
     *
     * @return self
     */
    public function setSubscription(string $subscription): self
    {
        $this->subscription = json_encode($subscription);

        return $this;
    }
}
