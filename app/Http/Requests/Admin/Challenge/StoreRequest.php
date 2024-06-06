<?php

namespace App\Http\Requests\Admin\Challenge;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'cover' => 'required',
            'short_description' => 'required',
            'short_description' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'tag' => 'required',
            'max_unworn_value' => 'required',
            'entry_reward' => 'required',
            'requirement' => 'required',
            //'dress_code' => 'required',
            //'prizes' => 'required',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
