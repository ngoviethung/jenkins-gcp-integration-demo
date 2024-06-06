<?php

namespace App\Http\Requests\Admin\Item;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
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
//            'image' => 'required|image',
//            'left_image' => 'required|image',
//            'right_image' => 'required|image',
//            'back_image' => 'required|image',
//            'thumbnail' => 'required|image',
            'price' => 'required|numeric',
            'pos_x' => 'required',
            'pos_y' => 'required',
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
