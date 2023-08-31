<?php

namespace Syno\Storm\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Syno\Storm\Document;

class PageCreationCommand extends Command
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;

        parent::__construct();
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this->setName('page:creation:command');
        $this->setDescription('Migrates survey pages into new page collection');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $start = microtime(true);
        $output->writeln('Fetching surveys...');

        $surveys = $this->findSurveys();

//        $this->deletePages(); for testing

        foreach ($surveys as $survey) {
            $output->writeln(sprintf('Creating pages for survey ID: %d and version: %d', $survey->getSurveyId(), $survey->getVersion()));

            $surveyPages = $survey->getPages();

            /** @var Document\SurveyPage $surveyPage */
            foreach ($surveyPages as $surveyPage) {
                if (!$this->pageExists($surveyPage->getPageId(), $survey->getSurveyId(), $survey->getVersion())) {
                    $newPage = new Document\Page();
                    $newPage->setId($surveyPage->getId());
                    $newPage->setPageId($surveyPage->getPageId());
                    $newPage->setSurveyId($survey->getSurveyId());
                    $newPage->setVersion($survey->getVersion());
                    $newPage->setCode($surveyPage->getCode());
                    $newPage->setSortOrder($surveyPage->getSortOrder());
                    $newPage->setTranslations($surveyPage->getTranslations());
                    $newPage->setJavascript($surveyPage->getJavascript());

                    $this->savePage($newPage);
                }
            }
        }

        $output->writeln('');
        $output->writeln('Time: ' . round((microtime(true) - $start), 2) . ' sec');
        $output->writeln('Memory: ' . round(memory_get_peak_usage(true) / 1024 / 1024) . ' MB');
        $output->writeln('Done.');

        return 0;
    }

    /**
     * @return Document\Survey[]
     */
    private function findSurveys(): array
    {
        return $this->dm->createQueryBuilder(Document\Survey::class)
                        ->select()
                        ->field('pages')->exists(true)
                        ->sort('version', -1)
                        ->getQuery()
                        ->execute()
                        ->toArray();
    }

    private function savePage(Document\Page $surveyPage) {
        $this->dm->persist($surveyPage);
        $this->dm->flush();
    }

    private function deletePages() {
        $this->dm->createQueryBuilder(Document\Page::class)
                 ->remove()
                 ->getQuery()
                 ->execute();
    }

    private function pageExists(int $pageId, int $surveyId, int $version)
    {
        return $this->dm->createQueryBuilder(Document\Page::class)
                        ->count()
                        ->field('pageId')->equals($pageId)
            ->field('surveyId')->equals($surveyId)
            ->field('version')->equals($version)
                        ->getQuery()
                        ->execute();
    }
}
