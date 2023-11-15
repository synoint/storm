<?php

namespace Plugins\Survey_172100;

use Symfony\Component\HttpFoundation\Request;
use Syno\Storm\Plugin\AbstractPlugin;
use Syno\Storm\RequestHandler\Survey;
use Syno\Storm\Services\ResponseSessionManager;

class Plugin extends AbstractPlugin
{
    private Survey                 $surveyHandler;
    private ResponseSessionManager $responseSessionManager;

    public function __construct(Survey $surveyHandler, ResponseSessionManager $responseSessionManager)
    {
        $this->surveyHandler          = $surveyHandler;
        $this->responseSessionManager = $responseSessionManager;
    }

    public function onSurveyEntry(Request $request): void
    {
        $customId = $request->query->getAlnum('cid');
        if (!$customId) {
            return;
        }

        $data = [];
        foreach ($this->readData() as $row) {
            if ($customId != $row[0]) {
                continue;
            }

            foreach ($this->getAnswerMap() as $code => $dataFileColumnIndex) {
                $data[$code] = $row[$dataFileColumnIndex];
            }

            $this->responseSessionManager->saveAnswers($data, $this->surveyHandler->getSurvey()->getQuestions());
            break;
        }
    }

    private function readData(): array
    {
        return array_map('str_getcsv', file(__DIR__ . '/data.csv'));
    }

    private function getAnswerMap(): array
    {
        // {question_code}_{answer_code} => data_file_column_index
        return [
            'customid_1' => 0,
            'Q1A_1' => 1, // group no
            'Q1B_1' => 2, // group
            'Q2A_1' => 3, // type
            'Q3A_1' => 4, // restaurant number
            'Q3B_1' => 5  // restaurant name
        ];
    }
}
