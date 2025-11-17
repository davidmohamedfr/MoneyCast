<?php

namespace App\Http\Requests\Import;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $import = $this->route('import');
        return $this->user()->can('update', $import);
    }

    public function rules(): array
    {
        return [
            'mapping' => ['required', 'array'],
            'mapping.date' => ['required', 'string'],
            'mapping.amount' => ['required_without:mapping.debit', 'string'],
            'mapping.debit' => ['required_without:mapping.amount', 'string'],
            'mapping.credit' => ['nullable', 'string'],
            'mapping.payee' => ['required', 'string'],
            'mapping.description' => ['nullable', 'string'],
        ];
    }
}
