<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServer extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'url' => 'required|regex:/^https?\:\/\/\w+(\.\w+)*(:[0-9]+)?\/?$/',
            'driver' => 'required|string',
            'api_key' => 'required|string'
        ];
    }
}
