<?php

namespace App\Http\Requests\Import;

use App\Domain\Import\Enums\ImportSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Domain\Import\Models\Import::class);
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'source_type' => ['required', Rule::enum(ImportSource::class)],
            'account_id' => [
                'required',
                'integer',
                'exists:accounts,id',
                function ($attribute, $value, $fail) {
                    $account = \App\Domain\Account\Models\Account::find($value);
                    if ($account && $account->user_id !== $this->user()->id) {
                        $fail('The selected account does not belong to you.');
                    }
                },
            ],
        ];
    }
}
