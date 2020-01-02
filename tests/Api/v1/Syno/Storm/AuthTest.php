<?php

namespace App\Tests\Api\v1\Syno\Storm;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuthTest extends WebTestCase
{
    public function testAuthWithToken()
    {
        $client = static::createClient([], [
            'HTTP_ACCESS_TOKEN' => getenv('STORM_API_TOKEN'),
        ]);

        $client->request('GET', '/api/v1');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $this->assertContains('ok', $client->getResponse()->getContent());
    }
}
