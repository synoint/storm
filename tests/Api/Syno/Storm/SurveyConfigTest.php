<?php

namespace App\Tests\Api\Syno\Storm;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Syno\Storm\Document;

final class SurveyConfigTest extends WebTestCase
{
    const SURVEY_ID     = 55555;
    const VERSION       = 1;

    /** @var KernelBrowser */
    protected static $client;

    public function setUp()
    {
        self::$client = static::createClient(
            [], [
                  'HTTP_ACCESS_TOKEN' => getenv('STORM_API_TOKEN'),
              ]
        );
    }

    /**
     * @afterClass
     */
    public static function deleteSurvey()
    {
        self::$client->request('DELETE', sprintf('/api/v1/survey/%d/%d', self::SURVEY_ID, self::VERSION));
    }

    public function testCreate()
    {
        $data = [
            'surveyId' => self::SURVEY_ID,
            'version'  => self::VERSION,
            'pages'    => [],
            'config'   => [
                'privacyConsentEnabled' => true,
                'theme' => 'test_theme'
            ]
        ];

        self::$client->request(
            'POST',
            '/api/v1/survey',
            [],
            [],
            [],
            json_encode($data)
        );

        $this->assertEquals(201, self::$client->getResponse()->getStatusCode());
    }

    /**
     * @depends testCreate
     */
    public function testConfig()
    {
        self::$client->request('GET', sprintf('/api/v1/survey/%d/%d', self::SURVEY_ID, self::VERSION));
        $survey = json_decode(self::$client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('config', $survey);
        $this->assertIsArray($survey['config']);

        $this->assertArrayHasKey('privacyConsentEnabled', $survey['config']);
        $this->assertTrue($survey['config']['privacyConsentEnabled']);

        $this->assertArrayHasKey('theme', $survey['config']);
        $this->assertEquals('test_theme', $survey['config']['theme']);
    }
}
