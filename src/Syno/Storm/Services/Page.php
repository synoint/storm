<?php

namespace Syno\Storm\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class Page
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function save(Document\Page $page)
    {
        $this->dm->persist($page);
        $this->dm->flush();
    }

    public function findBySurvey(Document\Survey $survey): Collection
    {
        $pages = $this->dm->getRepository(Document\Page::class)->findBy(
            [
                'surveyId' => $survey->getSurveyId(),
                'version'  => $survey->getVersion(),
            ]
        );

        if ($pages) {
            foreach ($pages as $page) {
                $this->dm->detach($page);
            }
        }

        return new ArrayCollection($pages);
    }

    public function deleteBySurvey(Document\Survey $survey)
    {
        $pages = $this->dm->getRepository(Document\Page::class)->findBy(
            [
                'surveyId' => $survey->getSurveyId(),
                'version'  => $survey->getVersion(),
            ]
        );

        foreach ($pages as $page) {
            $this->dm->remove($page);
            $this->dm->flush();
        }
    }

    public function media(Document\Survey $survey)
    {
        $pages       = $this->findPagesBySurvey($survey);
        $patern1     = "https://syno-media-input.s3.eu-west-1.amazonaws.com";
        $patern2     = "https://dk8uke8mqjln7.cloudfront.net";
        $replacement = "https://ddtos04263ciu.cloudfront.net/survey";

        $survey->setLogoPath(str_replace($patern1, $replacement, $survey->getLogoPath()));
        $survey->setLogoPath(str_replace($patern2, $replacement, $survey->getLogoPath()));

        $this->dm->persist($survey);
        $this->dm->flush();

        /** @var Document\Page $page */
        foreach ($pages as $page) {
            $page->setContent(str_replace($patern1, $replacement, $page->getContent()));
            $page->setContent(str_replace($patern2, $replacement, $page->getContent()));
            /** @var Document\PageTranslation $translation */
            foreach ($page->getTranslations() as $translation) {
                $translation->setContent(str_replace($patern1, $replacement, $translation->getContent()));
                $translation->setContent(str_replace($patern2, $replacement, $translation->getContent()));
            }

            /** @var Document\Question $question */
            foreach ($page->getQuestions() as $question) {
                $question->setText(str_replace($patern1, $replacement, $question->getText()));
                $question->setText(str_replace($patern2, $replacement, $question->getText()));

                /** @var Document\QuestionTranslation $translation */
                foreach ($question->getTranslations() as $translation) {
                    $translation->setText(str_replace($patern1, $replacement, $translation->getText()));
                    $translation->setText(str_replace($patern2, $replacement, $translation->getText()));
                }
                /** @var Document\Answer $answer */
                foreach ($question->getAnswers() as $answer) {
                    /** @var Document\AnswerTranslation $translation */
                    foreach ($answer->getTranslations() as $translation) {
                        $translation->setLabel(str_replace($patern1, $replacement, $translation->getLabel()));
                        $translation->setLabel(str_replace($patern2, $replacement, $translation->getLabel()));
                        $translation->setRowLabel(str_replace($patern1, $replacement, $translation->getRowLabel()));
                        $translation->setRowLabel(str_replace($patern2, $replacement, $translation->getRowLabel()));
                        $translation->setColumnLabel(str_replace($patern1, $replacement, $translation->getColumnLabel()));
                        $translation->setColumnLabel(str_replace($patern2, $replacement, $translation->getColumnLabel()));
                    }
                }
            }

            $this->save($page);
        }
    }

    private function findPagesBySurvey(Document\Survey $survey)
    {
        return $this->dm->getRepository(Document\Page::class)->findBy(
            [
                'surveyId' => $survey->getSurveyId(),
                'version'  => $survey->getVersion(),
            ]
        );
    }
}
