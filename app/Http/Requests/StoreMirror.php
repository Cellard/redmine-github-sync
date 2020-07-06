<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMirror extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'left' => 'required',
            'right' => 'required',
            'owner' => 'required|integer',
            'config' => 'required|string'
        ];
    }
}
