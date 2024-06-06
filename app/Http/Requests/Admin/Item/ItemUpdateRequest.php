<?php

namespace App\Http\Requests\Admin\Item;

use Illuminate\Foundation\Http\FormRequest;

class ItemUpdateRequest extends FormRequest
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
           // 'name' => 'required',
//            'image' => 'required|image',
//            'left_image' => 'required|image',
//            'right_image' => 'required|image',
//            'back_image' => 'required|image',
//            'thumbnail' => 'required|image',
            //'price' => 'required|numeric',
//            'pos_x' => 'required',
//            'pos_y' => 'required',
        ];
    }
}
