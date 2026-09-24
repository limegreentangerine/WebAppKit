<?php

namespace WebApp\Entity;

use Doctrine\ORM\Mapping as ORM;
use ClassKit\Entity\Core\BaseEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="pushKeys"
 * )
 */
class PushKey extends BaseEntity
{
    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected string $publicKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected string $privateKey;

    /**
     * Get the value of publicKey
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Set the value of publicKey
     *
     * @return self
     */
    public function setPublicKey(string $publicKey): self
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * Get the value of privateKey
     */
    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    /**
     * Set the value of privateKey
     *
     * @return self
     */
    public function setPrivateKey(string $privateKey): self
    {
        $this->privateKey = $privateKey;

        return $this;
    }
}
