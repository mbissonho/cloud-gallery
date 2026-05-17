<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class CreateCheckoutSessionRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'image_id' => ['required', 'integer', 'exists:images,id'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
