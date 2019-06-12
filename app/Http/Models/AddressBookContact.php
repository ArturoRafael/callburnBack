<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class AddressBookContact extends Model {

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
	protected $table = 'address_book_contacts';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['phone_number', 'name', 'user_id', 'tariff_id', 'label','type'];


	/**
	 * Get a group of the number.
	 */
	public function groups()
	{
		return $this->belongsToMany('App\Models\AddressBookGroup', 'address_book_group_contact')->withTimestamps();
	}

	/**
	 * Get tariff of the contact
	 */
	public function tariff()
	{
		return $this->belongsTo('App\Models\Tariff')->with('country');
	}



    /**
     * Get all phonenumbers
     */
    public function phonenumbers(){
        return $this->hasMany('App\Models\Phonenumber', 'phone_no', 'phone_number')->orderBy('last_called_at', 'DESC');
    }

}
