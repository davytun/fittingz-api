<?php

namespace App\Http\Requests\StyleImage;

use App\Http\Requests\BaseRequest;

class StoreStyleImageRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images'      => ['required', 'array', 'min:1'],
            'images.*'    => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'category'    => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.required'  => 'At least one image is required',
            'images.array'     => 'Images must be an array',
            'images.min'       => 'At least one image is required',
            'images.*.image'   => 'Each file must be an image',
            'images.*.mimes'   => 'Each image must be jpeg, png, jpg, or webp',
            'images.*.max'     => 'Each image size cannot exceed 5MB',
            'category.max'     => 'Category cannot exceed 100 characters',
            'description.max'  => 'Description cannot exceed 2000 characters',
        ];
    }

    /**
     * Gather uploaded images from any field name, normalised under "images".
     */
    protected function prepareForValidation(): void
    {
        // Collect files from all field names, then re-merge under "images"
        // so validation rules apply uniformly.
        $allFiles = collect($this->allFiles())->flatten()->filter()->values();

        if ($allFiles->isNotEmpty() && !$this->hasFile('images')) {
            // Files arrived under arbitrary field names — normalise them.
            $this->files->set('images', $allFiles->all());
        }

        if ($this->has('category')) {
            $this->merge(['category' => $this->category ? trim(strtolower($this->category)) : null]);
        }

        if ($this->has('description')) {
            $this->merge(['description' => $this->description ? trim($this->description) : null]);
        }
    }
}
