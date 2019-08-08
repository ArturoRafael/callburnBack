<?php

namespace App\Http\Requests\Authentication;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends FormRequest
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
            
        ];
    }


    public function messages()
    {
        return [
            'email.required' => 'The :attribute is required',
            'password.required' => 'The :attribute is required', 
            'c_password.required' => 'The :attribute is required', 
            'firstname.required' => 'The :attribute is required',
            'lastname.required' => 'The :attribute is required',
            'businessname.required' => 'The :attribute is required',
            'type_business' => 'The :attribute is required',
            'idrol' => 'The :attribute is required',        
        ];
    }

   
    public function attributes()
    {
        return [
            'user_email' => 'email from the user who signed in'            
        ];
    }
}
