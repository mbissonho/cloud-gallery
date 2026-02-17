<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class BasePreSignedUrlRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'filename' => 'required|string',
            'content_type' => 'required|string'
        ];
    }
}
