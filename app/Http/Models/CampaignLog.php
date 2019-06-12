<?php namespace App\Models;

use Jenssegers\Mongodb\Model;

class CampaignLog extends Model{

	protected $connection = 'mongodb_logs';

	/**
	 * The primary key name used by mongo database .
	 *
	 * @var string
	 */
	protected $primaryKey = '_id';


	protected $hidden = ['_id', 'updated_at'];
}