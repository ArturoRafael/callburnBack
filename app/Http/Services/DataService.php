<?php

namespace App\Http\Services;

class DataService {


    /**
     * Filter final collection and return only needed data by $filterData
     *
     * @description
     *
     * $filterData should be an associative array where keys are relationship names and values are array of filled names
     *
     * @param Array $filterData
     * @param Collection $collection
     * @return Collection
     */

    public function filterData($filterData,$collection)
    {

        try {

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }

}