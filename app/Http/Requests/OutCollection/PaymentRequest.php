<?php

namespace App\Http\Requests\OutCollection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class PaymentRequest extends FormRequest
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
        $payment = $this->route('payment')?->id ?? $this->route('payment');

        return [
            'id'                    => [''],
            'deposit_id'            => ['required', 'exists:deposits,id'],
            'makerId'               => ['required'],
            'makerName'             => ['required'],
            'agreementNumber'       => ['required'],
            'referenceNumber'       => ['required'],
            'misNumber'             => ['required'],
            'customerName'          => ['required'],
            'aoc'                   => ['required'],
            'arNumber'              => [
                'required',
                Rule::unique('payments', 'arNumber')
                    ->where(function ($query) {
                        $query->where('deposit_id', $this->input('deposit_id'))
                            ->whereNull('deleted_at');
                    })
                    ->ignore($payment),
            ],
            'arAmount'              => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'arDate'                => ['required', 'date'],
            'paymentType'           => ['required'],
            'npaStage'              => ['nullable'],
            'reason'                => ['required'],
            'status'                => ['required'],
            'source'                => ['required'],
            'company'               => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'agreementNumber.required'  => 'Agreement Number is required.',
            'referenceNumber.required'  => 'Reference Number is required.',
            'misNumber.required'        => 'MIS Number is required.',
            'customerName.required'     => 'Customer Name is required.',
            'aoc.required'              => 'AOC is required.',
            'source.required'           => 'Source is required.',

            'arDate.required'           => 'AR Date is required.',
            'arDate.unique'             => 'AR Date already exists.',
            'arNumber.required'         => 'AR Number is required.',
            'arNumber.unique'           => 'AR Number already exists.',
            'arAmount.required'         => 'AR Amount is required.',
            'arAmount.unique'           => 'AR Amount already exists.',
            'reason.required'           => 'Reason is required.',
            'reason.unique'             => 'Reason already exists.',
        ];
    }
}
