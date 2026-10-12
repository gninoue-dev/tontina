<?php

namespace App\Http\Requests;

use App\Frequency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTontineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'contribution_amount' => ['required', 'integer', 'min:500', 'max:10000000'],
            'frequency' => ['required', Rule::enum(Frequency::class)],
            'starts_on' => ['required', 'date', 'after_or_equal:today'],
            'late_penalty' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'contribution_amount' => 'montant de la cotisation',
            'frequency' => 'fréquence',
            'starts_on' => 'date du premier tour',
            'late_penalty' => 'pénalité de retard',
        ];
    }
}
