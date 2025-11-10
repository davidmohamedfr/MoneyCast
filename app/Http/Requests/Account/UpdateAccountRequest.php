<?php

namespace App\Http\Requests\Account;

use App\Domain\Account\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = auth()->id();
        $accountId = $this->route('account')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('accounts', 'name')
                    ->where('user_id', $userId)
                    ->where('bank', $this->bank)
                    ->ignore($accountId),
            ],
            'type' => ['required', Rule::enum(AccountType::class)],
            'bank' => ['required', 'string', 'max:255'],
        ];
    }
}
