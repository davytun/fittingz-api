<?php

namespace App\Http\Requests\Measurement;

use App\Http\Requests\BaseRequest;
use App\Rules\ValidMeasurementKeys;
use App\Rules\ValidMeasurementValue;
use Illuminate\Validation\Rule;

class UpdateMeasurementRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['sometimes', 'string', 'max:255'],
            'fields'           => ['sometimes', 'array', new ValidMeasurementKeys()],
            'fields.*'         => ['nullable', new ValidMeasurementValue()],
            'unit'             => ['sometimes', 'required', Rule::in(['cm', 'inches'])],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'measurement_date' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'is_default'       => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'fields.array'                     => 'Fields must be a valid object',
            'unit.required'                    => 'Measurement unit is required',
            'unit.in'                          => 'Unit must be either cm or inches',
            'measurement_date.required'        => 'Measurement date is required',
            'measurement_date.date'            => 'Invalid date format',
            'measurement_date.before_or_equal' => 'Measurement date cannot be in the future',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('fields')) {
            $fields = $this->fields;

            if (is_array($fields)) {
                $sanitized = [];
                foreach ($fields as $key => $value) {
                    $sanitizedKey = trim(strtolower(str_replace(' ', '_', $key)));

                    // Allow null to delete a field
                    $sanitized[$sanitizedKey] = ($value === null) ? null : (is_string($value) ? trim($value) : $value);
                }

                $this->merge(['fields' => $sanitized]);
            }
        }

        if ($this->has('name')) {
            $this->merge(['name' => trim($this->name)]);
        }

        if ($this->has('notes')) {
            $this->merge(['notes' => $this->notes ? trim($this->notes) : null]);
        }
    }
}