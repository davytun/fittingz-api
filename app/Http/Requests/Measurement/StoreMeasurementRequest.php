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
            'name'             => ['required', 'string', 'max:255'],
            'fields'           => ['required', 'array', 'min:1', new ValidMeasurementKeys()],
            'fields.*'         => ['required', new ValidMeasurementValue()],
            'unit'             => ['required', Rule::in(['cm', 'inches'])],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'measurement_date' => ['required', 'date', 'before_or_equal:today'],
            'is_default'       => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                => 'Measurement name is required',
            'fields.required'              => 'Measurement fields are required',
            'fields.array'                 => 'Fields must be a valid object',
            'fields.min'                   => 'At least one measurement field is required',
            'fields.*.required'            => 'All measurement values are required',
            'unit.required'                => 'Measurement unit is required',
            'unit.in'                      => 'Unit must be either cm or inches',
            'measurement_date.required'    => 'Measurement date is required',
            'measurement_date.date'        => 'Invalid date format',
            'measurement_date.before_or_equal' => 'Measurement date cannot be in the future',
        ];
    }

    protected function prepareForValidation()
    {
        $fields = $this->fields;

        if (is_array($fields)) {
            $sanitized = [];
            foreach ($fields as $key => $value) {
                $sanitizedKey = trim(strtolower(str_replace(' ', '_', $key)));
                $sanitizedValue = is_string($value) ? trim($value) : $value;
                $sanitized[$sanitizedKey] = $sanitizedValue;
            }

            $this->merge([
                'fields' => $sanitized,
                'notes'  => $this->notes ? trim($this->notes) : null,
                'name'   => $this->name ? trim($this->name) : $this->name,
            ]);
        }
    }
}
