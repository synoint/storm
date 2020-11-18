<?php

namespace Syno\Storm\Services\Provider;

use Cint\Demand\Factories\ClientFactory;
use Syno\Storm\Document;
use Syno\Cint\Demand\Resources;

class CintDemand
{
    const STATUS_COMPLETE = 5;
    const STATUS_SCREENOUT = 2;
    const STATUS_QUOTA_FULL = 3;
    const STATUS_QUALITY_TERMINATE = 4;
    const STATUS_TIMED_OUT = 6;

    const GUID_PARAMETER = 'RID';

    /** @var ClientFactory */
    private $clientFactory;

    /** @var Resources\Respondent */
    private $respondentResource;

    /**
     * @param Resources\Respondent  $respondentResource
     * @param ClientFactory         $clientFactory
     */
    public function __construct(
        Resources\Respondent    $respondentResource,
        ClientFactory           $clientFactory
    )
    {
        $this->clientFactory        = $clientFactory;
        $this->respondentResource   = $respondentResource;
    }

    /**
     * @param Document\Survey $survey
     * @param Document\Response $surveyResponse
     */
    public function submitComplete(Document\Response $surveyResponse, Document\Survey $survey)
    {
        $this->submitStatus($survey, $surveyResponse->getParameter(self::GUID_PARAMETER), self::STATUS_COMPLETE);
    }

    /**
     * @param Document\Response $surveyResponse
     * @param Document\Survey $survey
     */
    public function submitScreenOut(Document\Response $surveyResponse, Document\Survey $survey)
    {
        $this->submitStatus($survey, $surveyResponse->getParameter(self::GUID_PARAMETER), self::STATUS_SCREENOUT);
    }

    /**
     * @param Document\Response $surveyResponse
     * @param Document\Survey $survey
     */
    public function submitQualityScreenOut(Document\Response $surveyResponse, Document\Survey $survey)
    {
        $this->submitStatus($survey, $surveyResponse->getParameter(self::GUID_PARAMETER), self::STATUS_QUALITY_TERMINATE);
    }

    /**
     * @param Document\Response $surveyResponse
     * @param Document\Survey $survey
     */
    public function submitQuotaFull(Document\Response $surveyResponse, Document\Survey $survey)
    {
        $this->submitStatus($survey, $surveyResponse->getParameter(self::GUID_PARAMETER), self::STATUS_QUOTA_FULL);
    }

    /**
     * @param Document\Survey $survey
     * @param Document\Parameter $guid
     * @param int $statusId
     */
    private function submitStatus(Document\Survey $survey, Document\Parameter $guid, int $statusId)
    {
        $surveyConfig = $survey->getConfig();

        if($surveyConfig->cintDemandApiKey && $guid->getValue()) {
            $this->clientFactory->loadClientApiKey($surveyConfig->cintDemandApiKey);

            $this->respondentResource->changeStatus($guid->getValue(), $statusId);
        }
    }
}
