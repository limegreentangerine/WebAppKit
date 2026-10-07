<?php

namespace WebApp\Tests;

use JsonException;
use PHPUnit\Framework\TestCase;
use WebApp\Response\PushNotificationResponse;

class PushNotificationResponseTest extends TestCase
{
    public function testSettersAreFluentAndExposeResponseValues(): void
    {
        $response = new PushNotificationResponse();

        $this->assertSame($response, $response->setUrl('https://example.test/push'));
        $this->assertSame($response, $response->setStatusCode(202));
        $this->assertSame($response, $response->setBody('{"accepted":true}'));
        $this->assertSame('https://example.test/push', $response->getUrl());
        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('{"accepted":true}', $response->getBody());
    }

    public function testBodyCanBeDecodedAsObjectOrAssociativeArray(): void
    {
        $response = (new PushNotificationResponse())->setBody('{"accepted":true,"count":2}');

        $this->assertEquals((object) ['accepted' => true, 'count' => 2], $response->getBodyDecoded());
        $this->assertSame(['accepted' => true, 'count' => 2], $response->getBodyDecoded(true));
    }

    public function testInvalidJsonRaisesJsonException(): void
    {
        $response = (new PushNotificationResponse())->setBody('{invalid json');

        $this->expectException(JsonException::class);
        $response->getBodyDecoded();
    }
}
