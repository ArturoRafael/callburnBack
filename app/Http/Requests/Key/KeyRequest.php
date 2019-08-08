<?php

namespace App\Http\Requests\Key;

use Illuminate\Foundation\Http\FormRequest;

class KeyRequest extends FormRequest
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
            'keypad_value' => 'required|string|max:1',
            'id_key_event_type' => 'required|integer',
            'label' => 'nullable|string|max:100',
            'first_action_text' => 'nullable|string',
            'first_action_audio' => 'nullable|string|max:255',
            'post_action_text' => 'nullable|string',
            'post_action_audio' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:200',
            'simultaneus_transfer_limit' => 'nullable|string|max:50',
            'name_text' => 'nullable|string|max:200',
            'transfer_text' => 'nullable|string',
            'transfer_audio' => 'nullable|string|max:255',
            'has_sub_key_menu' => 'nullable|boolean',
            'id_parent_key' => 'nullable|integer',
        ];
    }
}
