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

        $ids = [101501, 105117, 110648, 114822, 125403, 126830, 131647, 134745, 137700, 146530, 165174, 165246, 172100, 175273, 186295, 187591, 190435, 196305, 199773, 206430, 212326, 217380, 222900, 226592, 233664, 235121, 237772, 246725, 248135, 253665, 267504, 271470, 283186, 288437, 295885, 304074, 305673, 308382, 310979, 316738, 321953, 329422, 329569, 331351, 333676, 339356, 359475, 362424, 375807, 385748, 396724, 398235, 401167, 402114, 403644, 413449, 415840, 432548, 450866, 457183, 469078, 470292, 473611, 486807, 491015, 491680, 495670, 497856, 506606, 507361, 508785, 513894, 517537, 525506, 526439, 527221, 556393, 560982, 571888, 573629, 579025, 583406, 583888, 586468, 586556, 614055, 615014, 634396, 638938, 644632, 645209, 646281, 650492, 654595, 657409, 657522, 661083, 669065, 671197, 687737, 719153, 725053, 729334, 729449, 748318, 749033, 752747, 753379, 764436, 770324, 777532, 795896, 804859, 815814, 821691, 823951, 824811, 832576, 832690, 833210, 838225, 840681, 851100, 856909, 871471, 885651, 911233, 914994, 919274, 919826, 932149, 942977, 944040, 945894, 946694, 958579, 960713, 976089, 980251, 981728, 982335, 992536, 998230, 2202771, 2202924, 2202934, 2202942, 2303953, 2303964, 2304082, 2304321, 2304322, 2304432, 2304505, 2304550, 2304557, 2304560, 2304561, 2304620, 526879, 632494, 650867, 654484, 703441, 718812, 726357, 2202360, 113790, 148383, 175337, 239218, 279330, 280184, 297199, 414499, 421502, 460052, 528431, 583987, 595051, 614686, 624351, 643361, 646505, 661667, 664551, 682972, 718485, 832883, 845551, 961461, 961860, 972283, 2000086, 2000429, 2000546, 2000564, 2000570, 2000952, 2001084];

        foreach ($ids as $id) {
            $surveys = $this->findSurveys($id);
            /** @var Document\Survey $survey */
            foreach ($surveys as $survey) {
                if ($survey->getSurveyId() == 165246 && $survey->getVersion() == 102) {
                    $output->writeln(sprintf('Updating media paths for survey_id: %d, version: %d', $survey->getSurveyId(), $survey->getVersion()));
                    $pages       = $this->findPagesBySurvey($survey);
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
            }
        }

        $output->writeln('');
        $output->writeln('Time: ' . round((microtime(true) - $start), 2) . ' sec');
        $output->writeln('Memory: ' . round(memory_get_peak_usage(true) / 1024 / 1024) . ' MB');
        $output->writeln('Done.');

        return 0;
    }

    private function findSurveys(int $surveyId): ?array
    {
        return $this->dm->createQueryBuilder(Document\Survey::class)
            ->select()
            ->field('surveyId')->equals($surveyId)
            ->getQuery()
            ->execute()
            ->toArray();
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
