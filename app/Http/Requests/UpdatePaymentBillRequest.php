<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentBillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'house_resident_id' => ['sometimes', 'required', 'exists:tr_house_residents,id'],
            'fee_type_id' => ['sometimes', 'required', 'exists:mst_fee_types,id'],
            'billing_month' => ['sometimes', 'required', 'date'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'status' => ['sometimes', 'required', 'in:paid,unpaid'],
            'paid_at' => ['nullable', 'date'],
            'payment_group_id' => ['nullable', 'string'],
        ];
    }
}
