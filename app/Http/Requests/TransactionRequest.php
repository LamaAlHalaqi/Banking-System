<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'account_id' => 'required|exists:accounts,id',
            'destination_account_id' => 'nullable|exists:accounts,id|different:account_id',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|string|in:deposit,withdrawal,transfer,payment',
            'description' => 'nullable|string|max:500',
            'scheduled_at' => 'nullable|date|after:now',
        ];
    }
}