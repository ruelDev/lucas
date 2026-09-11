<?php

namespace App\Http\Requests\MasterSetup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankManagementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bankId = $this->route('bank')?->id ?? $this->route('bank');

        return [
            'name'          => ['required', 'string', 'max:255', Rule::unique('banks', 'name')->ignore($bankId)],
            'abbreviation'  => ['required', 'string', 'max:50',  Rule::unique('banks', 'abbreviation')->ignore($bankId)],
            'description'   => ['nullable', 'string']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Bank name is required.',
            'name.unique'           => 'Bank name already exists.',
            'abbreviation.required' => 'Abbreviation is required.',
            'abbreviation.unique'   => 'Abbreviation already exists.',
        ];
    }
}
