<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'email' => 'nullable|email',            
            'phone' => 'required|regex:/(^[0-9]{7,15}$)/|min:7|max:15',
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'born_date' => 'nullable|date|date_format:Y-m-d',
            'status' => 'required|integer',
            'date_reservation' => 'nullable|date|date_format:Y-m-d',
            'gender' => 'nullable|in:M,F',
            'array_group' => 'nullable|array',
            'array_group.*' => 'nullable|integer|distinct',
        ];
    }

    public function messages()
    {
        return [
            'status.required' => 'The :attribute is required',
            'phone.required' => 'The :attribute is required',
            'phone.regex' => 'The :attribute should only contain numbers with a minimum length of 7 numbers and a maximum of 15'       
        ];
    }

   
    public function attributes()
    {
        return [
            'phone' => 'phone from the contact',
            'status' => 'status from the contact'            
        ];
    }

}
