<?php

namespace App\Services;

use App\Models\Campaign;

class MessageLogsService{

    private $messageLog;

    /**
     * Create a new instance of MessageLogs class.
     * @return void
     */
    public function __construct()
    {
        $this->messageLog = new \App\Models\MessageLogs();
    }

    /**
     * Add a new log message when user has created a campaign.
     *
     * @param string $type
     * @param string $text
     * @param Campaign $campaign
     * @return void
     */


    public function createMessageLogForCreate($type, $text, $campaign)
    {

        $data = json_encode($campaign->toArray());
        $status = 'CAMPAIGN_CREATED';
        $this->createSimpleMessageLog($type, $text, $status, $campaign, $data);

    }

    /**
     * Add a new log message when user has updated a campaign.
     *
     * @param string $type
     * @param string $text
     * @param Campaign $oldCampaign
     * @param Campaign $oldCampaign
     * @return void
     */


    public function createMessageLogForUpdate($type, $text, $status ,$newCampaign, $oldCampaign)
    {

        // we should remove relationships from array because array_diff can't work with multidimensional arrays

        $newCampaignArray = $this->removeRelationships($newCampaign->toArray());

        $oldCampaignArray = $this->removeRelationships($oldCampaign->toArray());

        $data = array_diff($newCampaignArray,$oldCampaignArray);

        $newCampaignSchedulations = $newCampaign->schedulations;
        $newCampaignGroups = $newCampaign->groups;

        $data['schedulations'] = $newCampaignSchedulations->toArray();
        $data['groups'] = $newCampaignGroups->toArray();
        $data = json_encode($data);

        $this->createSimpleMessageLog($type, $text, $status, $newCampaign, $data);

    }

    /**
     * Add a new log message when status changed.
     * @param string $type
     * @param string $text
     * @param string $status
     * @param Campaign $campaign
     * @return void
     */

    public function createMessageLogForStatus($type, $text, $status, $campaign)
    {

        $this->createSimpleMessageLog($type, $text, $status, $campaign);

    }


    /**
     * Add a new log message when user has removed a campaign.
     * @param string $type
     * @param string $text
     * @param string $status
     * @param Campaign $campaign
     * @return void
     */

    public function createMessageLogForRemove($type, $text, $status, $campaign)
    {

        $this->createSimpleMessageLog($type, $text, $status, $campaign);

    }


    /**
     * Add a new log message to db.
     * @param string $type
     * @param string $text
     * @param string $status
     * @param Campaign $campaign
     * @param json $data
     * @return void
     */

    private function createSimpleMessageLog($type, $text, $status, $campaign, $data = null) {

        try {

            $this->messageLog->campaign_id = $campaign->_id;
            $this->messageLog->text = $text;
            $this->messageLog->type = $type;
            $this->messageLog->status = $status;
            $this->messageLog->data = $data;

            $this->messageLog->save();

        } catch (\Exception $e) {

            \Log::info($e->getMessage());
        }

    }


    private function removeRelationships($campaignArray) {

        foreach ($campaignArray as $key => $value) {

            if(is_array($value)) {
                unset($campaignArray[$key]);
            }

        }

        return $campaignArray;

    }


}