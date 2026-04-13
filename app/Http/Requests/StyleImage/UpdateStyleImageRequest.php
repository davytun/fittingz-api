<?php

namespace App\Http\Requests\StyleImage;

use App\Http\Requests\BaseRequest;

class UpdateStyleImageRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category'    => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.max'    => 'Category cannot exceed 100 characters',
            'description.max' => 'Description cannot exceed 2000 characters',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('category')) {
            $this->merge(['category' => $this->category ? trim(strtolower($this->category)) : null]);
        }

        if ($this->has('description')) {
            $this->merge(['description' => $this->description ? trim($this->description) : null]);
        }
    }
}
