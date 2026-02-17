<?php

namespace App\Http\Requests;

use App\Models\ImageStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class EditImageRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => ['exists:App\Models\Tag,id', 'distinct:strict'],
            'description' => 'sometimes|nullable|string|max:255',
            'status' => Rule::in([ImageStatus::AVAILABLE->value, ImageStatus::DISABLED->value])
        ];
    }
}
