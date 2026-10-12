<?php

declare(strict_types=1);

namespace App\Http\Requests\Proposal;

use Illuminate\Foundation\Http\FormRequest;

class DisapproveProposalRequest extends FormRequest
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
            'remarks' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'remarks.required' => 'Remarks are required to disapprove a proposal.',
        ];
    }
}
