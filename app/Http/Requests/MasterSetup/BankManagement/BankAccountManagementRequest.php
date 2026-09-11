<?php

namespace App\Http\Requests\MasterSetup\BankManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankAccountManagementRequest extends FormRequest
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
        $bankAccountId = $this->route('bankAccount')?->id ?? $this->route('bankAccount');

        return [
            'id'                    => ['nullable'],
            'bank_id'               => ['required'],
            'account_number'        => ['required', 'string', 'max:255', Rule::unique('bank_accounts', 'account_number')->ignore($bankAccountId)],
            'depository_remarks'    => ['required', 'string', 'max:50', Rule::unique('bank_accounts', 'depository_remarks')->ignore($bankAccountId)],
        ];
    }

    public function messages(): array
    {
        return [
            'account_number.required'       => 'Bank account is required.',
            'account_number.unique'         => 'Bank account already exists.',
            'depository_remarks.required'   => 'Bank depository remarks is required.',
            'depository_remarks.unique'     => 'Bank depository remarks already exists.',
        ];
    }
}
