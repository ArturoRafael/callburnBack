<?php 
namespace App\Http\Services;


class BillingService
{
    public function checkVatId($vatId, $countryCode)
    {

        $countryCode = strtolower($countryCode);
        $response = [
            'error' => [
                'no' => 0,
                'text' => ''
            ]
        ];
        $vatId = str_replace(' ', '', $vatId);
        if (preg_match("/^[a-zA-Z]{2}$/", substr($vatId, 0, 2))) {
            $vn = substr($vatId, 2);
        } else {
            $vn = $vatId;
        }
        $params = array('countryCode' => $countryCode, 'vatNumber' => $vn);
        $client = new \SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");

        if (!$vatId) {
            $response['error']['no'] = -1;
            $response['error']['text'] = 'Vat_id__is_not_provided';
            return $response;
        }

        if (!$countryCode) {
            $response['error']['no'] = -1;
            $response['error']['text'] = 'country_code__is_not_provided';
            return $response;
        }

        if ($countryCode == 'es') {
            $response['error']['no'] = -2;
            $response['error']['text'] = 'vat_id_is_from_spain';
            return $response;
        }

        if (!$client) {
            $response['error']['no'] = -1;
            $response['error']['text'] = 'Vat_id__is_not_valid_SOAP_CLIENT';
            return $response;
        }

        try {
            $r = $client->checkVat($params);
            if ($r->valid == true) {
                $response['error']['no'] = 0;
                $response['error']['text'] = 'Vat_id__is_valid';
                return $response;
            } else {
                $response['error']['no'] = -1;
                $response['error']['text'] = 'Vat_id__is_not_valid';
                return $response;
            }
        } catch (\SoapFault $e) {
            \Log::error($e);
            $response['error']['no'] = -1;
            $response['error']['text'] = 'Vat_id__is_not_valid_something_wrong';
            return $response;
        }
    }
}