<?php

namespace Syno\Storm\Services;

use JWadhams;

class Logic
{

    public function applyScreenoutRule(array $responsesArray, $screenoutLogic)
    {
        $redirectUrl = null;

        $screenouts = json_decode($screenoutLogic, true);

        foreach($screenouts as $screenout) {

            $logicData = [];

            foreach($screenout['ruleQuestionIds'] as $questionId){
                if (isset($responsesArray[$questionId])) {
                    foreach($responsesArray[$questionId] as $answer){

                        $logicData['q'.$questionId.$answer] =  1;
                    }
                }
            }

             if(JWadhams\JsonLogic::apply($screenout['rule'], $logicData)){
                 $redirectUrl = $screenout['url'];
             }
        }
        return $redirectUrl;
    }
}