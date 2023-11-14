<?php

namespace Syno\Storm\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Syno\Storm\Document;
use Syno\Storm\Services;

class MediaPathsMigrationCommand extends Command
{
    private DocumentManager $dm;
    private Services\Page   $pageService;
    private Services\Survey $surveyService;

    public function __construct(DocumentManager $dm, Services\Page $pageService, Services\Survey $surveyService)
    {
        $this->dm            = $dm;
        $this->pageService   = $pageService;
        $this->surveyService = $surveyService;

        parent::__construct();
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this->setName('media:path:migration:command');
        $this->setDescription('Changes media file paths to cdn ones');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $start = microtime(true);
        $output->writeln('Fetching surveys...');

        $surveys = $this->findSurveys();

        /** @var Document\Survey $survey */
        foreach ($surveys as $survey) {
            $output->writeln(sprintf('Updating media paths for survey_id: %d, version: %d', $survey->getSurveyId(), $survey->getVersion()));
            $pages       = $this->pageService->findBySurvey($survey);
            $patern1     = "https://syno-media-input.s3.eu-west-1.amazonaws.com";
            $patern2     = "https://dk8uke8mqjln7.cloudfront.net";
            $replacement = "https://ddtos04263ciu.cloudfront.net/survey";

            $survey->setLogoPath(str_replace($patern1, $replacement, $survey->getLogoPath()));
            $survey->setLogoPath(str_replace($patern2, $replacement, $survey->getLogoPath()));

            $this->surveyService->save($survey);

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
                $this->pageService->save($page);
            }
        }

        $output->writeln('');
        $output->writeln('Time: ' . round((microtime(true) - $start), 2) . ' sec');
        $output->writeln('Memory: ' . round(memory_get_peak_usage(true) / 1024 / 1024) . ' MB');
        $output->writeln('Done.');

        return 0;
    }

    private function findSurveys()
    {
        return $this->dm->createQueryBuilder(Document\Survey::class)
            ->select()
            ->getQuery()
            ->execute();
    }
}
