<?php

namespace App\Http\Requests;

use App\Models\Resident;
use App\Models\HouseResident;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHouseResidentRequest extends FormRequest
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
        // Retrieve start_date either from request or current record
        $startDate = $this->input('start_date');
        if (!$startDate && $this->route('house_resident')) {
            $association = $this->route('house_resident');
            if ($association instanceof HouseResident) {
                $startDate = $association->start_date?->format('Y-m-d');
            } else {
                $rec = HouseResident::find($association);
                if ($rec) {
                    $startDate = $rec->start_date?->format('Y-m-d');
                }
            }
        }

        $endDateRules = ['nullable', 'date'];
        if ($startDate) {
            $endDateRules[] = 'after_or_equal:' . $startDate;
        }

        // Retrieve resident_id either from request or current record
        $residentId = $this->input('resident_id');
        if (!$residentId && $this->route('house_resident')) {
            $association = $this->route('house_resident');
            if ($association instanceof HouseResident) {
                $residentId = $association->resident_id;
            } else {
                $rec = HouseResident::find($association);
                if ($rec) {
                    $residentId = $rec->resident_id;
                }
            }
        }

        if ($residentId) {
            $resident = Resident::find($residentId);
            if ($resident && $resident->resident_status === 'contract') {
                $endDateRules[] = 'sometimes';
                $endDateRules[] = 'required';
                // Remove 'nullable' if it was added
                $endDateRules = array_filter($endDateRules, fn($rule) => $rule !== 'nullable');
            }
        }

        return [
            'house_id' => ['sometimes', 'required', 'exists:mst_houses,id'],
            'resident_id' => ['sometimes', 'required', 'exists:mst_residents,id'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => array_values($endDateRules),
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
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
