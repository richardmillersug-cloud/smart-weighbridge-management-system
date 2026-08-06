<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('drivers.edit');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'license_number' => [
                'required', 'string', 'max:50',
                Rule::unique('drivers', 'license_number')->ignore($this->route('driver')),
            ],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
