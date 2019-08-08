<?php

namespace App\Http\Requests\GroupContact;

use Illuminate\Foundation\Http\FormRequest;

class CreateGroupContact extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id_contact' => 'required|integer',            
            'id_group' => 'required|integer'
        ];
    }

    public function messages()
    {
        return [
            'id_contact.required' => 'The :attribute is required',
            'id_group.required' => 'The :attribute is required'       
        ];
    }

    public function attributes()
    {
        return [
            'id_contact' => 'Id of contact',
            'id_group' => 'Id of group'            
        ];
    }
}
