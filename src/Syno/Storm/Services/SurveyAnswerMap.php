<?php
declare(strict_types=1);

namespace Syno\Storm\Services;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Syno\Storm\Traits\HtmlAware;

class SurveyAnswerMap
{
    use HtmlAware;

    private Page                   $pageService;
    private TagAwareCacheInterface $cache;

    public function __construct(Page $pageService, TagAwareCacheInterface $surveyCache)
    {
        $this->pageService = $pageService;
        $this->cache       = $surveyCache;
    }

    public function get(int $surveyId, int $version, string $locale): array
    {
        $key = 'answer_map_' . $surveyId . '_' . $version . '_' . $locale;

        return $this->cache->get($key, function (ItemInterface $item) use ($surveyId, $version, $locale) {
            $item->expiresAfter(900);

            $result = [];
            $surveyAnswers = $this->pageService->getAnswersForDataLayer($surveyId, $version);
            if ($surveyAnswers) {
                $result = $this->convertToMapByAnswerId($surveyAnswers, $locale);
            }

            return $result;
        });
    }

    private function convertToMapByAnswerId($answerData, string $locale): array
    {
        $map = [];
        foreach ($answerData as $page) {
            if (!isset($page['questions'])) {
                continue;
            }
            foreach ($page['questions'] as $question) {
                if (!isset($question['answers'])) {
                    continue;
                }
                foreach ($question['answers'] as $answer) {

                    $mapItem = [
                        'pageCode'     => $page['code'],
                        'questionCode' => $question['code'],
                        'questionText' => !empty($question['text']) ? $this->cleanMarkup($question['text']) : '',
                        'questionType' => $question['questionTypeId']
                    ];

                    $translations = $answer['translations'] ?? [];

                    if (isset($answer['rowCode']) || isset($answer['columnCode'])) {
                        $mapItem['rowCode']    = $answer['rowCode'] ?? null;
                        $mapItem['rowLabel']   = $this->translate($locale, $translations, 'rowLabel');
                        $mapItem['columnCode'] = $answer['columnCode'] ?? null;
                        $mapItem['columnLabel'] = $this->translate($locale, $translations, 'columnLabel');
                    } else {
                        $mapItem['code']  = $answer['code'] ?? null;
                        $mapItem['label'] = $this->translate($locale, $translations, 'label');
                    }

                    $answerId = (int) $answer['answerId'];
                    $map[$answerId] = $mapItem;
                }
            }
        }

        return $map;
    }

    private function translate(string $locale, array $translations, string $key):? string
    {
        foreach ($translations as $translation) {
            if ($translation['locale'] === $locale && isset($translation[$key])) {
                return $translation[$key];
            }
        }

        return null;
    }


}
