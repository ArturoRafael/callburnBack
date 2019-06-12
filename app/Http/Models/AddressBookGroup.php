<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressBookGroup extends Model {

	/**
	 * The primary key name used by database .
	 *
	 * @var string
	 */
	protected $primaryKey = '_id';

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'address_book_groups';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'name', 'type','in_progress', 'image_name'];


	/**
	 * Get numbers of this group
	 */
	public function contacts()
	{
		return $this->belongsToMany('App\Models\AddressBookContact', 'address_book_group_contact')
			->with('tariff')->withTimestamps();
	}

	/**
	 * Get count of contacts of the group
	 */
	public function contactCount()
	{
		return $this->contacts()
    		->selectRaw('address_book_group_id, count(*) as count')->groupBy('address_book_group_id');
	}


    public function snippet()
    {
        return $this->belongsTo('App\Models\Snippet');
    }

    /**
	 * Get campaigns of the addressbook
	 */
	public function campaigns()
	{
		return $this->belongsToMany('\App\Models\Campaign', 'campaign_groups');
	}

}
