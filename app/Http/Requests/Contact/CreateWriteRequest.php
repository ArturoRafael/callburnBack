<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class CreateWriteRequest extends FormRequest
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
            'phone' => 'required|array',            
            'phone.*' => 'required|regex:/(^[0-9]{7,15}$)/|min:7|max:15',
            'array_group' => 'nullable|array',
            'array_group.*' => 'nullable|integer|distinct',
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => 'The :attribute is required',
            'phone.*.regex' => 'The :attribute should only contain numbers with a minimum length of 9 numbers and a maximum of 15'       
        ];
    }

   
    public function attributes()
    {
        return [
            'phone' => 'phone from the contact'                        
        ];
    }

}
