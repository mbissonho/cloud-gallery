<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class EditUserProfileRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100'
            ],
            'bio' => [
                'sometimes',
                'min:0',
                'max:1500'
            ],
            'password' => [
                'sometimes',
                'string',
                'max:50',
                Password::min(8),
                Rule::requiredIf(function (){
                    return !empty($this->data('new_password'));
                })
            ],
            'new_password' => [
                'sometimes',
                'string',
                'max:50',
                Password::min(8),
            ]
        ];
    }
}
