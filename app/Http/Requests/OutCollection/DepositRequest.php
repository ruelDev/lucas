<?php

namespace App\Http\Requests\OutCollection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepositRequest extends FormRequest
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
        $deposit = $this->route('deposit')?->id ?? $this->route('deposit');

        return [
            'id'                    => ['nullable'],
            'bank_id'               => ['required'],
            'depositSlip'           => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'depositDate'           => ['required', 'date_format:Y-m-d'],
            'depositAmount'         => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'depositCharge'         => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'referenceNumber'       => ['required', 'string', Rule::unique('deposits', 'referenceNumber')->ignore($deposit)],
            'depositoryRemarks'     => ['required', 'string'],
        ];
    }
}
