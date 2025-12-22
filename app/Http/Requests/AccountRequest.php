<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
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
            'account_type' => 'required|string|in:savings,checking,loan,investment',
            'initial_deposit' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:1',
            'overdraft_limit' => 'nullable|numeric|min:0',
             'parent_account_id' => 'nullable|exists:accounts,id',
        ];
    }
}
