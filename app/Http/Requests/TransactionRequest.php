<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransactionRequest extends FormRequest
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
            'desc' => 'nullable|string|max:255',
            'code' => 'required|string|max:50|unique:transaction_headers,code,' . $this->id,
            'rate_euro' => 'required|numeric',
            'date_paid' => 'required|date',

            'details' => 'required|array|min:1',
            'details.*.transaction_category_id' => 'required|exists:categories,id',
            'details.*.name' => 'required|string|max:255',
            'details.*.value_idr' => 'required|numeric|min:0',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        throw new HttpResponseException(response()->json([
            'success' => false,
            'status' => 422,
            'message' => 'Validation failed',
            'data' => null,
            'errors' => $errors,
        ], 422));
    }
}
