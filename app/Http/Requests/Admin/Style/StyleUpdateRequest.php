<?php

namespace App\Http\Requests\Admin\Style;

use Illuminate\Foundation\Http\FormRequest;

class StyleUpdateRequest extends FormRequest
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
            'name' => 'required|unique:styles,name,' . $this->route('style')
        ];
    }
}