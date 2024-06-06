<?php

namespace App\Http\Requests\Admin\Language;

use Illuminate\Foundation\Http\FormRequest;

class LanguageUpdateRequest extends FormRequest
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
            'name' => 'required|unique:languages,name,' . $this->route('language'),
            'symbol' => 'required|unique:languages,symbol,' . $this->route('language'),
        ];
    }
}