<?php

namespace App\Http\Requests;

use App\Models\Resident;
use Illuminate\Foundation\Http\FormRequest;

class StoreHouseResidentRequest extends FormRequest
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
        $rules = [
            'house_id' => ['required', 'exists:mst_houses,id'],
            'resident_id' => ['required', 'exists:mst_residents,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['required', 'boolean'],
        ];

        $residentId = $this->input('resident_id');
        if ($residentId) {
            $resident = Resident::find($residentId);
            if ($resident && $resident->resident_status === 'contract') {
                $rules['end_date'] = ['required', 'date', 'after_or_equal:start_date'];
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'end_date.required' => 'The end date is required when the resident status is contract.',
        ];
    }
}
