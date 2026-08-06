<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.manage');
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_phone' => ['nullable', 'string', 'max:30'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_logo' => ['nullable', 'string', 'max:500'],
            'currency' => ['required', 'string', 'max:10'],
            'default_rate' => ['nullable', 'numeric', 'min:0'],
            'ticket_prefix' => ['required', 'string', 'max:10', 'alpha_num:ascii'],
            'invoice_prefix' => ['required', 'string', 'max:10', 'alpha_num:ascii'],
            'receipt_prefix' => ['required', 'string', 'max:10', 'alpha_num:ascii'],
            'weight_unit' => ['nullable', 'string', 'max:10'],
            'deduction_enabled' => ['nullable', 'in:0,1'],
            'default_deduction_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'allow_manual_weight' => ['nullable', 'in:0,1'],
            'stable_weight_timeout' => ['nullable', 'integer', 'min:1', 'max:60'],
            'ticket_template' => ['nullable', 'string', 'max:50'],
            'invoice_template' => ['nullable', 'string', 'max:50'],
            'receipt_template' => ['nullable', 'string', 'max:50'],
            'default_printer' => ['nullable', 'string', 'max:100'],
            'session_timeout_minutes' => ['nullable', 'integer', 'min:15', 'max:1440'],
            'password_min_length' => ['nullable', 'integer', 'min:6', 'max:64'],
        ];
    }
}
