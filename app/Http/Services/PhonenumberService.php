<?php

namespace App\Http\Services;

use App\Models\Phonenumber;
use App\Models\ArchivedPhonenumber;


class PhonenumberService
{
    /**
     * Object of Phonenumber class for working with DB.
     *
     * @var phonenumber
     */
    protected $phonenumber;

    /**
     * Object of archivedPhonenumber class for working with DB.
     *
     * @var archivedPhonenumber
     */
    protected $archivedPhonenumber;

    public function __construct()
    {
        $this->phonenumber = new Phonenumber();
        $this->archivedPhonenumber = new ArchivedPhonenumber();
    }


    public function getPhonenumberById($id){
        return $this->phonenumber->where('_id', $id)->first();
    }

    public function getArchivedPhonenumberById($id){
        return $this->archivedPhonenumber->where('_id', $id)->first();
    }

    public function updatePhonenumber($id,$data){
        return $this->phonenumber->where('_id', $id)->update($data);
    }

    public function updateArchivedPhonenumber($id,$data){
        return $this->archivedPhonenumber->where('_id', $id)->update($data);
    }
}