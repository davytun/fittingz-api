<?php

namespace App\Http\Requests\Measurement;

use App\Http\Requests\BaseRequest;
use App\Rules\ValidMeasurementKeys;
use App\Rules\ValidMeasurementValue;
use Illuminate\Validation\Rule;

class StoreMeasurementRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'measurements' => ['required', 'array', 'min:1', new ValidMeasurementKeys()],
            'measurements.*' => ['required', new ValidMeasurementValue()],
            'unit' => ['required', Rule::in(['cm', 'inches'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'measurement_date' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'measurements.required' => 'Measurements are required',
            'measurements.array' => 'Measurements must be a valid object',
            'measurements.min' => 'At least one measurement field is required',
            'measurements.*.required' => 'All measurement values are required',
            'unit.required' => 'Measurement unit is required',
            'unit.in' => 'Unit must be either cm or inches',
            'measurement_date.required' => 'Measurement date is required',
            'measurement_date.date' => 'Invalid date format',
            'measurement_date.before_or_equal' => 'Measurement date cannot be in the future',
        ];
    }

    protected function prepareForValidation()
    {
        $measurements = $this->measurements;
        
        if (is_array($measurements)) {
            $sanitized = [];
            foreach ($measurements as $key => $value) {
                $sanitizedKey = trim(strtolower(str_replace(' ', '_', $key)));
                $sanitizedValue = is_string($value) ? trim($value) : $value;
                $sanitized[$sanitizedKey] = $sanitizedValue;
            }
            
            $this->merge([
                'measurements' => $sanitized,
                'notes' => $this->notes ? trim($this->notes) : null,
            ]);
        }
    }
}
