<?php

namespace App\Tests\Api\Syno\Storm;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Syno\Storm\Document;

final class SurveyPublishTest extends WebTestCase
{
    const SURVEY_ID = 23456;

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
    public static function deleteSurveys()
    {
        self::$client->request('DELETE', sprintf('/api/v1/survey/%d/%d', self::SURVEY_ID, 1));
        self::$client->request('DELETE', sprintf('/api/v1/survey/%d/%d', self::SURVEY_ID, 2));
    }

    public function testCreate()
    {
        $surveys = [
            [
                'surveyId' => self::SURVEY_ID,
                'slug'     => 'publish_test',
                'version'  => 1,
                'pages'    => []
            ],
            [
                'surveyId' => self::SURVEY_ID,
                'slug'     => 'publish_test',
                'version'  => 2,
                'pages'    => []
            ]
        ];

        foreach ($surveys as $version) {
            self::$client->request(
                'POST',
                '/api/v1/survey',
                [],
                [],
                [],
                json_encode($version)
            );
            $this->assertEquals(201, self::$client->getResponse()->getStatusCode());
        }
    }

    /**
     * @depends testCreate
     */
    public function testSurveysAreUnpublishedByDefault()
    {
        foreach ([1, 2] as $version) {
            self::$client->request('GET', sprintf('/api/v1/survey/%d/%d', self::SURVEY_ID, $version));
            $survey = json_decode(self::$client->getResponse()->getContent(), true);
            $this->assertArrayHasKey('published', $survey);
            $this->assertFalse($survey['published']);
        }
    }

    /**
     * @depends testCreate
     */
    public function testPublish()
    {
        // publish v1
        self::$client->request('PUT', sprintf('/api/v1/survey/%d/%d', self::SURVEY_ID, 1));

        self::$client->request('GET', sprintf('/api/v1/survey/%d/%d', self::SURVEY_ID, 1));
        $survey1 = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertTrue($survey1['published']);

        self::$client->request('GET', sprintf('/api/v1/survey/%d/%d', self::SURVEY_ID, 2));
        $survey2 = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertFalse($survey2['published']);

        // publish v2
        self::$client->request('PUT', sprintf('/api/v1/survey/%d/%d', self::SURVEY_ID, 2));

        self::$client->request('GET', sprintf('/api/v1/survey/%d/%d', self::SURVEY_ID, 1));
        $survey1 = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertFalse($survey1['published']);

        self::$client->request('GET', sprintf('/api/v1/survey/%d/%d', self::SURVEY_ID, 2));
        $survey2 = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertTrue($survey2['published']);
    }
}
