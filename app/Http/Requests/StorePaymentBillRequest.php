<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentBillRequest extends FormRequest
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
            'months' => ['required', 'array', 'min:1'],
            'months.*' => ['required', 'date_format:Y-m'],
            'house_resident_id' => ['required', 'exists:tr_house_residents,id'],
            'fee_type_id' => ['required', 'exists:mst_fee_types,id'],
            'amount_per_month' => ['required', 'numeric', 'min:0'],
            'paid_at' => ['required', 'date'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $months = $this->input('months', []);
            $feeTypeId = $this->input('fee_type_id');

            if (is_array($months) && count($months) > 1 && $feeTypeId) {
                $feeType = \App\Models\FeeType::find($feeTypeId);
                if ($feeType && $feeType->billing_cycle !== 'monthly') {
                    $validator->errors()->add(
                        'months',
                        'Pembayaran lebih dari 1 bulan hanya diperbolehkan untuk tipe iuran bulanan (monthly).'
                    );
                }
            }
        });
    }
}
