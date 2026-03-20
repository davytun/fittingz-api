<?php

namespace App\Http\Requests\Style;

use App\Http\Requests\BaseRequest;

class StoreStyleRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // 5MB
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Style image is required',
            'image.image' => 'File must be an image',
            'image.mimes' => 'Image must be jpeg, png, jpg, or webp',
            'image.max' => 'Image size cannot exceed 5MB',
            'title.max' => 'Title cannot exceed 255 characters',
            'description.max' => 'Description cannot exceed 2000 characters',
            'category.max' => 'Category cannot exceed 100 characters',
            'tags.array' => 'Tags must be an array',
            'tags.*.string' => 'Each tag must be a string',
            'tags.*.max' => 'Each tag cannot exceed 50 characters',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'title' => $this->title ? trim($this->title) : null,
            'description' => $this->description ? trim($this->description) : null,
            'category' => $this->category ? trim(strtolower($this->category)) : null,
        ]);

        // Clean tags
        if ($this->has('tags') && is_array($this->tags)) {
            $cleanTags = array_map(fn($tag) => trim(strtolower($tag)), $this->tags);
            $cleanTags = array_filter($cleanTags); // Remove empty
            $cleanTags = array_unique($cleanTags); // Remove duplicates
            $this->merge(['tags' => array_values($cleanTags)]);
        }
    }
}