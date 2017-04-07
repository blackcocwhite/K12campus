<?php

namespace App\Http\Requests\Equipment;

use App\Http\Requests\Request;

class OrderCreateRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_desc' => 'required|max:200',
            'place' => 'required|max:100',
            'channel_id' => 'required',
            'mobile' => 'required'
        ];
    }
}
