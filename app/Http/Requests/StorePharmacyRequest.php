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
            'medicine_name'  => 'required|string|max:255',
            'dosage'         => 'required|string|max:100',
            'frequency'      => 'required|string|max:100',
            'quantity'       => 'required|integer|min:1',
            'instructions'   => 'nullable|string',
            'status'         => 'nullable|in:PENDING,PREPARING,READY_TO_PICKUP,DISPENSED',
        ];
    }

    /**
     * Pastikan response validasi gagal selalu JSON sesuai Standard Integration Contract,
     * terlepas dari header Accept yang dikirim client (mencegah redirect 302 default Laravel).
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors(),
            'meta'    => [
                'service_name' => 'E-Healthcare-Farmasi-dan-Obat',
                'api_version'  => 'v1',
            ],
        ], 422));
    }
}