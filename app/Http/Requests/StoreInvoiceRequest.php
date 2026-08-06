<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('invoices.create');
    }

    public function rules(): array
    {
        return [
            'ticket_id' => ['required', 'integer', 'exists:weighbridge_tickets,id'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999'],
        ];
    }

    public function attributes(): array
    {
        return [
            'amount' => 'amount payable',
        ];
    }
}
