<?php

namespace WebApp\Response;

class PushNotificationResponse
{
    protected string $url;

    protected int $statusCode;

    protected string $body;

    /**
     * Get the value of url
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set the value of url
     *
     * @param string $url
     *
     * @return self
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the value of statusCode
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set the value of statusCode
     *
     * @param int $statusCode
     *
     * @return self
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Get the value of body
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Get the value of body decoded
     *
     * @return mixed
     */
    public function getBodyDecoded(bool $asAssoc = false): mixed
    {
        return json_decode($this->body, $asAssoc, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Set the value of body
     *
     * @param string $body
     *
     * @return self
     */
    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }
}
