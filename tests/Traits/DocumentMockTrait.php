<?php
declare(strict_types=1);

namespace App\Tests\Traits;

use Syno\Storm\Document;

trait DocumentMockTrait
{
    public int $blockId    = 10;
    public int $pageId     = 101;
    public int $questionId = 1001;
    public int $answerId   = 10001;

    public function mockSurvey(): Document\Survey
    {
        $survey = new Document\Survey();
        $survey->setId(rand(1, 1000));
        $survey->setVersion(1);
        $survey->setPublished(true);

        return $survey;
    }

    public function mockPage(array $values = []): Document\Page
    {
        $page = new Document\Page();

        $id     = rand(1, 1000);
        $pageId = $this->pageId;
        $code   = 'P1';

        if (!empty($values)) {
            $id     = $values['id'];
            $pageId = $values['pageId'];
            $code   = $values['code'];
        }

        $page->setId($id);
        $page->setPageId($pageId);
        $page->setCode($code);
        $page->setSortOrder(1);
        $page->setContent('lorem ipsum');

        return $page;
    }

    public function mockQuestion(array $values = []): Document\Question
    {
        $question = new Document\Question();

        $id         = rand(1, 1000);
        $code       = 'Q1';
        $questionId = $this->questionId;

        if (!empty($values)) {
            $id         = $values['id'];
            $code       = $values['code'];
            $questionId = $values['questionId'];
        }

        $question->setId($id);
        $question->setQuestionId($questionId);
        $question->setCode($code);
        $question->setSortOrder(1);
        $question->setRequired(true);
        $question->setText('Question 1');
        $question->setQuestionTypeId(2);

        return $question;
    }

    public function mockAnswer(array $values = []): Document\Answer
    {
        $answer = new Document\Answer();

        $id       = rand(1, 1000);
        $code     = 'A1';
        $answerId = $this->answerId;

        if (!empty($values)) {
            $id       = $values['id'];
            $code     = $values['code'];
            $answerId = $values['answerId'];
        }

        $answer->setId($id);
        $answer->setAnswerId($answerId);
        $answer->setCode($code);
        $answer->setRowCode('');
        $answer->setColumnCode('');
        $answer->setSortOrder(1);
        $answer->setAnswerFieldTypeId(4);
        $answer->setLabel('Answer 1');

        return $answer;
    }

    public function mockRandomization(array $values = []): Document\Randomization
    {
        $randomization = new Document\Randomization();

        $id           = $this->blockId;
        $type         = 'page';
        $isRandomized = true;

        if (!empty($values)) {
            $id           = $values['id'];
            $type         = $values['type'];
            $isRandomized = isset($values['isRandomized']) || false;
        }

        $randomization->setId($id);
        $randomization->setIsRandomized($isRandomized);
        $randomization->setType($type);

        return $randomization;
    }

    public function mockBlockItem(array $values = []): Document\BlockItem
    {
        $blockItem = new Document\BlockItem();

        $id           = rand(1, 1000);
        $blockId      = null;
        $pageId       = $this->pageId;
        $questionId   = null;
        $answerId     = null;
        $weight       = 2;
        $isRandomized = true;

        if (!empty($values)) {
            $id           = $values['id'];
            $blockId      = (isset($values['blockId'])) ? $values['blockId'] : null;
            $pageId       = (isset($values['pageId'])) ? $values['pageId'] : null;
            $questionId   = (isset($values['questionId'])) ? $values['questionId'] : null;
            $answerId     = (isset($values['answerId'])) ? $values['answerId'] : null;
            $weight       = (isset($values['weight'])) ? $values['weight'] : null;
            $isRandomized = (isset($values['isRandomized'])) ? $values['isRandomized'] : null;
        }

        $blockItem->setId($id);

        if (null !== $blockId) {
            $blockItem->setBlock($blockId);
        }

        if (null !== $pageId) {
            $blockItem->setPage($pageId);
        }

        if (null !== $questionId) {
            $blockItem->setQuestion($questionId);
        }

        if (null !== $answerId) {
            $blockItem->setAnswer($answerId);
        }

        $blockItem->setRandomize($isRandomized);
        $blockItem->setWeight($weight);

        return $blockItem;
    }
}
