<?php

namespace App\Http\Requests\Account;

use App\Domain\Account\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('accounts', 'name')
                    ->where('user_id', auth()->id())
                    ->where('bank', $this->bank),
            ],
            'type' => ['required', Rule::enum(AccountType::class)],
            'bank' => ['required', 'string', 'max:255'],
            'initial_balance' => ['required', 'numeric'],
            'currency' => ['required', 'string', 'size:3'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'currency' => $this->currency ?? 'EUR',
        ]);
    }
}
