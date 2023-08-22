<?php

namespace Syno\Storm\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Syno\Storm\Document;

class PageMigrationCommand extends Command
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
        $this->setName('page:migration:command');
        $this->setDescription('Migrates survey pages into new page collection');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $start = microtime(true);
        $output->writeln('Migrating pages...');

        $surveys = $this->findSurveys();

        /** Delete pages just in case */
        $this->deleteSurveyPages();

        foreach ($surveys as $survey) {
            $pages = $survey->getPages();

            foreach ($pages as $page) {
                $this->saveSurveyPage($page);
            }
            die('ups');
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

    private function saveSurveyPage(Document\SurveyPage $surveyPage) {
        $this->dm->persist($surveyPage);
        $this->dm->flush();
    }

    private function deleteSurveyPages() {
        $this->dm->createQueryBuilder(Document\SurveyPage::class)
                 ->remove()
                 ->getQuery()
                 ->execute();
    }
}
