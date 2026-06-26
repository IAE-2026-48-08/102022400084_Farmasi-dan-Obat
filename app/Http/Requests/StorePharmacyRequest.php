<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
class StorePharmacyRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'patient_id'     => 'nullable|string|max:36',
            'appointment_id' => 'nullable|string|max:36',
            'medicine_name'  => 'required|string|max:255',
            'dosage'         => 'required|string|max:100',
            'frequency'      => 'required|string|max:100',
            'quantity'       => 'required|integer|min:1',
            'instructions'   => 'nullable|string',
            'status'         => 'nullable|in:PENDING,PREPARING,READY_TO_PICKUP,DISPENSED',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors()
        ], 422));
    }
}