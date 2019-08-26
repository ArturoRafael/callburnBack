<?php


namespace App\Services;

use App\Models\BlacklistIp;
class BlackListIpService
{
    /**
     * Object of BlackListIp class
     *
     * @var App\Models\BlackListIp
     */
    private $blacklistIp;

    /**
     * Create a new instance of BlackListIpService class.
     *
     * @return void
     */
    public function __construct()
    {
        $this->blacklistIp = new BlacklistIp();
    }


    /**
     * @return App\Models\BlackListIp
     */
    public function getBlacklistIp($ip,$id)
    {
        return $this->blacklistIp->where('user_id',$id)->where('ip', $ip)->first();
    }
    /**
     * @return App\Models\BlackListIp
     */
    public function getBlacklistIpByIp($ip)
    {
        return $this->blacklistIp->where('ip', $ip)->where('user_id', NULL)->first();
    }

    /**
     * Create BlacklistIp
     * @return Collection
     */
    public function createBlacklistIp($data)
    {
        return $this->blacklistIp->create($data);
    }


    /**
     * Update BlacklistIp
     * @return Collection
     */
    public function update($id,$data)
    {
        return $this->blacklistIp->where('_id',$id)->update($data);
    }

}