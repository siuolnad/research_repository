<?php

declare(strict_types=1);

namespace App\Http\Requests\Proposal;

use App\Enums\ProposalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(ProposalStatus::class)],
            'year' => ['nullable', 'integer', 'between:1900,2100'],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}
