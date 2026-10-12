<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', $this->route('tontine'));
    }

    /**
     * Les numéros ivoiriens ont 10 chiffres depuis 2021 : on retire espaces, points et indicatif +225.
     */
    protected function prepareForValidation(): void
    {
        $phone = preg_replace('/[^\d+]/', '', (string) $this->input('phone'));
        $phone = preg_replace('/^(\+|00)?225(?=\d{10}$)/', '', $phone);
        $this->merge(['phone' => $phone]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => [
                'required', 'regex:/^0\d{9}$/',
                Rule::unique('members', 'phone')->where('tontine_id', $this->route('tontine')->id),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Le numéro doit avoir 10 chiffres, par exemple 07 07 12 34 56.',
            'phone.unique' => 'Ce numéro est déjà inscrit dans cette tontine.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'nom', 'phone' => 'téléphone'];
    }
}
