<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('vehicles.edit');
    }

    public function rules(): array
    {
        return [
            'plate_number' => [
                'required', 'string', 'max:30',
                Rule::unique('vehicles', 'plate_number')->ignore($this->route('vehicle')),
            ],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'numeric', 'min:0', 'max:200000'],
            'preset_tare' => ['nullable', 'numeric', 'min:0', 'max:200000'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('plate_number')) {
            $this->merge(['plate_number' => strtoupper(trim($this->string('plate_number')))]);
        }
    }
}
