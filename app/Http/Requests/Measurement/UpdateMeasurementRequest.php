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
            'measurements' => ['sometimes', 'array', new ValidMeasurementKeys()],
            'measurements.*' => ['nullable', new ValidMeasurementValue()],
            'unit' => ['sometimes', 'required', Rule::in(['cm', 'inches'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'measurement_date' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'measurements.array' => 'Measurements must be a valid object',
            'unit.required' => 'Measurement unit is required',
            'unit.in' => 'Unit must be either cm or inches',
            'measurement_date.required' => 'Measurement date is required',
            'measurement_date.date' => 'Invalid date format',
            'measurement_date.before_or_equal' => 'Measurement date cannot be in the future',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('measurements')) {
            $measurements = $this->measurements;
            
            if (is_array($measurements)) {
                $sanitized = [];
                foreach ($measurements as $key => $value) {
                    $sanitizedKey = trim(strtolower(str_replace(' ', '_', $key)));
                    
                    // Allow null to delete fields
                    if ($value === null) {
                        $sanitized[$sanitizedKey] = null;
                    } else {
                        $sanitizedValue = is_string($value) ? trim($value) : $value;
                        $sanitized[$sanitizedKey] = $sanitizedValue;
                    }
                }
                
                $this->merge([
                    'measurements' => $sanitized,
                ]);
            }
        }
        
        if ($this->has('notes')) {
            $this->merge([
                'notes' => $this->notes ? trim($this->notes) : null,
            ]);
        }
    }
}