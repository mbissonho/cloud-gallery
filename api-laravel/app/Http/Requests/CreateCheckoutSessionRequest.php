<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCheckoutSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image_id' => ['required', 'integer', 'exists:images,id'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
