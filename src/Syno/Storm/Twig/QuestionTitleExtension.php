<?php

namespace Syno\Storm\Twig;

use Syno\Storm\Document\Question;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class QuestionTitleExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'title_string', function (array $responseDataLayer, string $title) {
                return $this->processTitle($responseDataLayer, $title);
            })
        ];
    }

    public function processTitle(array $responseDataLayer, string $title): string
    {
        $pattern = '/{{INSERT_ANSWER_FROM:([^}]+)}}/';
        $matches = [];

        if (preg_match_all($pattern, $title, $matches)) {
            foreach ($matches[1] as $i => $match) {
                $answer = $this->findAnswers($responseDataLayer, $match);
                $title  = str_replace($matches[0][$i], $answer, $title);
            }
        }

        return $title;
    }

    private function findAnswers(array $responseDataLayer, string $code): string
    {
        $result = [];

        foreach ($responseDataLayer['answers'] as $answer) {

            if ($answer['pageCode'] . '/' . $answer['questionCode'] === $code) {
                switch ($answer['questionType']) {
                    case Question::TYPE_SINGLE_CHOICE:
                    case Question::TYPE_GABOR_GRANGER:
                        $result[] = $answer['label'];
                        break;
                    case Question::TYPE_LINEAR_SCALE:
                        $result[] = $answer['code'];
                        break;
                    case Question::TYPE_TEXT:
                        $result[] = $answer['value'];
                        break;
                    default:
                        $result[] = '';
                }
            }

            if ($answer['questionType'] === Question::TYPE_LINEAR_SCALE_MATRIX
                && $answer['pageCode'] . '/' . $answer['questionCode'] . '/' . $answer['rowCode'] === $code) {
                $result[] = $answer['columnCode'];
                break;
            }

        }

        return implode(', ', $result);
    }
}
