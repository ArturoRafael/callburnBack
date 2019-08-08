<?php

namespace App\Http\Requests\GroupContact;

use Illuminate\Foundation\Http\FormRequest;

class GroupCreateContacts extends FormRequest
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
            'array_contact' => 'required|array', 
            'array_contact.*' => 'required|integer|min:1|distinct',            
            'description' => 'required|string|max:200'
        ];
    }

    public function messages()
    {
        return [
            'array_contact.required' => 'The :attribute is array required',
            'array_contact.*.required' => 'The :attribute is required and is integer',
            'description.required' => 'The :attribute is required'       
        ];
    }

    public function attributes()
    {
        return [
            'array_contact' => 'Ids of contacts',
            'description' => 'Name from the group contact'            
        ];
    }
}
