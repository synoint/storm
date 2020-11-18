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
     * @param int $statusId
     */
    public function submitStatus(Document\Survey $survey, Document\Response $surveyResponse, int $statusId)
    {
        $surveyConfig = $survey->getConfig();

        $guid = $surveyResponse->getParameter(self::GUID_PARAMETER);

        if($surveyConfig->cintDemandApiKey && $guid->getValue()) {
            $this->clientFactory->loadClientApiKey($surveyConfig->cintDemandApiKey);

            $this->respondentResource->changeStatus($guid->getValue(), $statusId);
        }
    }
}
