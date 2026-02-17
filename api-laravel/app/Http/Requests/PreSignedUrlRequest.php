<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class PreSignedUrlRequest extends BasePreSignedUrlRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'file_title' => 'required|string',
                'file_tag_ids' => 'nullable|array',
                'file_tag_ids.*' => ['exists:App\Models\Tag,id', 'distinct:strict'],
                'file_description' => 'sometimes|nullable|string|max:255'
            ]
        );
    }
}
