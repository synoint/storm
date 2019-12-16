<?php

namespace App\Tests\Api\Syno\Storm;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Syno\Storm\Document;

final class SurveyTest extends WebTestCase
{
    const SURVEY_ID     = 123456;
    const VERSION       = 1;
    const DEFAULT_THEME = 'materialize';

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

    public function testCreate()
    {
        $data = [
            'surveyId' => self::SURVEY_ID,
            'slug'     => 'test_slug',
            'version'  => self::VERSION,
            'pages'    => [
                $this->getPage1(),
                $this->getPage2(),
                $this->getPage3(),
                $this->getPage4()
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
        $surveyId = self::$client->getResponse()->getContent();
        $this->assertIsString($surveyId);
        $this->assertTrue(26 === strlen($surveyId));
    }

    /**
     * @depends testCreate
     */
    public function testRetrieve()
    {
        self::$client->request('GET', sprintf('/api/v1/survey/%d/%d', self::SURVEY_ID, self::VERSION));
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $survey = json_decode(self::$client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $survey);
        $this->assertArrayHasKey('surveyId', $survey);
        $this->assertArrayHasKey('version', $survey);
        $this->assertArrayHasKey('published', $survey);
        $this->assertArrayHasKey('theme', $survey);

        $this->assertEquals(self::SURVEY_ID, $survey['surveyId']);
        $this->assertEquals(self::VERSION, $survey['version']);
        $this->assertEquals(self::DEFAULT_THEME, $survey['theme']);
    }

    /**
     * @depends testRetrieve
     */
    public function testDelete()
    {
        self::$client->request('DELETE', sprintf('/api/v1/survey/%d/%d', self::SURVEY_ID, self::VERSION));
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
    }

    protected function getPage1()
    {
        return [
            'pageId'    => 1234561,
            'code'      => 'P1',
            'sortOrder' => 1,
            'content'   => 'content for page 1',
            'questions' => [
                [
                    'questionId'     => 12345611,
                    'code'           => 'P1Q1',
                    'sortOrder'      => 1,
                    'required'       => true,
                    'text'           => 'Single choice question',
                    'questionTypeId' => Document\Question::TYPE_SINGLE_CHOICE,
                    'answers'        => [
                        [
                            'answerId'          => 123456111,
                            'code'              => 'A1',
                            'sortOrder'         => 1,
                            'answerFieldTypeId' => Document\Answer::FIELD_TYPE_RADIO,
                            'label'             => 'Yes',
                        ],
                        [
                            'answerId'          => 123456112,
                            'code'              => 'A2',
                            'sortOrder'         => 2,
                            'answerFieldTypeId' => Document\Answer::FIELD_TYPE_RADIO,
                            'label'             => 'No',
                        ],

                    ]
                ],
                [
                    'questionId'     => 12345612,
                    'code'           => 'P1Q2',
                    'sortOrder'      => 2,
                    'required'       => true,
                    'text'           => 'Multiple choice question',
                    'questionTypeId' => Document\Question::TYPE_MULTIPLE_CHOICE,
                    'answers'        => [
                        [
                            'answerId'          => 123456121,
                            'code'              => 'A1',
                            'sortOrder'         => 1,
                            'answerFieldTypeId' => Document\Answer::FIELD_TYPE_CHECKBOX,
                            'label'             => 'Checkbox 1',
                        ],
                        [
                            'answerId'          => 123456122,
                            'code'              => 'A2',
                            'sortOrder'         => 2,
                            'answerFieldTypeId' => Document\Answer::FIELD_TYPE_CHECKBOX,
                            'label'             => 'Checkbox 2',
                        ],
                        [
                            'answerId'          => 123456123,
                            'code'              => 'A3',
                            'sortOrder'         => 3,
                            'answerFieldTypeId' => Document\Answer::FIELD_TYPE_CHECKBOX,
                            'label'             => 'Checkbox 3',
                        ],
                    ]
                ]

            ]
        ];
    }

    protected function getPage2()
    {
        return [
            'pageId'    => 1234562,
            'code'      => 'P2',
            'sortOrder' => 2,
            'questions' => [
                [
                    'questionId'     => 12345621,
                    'code'           => 'P2Q1',
                    'sortOrder'      => 1,
                    'required'       => true,
                    'text'           => 'Single choice matrix',
                    'questionTypeId' => Document\Question::TYPE_SINGLE_CHOICE_MATRIX,
                    'answers'        => [
                        [
                            'answerId'          => 123456211,
                            'rowCode'           => 'R1',
                            'columnCode'        => 'C1',
                            'sortOrder'         => 1,
                            'answerFieldTypeId' => Document\Answer::FIELD_TYPE_RADIO,
                            'rowLabel'          => 'Row 1',
                            'columnLabel'       => 'Column 1',
                        ],
                        [
                            'answerId'          => 123456212,
                            'rowCode'           => 'R1',
                            'columnCode'        => 'C2',
                            'sortOrder'         => 2,
                            'answerFieldTypeId' => Document\Answer::FIELD_TYPE_RADIO,
                            'rowLabel'          => 'Row 1',
                            'columnLabel'       => 'Column 2',
                        ],
                        [
                            'answerId'          => 123456213,
                            'rowCode'           => 'R2',
                            'columnCode'        => 'C1',
                            'sortOrder'         => 3,
                            'answerFieldTypeId' => Document\Answer::FIELD_TYPE_RADIO,
                            'rowLabel'          => 'Row 2',
                            'columnLabel'       => 'Column 1',
                        ],
                        [
                            'answerId'          => 123456214,
                            'rowCode'           => 'R2',
                            'columnCode'        => 'C2',
                            'sortOrder'         => 4,
                            'answerFieldTypeId' => Document\Answer::FIELD_TYPE_RADIO,
                            'rowLabel'          => 'Row 2',
                            'columnLabel'       => 'Column 2',
                        ],
                    ]
                ]
            ]
        ];
    }

    protected function getPage3()
    {
        return [
            'pageId'    => 1234563,
            'code'      => 'P3',
            'sortOrder' => 3,
            'questions' => [
                [
                    'questionId'     => 12345631,
                    'code'           => 'P3Q1',
                    'sortOrder'      => 1,
                    'required'       => true,
                    'text'           => 'Multiple choice matrix',
                    'questionTypeId' => Document\Question::TYPE_MULTIPLE_CHOICE_MATRIX,
                    'answers'        => [
                        [
                            'answerId'          => 123456311,
                            'rowCode'           => 'R1',
                            'columnCode'        => 'C1',
                            'sortOrder'         => 1,
                            'answerFieldTypeId' => Document\Answer::FIELD_TYPE_CHECKBOX,
                            'rowLabel'          => 'Row 1',
                            'columnLabel'       => 'Column 1',
                        ],
                        [
                            'answerId'          => 123456312,
                            'rowCode'           => 'R1',
                            'columnCode'        => 'C2',
                            'sortOrder'         => 2,
                            'answerFieldTypeId' => Document\Answer::FIELD_TYPE_CHECKBOX,
                            'rowLabel'          => 'Row 1',
                            'columnLabel'       => 'Column 2',
                        ],
                        [
                            'answerId'          => 123456313,
                            'rowCode'           => 'R2',
                            'columnCode'        => 'C1',
                            'sortOrder'         => 3,
                            'answerFieldTypeId' => Document\Answer::FIELD_TYPE_CHECKBOX,
                            'rowLabel'          => 'Row 2',
                            'columnLabel'       => 'Column 1',
                        ],
                        [
                            'answerId'          => 123456314,
                            'rowCode'           => 'R2',
                            'columnCode'        => 'C2',
                            'sortOrder'         => 4,
                            'answerFieldTypeId' => Document\Answer::FIELD_TYPE_CHECKBOX,
                            'rowLabel'          => 'Row 2',
                            'columnLabel'       => 'Column 2',
                        ],
                    ]
                ]
            ]
        ];
    }

    protected function getPage4()
    {
        return [
            'pageId'    => 1234564,
            'code'      => 'P4',
            'sortOrder' => 4,
            'questions' => [
                [
                    'questionId'     => 12345641,
                    'code'           => 'P4Q1',
                    'sortOrder'      => 1,
                    'required'       => true,
                    'text'           => 'Text question',
                    'questionTypeId' => Document\Question::TYPE_TEXT,
                    'answers'        => [
                        [
                            'answerId'          => 123456411,
                            'code'              => 'A1',
                            'sortOrder'         => 1,
                            'answerFieldTypeId' => Document\Answer::FIELD_TYPE_TEXT,
                            'label'             => 'Text label'
                        ],
                    ]
                ]
            ]
        ];
    }
}
