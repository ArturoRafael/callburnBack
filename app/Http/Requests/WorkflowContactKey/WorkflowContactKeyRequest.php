<?php

namespace App\Http\Requests\WorkflowContactKey;

use Illuminate\Foundation\Http\FormRequest;

class WorkflowContactKeyRequest extends FormRequest
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
            'id_workflow' => 'required|integer',
            'id_contact_workflow' => 'required|integer',
            'id_key' => 'required|integer',
            'date_time' => 'nullable|date|date_format:Y-m-d H:m:s',
        ];
    }
}
