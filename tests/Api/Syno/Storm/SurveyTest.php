<?php

namespace App\Tests\Api\Syno\Storm;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Syno\Storm\Document;

final class SurveyTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(
            [], [
                  'HTTP_ACCESS_TOKEN' => getenv('STORM_API_TOKEN'),
              ]
        );
    }

    public function testSave()
    {
        $data = [
            'stormMakerSurveyId' => 123456,
            'slug'               => 'test_slug',
            'version'            => 1,
            'pages'              => [
                $this->getPage1(),
                $this->getPage2(),
                $this->getPage3(),
                $this->getPage4()
            ]
        ];

        $this->client->request(
            'POST',
            '/api/survey',
            [],
            [],
            [],
            json_encode($data)
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
        $surveyId = $this->client->getResponse()->getContent();
        $this->assertIsString($surveyId);
        $this->assertTrue(26 === strlen($surveyId));

        return $surveyId;
    }

    protected function getPage1()
    {
        return [
            'stormMakerPageId' => 1234561,
            'code'             => 'P1',
            'sortOrder'        => 1,
            'content'          => 'content for page 1',
            'questions'        => [
                [
                    'stormMakerQuestionId' => 12345611,
                    'code'                 => 'P1Q1',
                    'sortOrder'            => 1,
                    'required'             => true,
                    'text'                 => 'Single choice question',
                    'questionTypeId'       => Document\Question::TYPE_SINGLE_CHOICE,
                    'answers'              => [
                        [
                            'stormMakerAnswerId' => 123456111,
                            'code'               => 'A1',
                            'sortOrder'          => 1,
                            'answerFieldTypeId'  => Document\Answer::FIELD_TYPE_RADIO,
                            'label'              => 'Yes',
                        ],
                        [
                            'stormMakerAnswerId' => 123456112,
                            'code'               => 'A2',
                            'sortOrder'          => 2,
                            'answerFieldTypeId'  => Document\Answer::FIELD_TYPE_RADIO,
                            'label'              => 'No',
                        ],

                    ]
                ],
                [
                    'stormMakerQuestionId' => 12345612,
                    'code'                 => 'P1Q2',
                    'sortOrder'            => 2,
                    'required'             => true,
                    'text'                 => 'Multiple choice question',
                    'questionTypeId'       => Document\Question::TYPE_MULTIPLE_CHOICE,
                    'answers'              => [
                        [
                            'stormMakerAnswerId' => 123456121,
                            'code'               => 'A1',
                            'sortOrder'          => 1,
                            'answerFieldTypeId'  => Document\Answer::FIELD_TYPE_CHECKBOX,
                            'label'              => 'Checkbox 1',
                        ],
                        [
                            'stormMakerAnswerId' => 123456122,
                            'code'               => 'A2',
                            'sortOrder'          => 2,
                            'answerFieldTypeId'  => Document\Answer::FIELD_TYPE_CHECKBOX,
                            'label'              => 'Checkbox 2',
                        ],
                        [
                            'stormMakerAnswerId' => 123456123,
                            'code'               => 'A3',
                            'sortOrder'          => 3,
                            'answerFieldTypeId'  => Document\Answer::FIELD_TYPE_CHECKBOX,
                            'label'              => 'Checkbox 3',
                        ],
                    ]
                ]

            ]
        ];
    }

    protected function getPage2()
    {
        return [
            'stormMakerPageId' => 1234562,
            'code'             => 'P2',
            'sortOrder'        => 2,
            'questions'        => [
                [
                    'stormMakerQuestionId' => 12345621,
                    'code'                 => 'P2Q1',
                    'sortOrder'            => 1,
                    'required'             => true,
                    'text'                 => 'Single choice matrix',
                    'questionTypeId'       => Document\Question::TYPE_SINGLE_CHOICE_MATRIX,
                    'answers'              => [
                        [
                            'stormMakerAnswerId' => 123456211,
                            'rowCode'           => 'R1',
                            'columnCode'        => 'C1',
                            'sortOrder'          => 1,
                            'answerFieldTypeId'  => Document\Answer::FIELD_TYPE_RADIO,
                            'rowLabel'          => 'Row 1',
                            'columnLabel'       => 'Column 1',
                        ],
                        [
                            'stormMakerAnswerId' => 123456212,
                            'rowCode'           => 'R1',
                            'columnCode'        => 'C2',
                            'sortOrder'          => 2,
                            'answerFieldTypeId'  => Document\Answer::FIELD_TYPE_RADIO,
                            'rowLabel'          => 'Row 1',
                            'columnLabel'       => 'Column 2',
                        ],
                        [
                            'stormMakerAnswerId' => 123456213,
                            'rowCode'           => 'R2',
                            'columnCode'        => 'C1',
                            'sortOrder'          => 3,
                            'answerFieldTypeId'  => Document\Answer::FIELD_TYPE_RADIO,
                            'rowLabel'          => 'Row 2',
                            'columnLabel'       => 'Column 1',
                        ],
                        [
                            'stormMakerAnswerId' => 123456214,
                            'rowCode'           => 'R2',
                            'columnCode'        => 'C2',
                            'sortOrder'          => 4,
                            'answerFieldTypeId'  => Document\Answer::FIELD_TYPE_RADIO,
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
            'stormMakerPageId' => 1234563,
            'code'             => 'P3',
            'sortOrder'        => 3,
            'questions'        => [
                [
                    'stormMakerQuestionId' => 12345631,
                    'code'                 => 'P3Q1',
                    'sortOrder'            => 1,
                    'required'             => true,
                    'text'                 => 'Multiple choice matrix',
                    'questionTypeId'       => Document\Question::TYPE_MULTIPLE_CHOICE_MATRIX,
                    'answers'              => [
                        [
                            'stormMakerAnswerId' => 123456311,
                            'rowCode'           => 'R1',
                            'columnCode'        => 'C1',
                            'sortOrder'          => 1,
                            'answerFieldTypeId'  => Document\Answer::FIELD_TYPE_CHECKBOX,
                            'rowLabel'          => 'Row 1',
                            'columnLabel'       => 'Column 1',
                        ],
                        [
                            'stormMakerAnswerId' => 123456312,
                            'rowCode'           => 'R1',
                            'columnCode'        => 'C2',
                            'sortOrder'          => 2,
                            'answerFieldTypeId'  => Document\Answer::FIELD_TYPE_CHECKBOX,
                            'rowLabel'          => 'Row 1',
                            'columnLabel'       => 'Column 2',
                        ],
                        [
                            'stormMakerAnswerId' => 123456313,
                            'rowCode'           => 'R2',
                            'columnCode'        => 'C1',
                            'sortOrder'          => 3,
                            'answerFieldTypeId'  => Document\Answer::FIELD_TYPE_CHECKBOX,
                            'rowLabel'          => 'Row 2',
                            'columnLabel'       => 'Column 1',
                        ],
                        [
                            'stormMakerAnswerId' => 123456314,
                            'rowCode'           => 'R2',
                            'columnCode'        => 'C2',
                            'sortOrder'          => 4,
                            'answerFieldTypeId'  => Document\Answer::FIELD_TYPE_CHECKBOX,
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
            'stormMakerPageId' => 1234564,
            'code'             => 'P4',
            'sortOrder'        => 4,
            'questions'        => [
                [
                    'stormMakerQuestionId' => 12345641,
                    'code'                 => 'P4Q1',
                    'sortOrder'            => 1,
                    'required'             => true,
                    'text'                 => 'Text question',
                    'questionTypeId'       => Document\Question::TYPE_TEXT,
                    'answers'              => [
                        [
                            'stormMakerAnswerId' => 123456411,
                            'code'               => 'A1',
                            'sortOrder'          => 1,
                            'answerFieldTypeId'  => Document\Answer::FIELD_TYPE_TEXT,
                            'label'              => 'Text label'
                        ],
                    ]
                ]
            ]
        ];
    }
}
