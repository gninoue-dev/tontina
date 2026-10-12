<?php

namespace App\Http\Requests;

use App\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', $this->route('tontine'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'member_id' => [
                'required', 'integer',
                Rule::exists('members', 'id')->where('tontine_id', $this->route('tontine')->id),
            ],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:60'],
            'paid_on' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'member_id' => 'membre',
            'method' => 'moyen de paiement',
            'reference' => 'référence de la transaction',
            'paid_on' => 'date du paiement',
        ];
    }
}
