<?php

namespace App\Tests\Controller;

final class AuthCodeControllerTest extends Unit
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/auth/request-code');
        self::assertResponseIsSuccessful();
    }
}
