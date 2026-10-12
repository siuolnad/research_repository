<?php

declare(strict_types=1);

namespace App\Http\Requests\Proposal;

use App\DTOs\ProposalData;
use App\Enums\ResearchArea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'proponents' => $this->toList($this->input('proponents'), ','),
            'objectives' => $this->toList($this->input('objectives'), "\n"),
            'keywords' => $this->toList($this->input('keywords'), ','),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string', 'min:50'],

            'proponents' => ['required', 'array', 'min:1'],
            'proponents.*' => ['required', 'string', 'max:255'],

            'objectives' => ['required', 'array', 'min:1'],
            'objectives.*' => ['required', 'string', 'max:1000'],

            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string', 'max:100'],

            'research_area' => ['required', Rule::enum(ResearchArea::class)],

            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'budget' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'abstract.min' => 'An abstract must be at least 50 characters to be meaningful.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',        ];
    }

    public function toDto(): ProposalData
    {
        return ProposalData::fromValidated($this->validated());
    }

    /**
     * @return array<int, string>|mixed
     */
    private function toList(mixed $value, string $delimiter): mixed
    {
        if (! is_string($value)) {
            return $value ?? [];
        }

        return array_values(array_filter(
            array_map('trim', explode($delimiter, str_replace("\r\n", "\n", $value))),
            fn (string $item): bool => $item !== '',
        ));
    }
}
