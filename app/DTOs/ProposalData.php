<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ResearchArea;

final readonly class ProposalData
{
    /**
     * @param  array<int, string>  $objectives
     * @param  array<int, string>|null  $keywords
     */
    public function __construct(
        public string $title,
        public string $abstract,
        public string $proponent,
        public array $objectives,
        public ResearchArea $researchArea,
        public string $startDate,
        public string $endDate,
        public string $budget,
        public ?array $keywords = null,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            title: $validated['title'],
            abstract: $validated['abstract'],
            proponent: $validated['proponent'],
            objectives: $validated['objectives'],
            researchArea: ResearchArea::from($validated['research_area']),
            startDate: $validated['start_date'],
            endDate: $validated['end_date'],
            budget: (string) $validated['budget'],
            keywords: $validated['keywords'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(): array
    {
        return [
            'title' => $this->title,
            'abstract' => $this->abstract,
            'proponent' => $this->proponent,
            'objectives' => $this->objectives,
            'keywords' => $this->keywords,
            'research_area' => $this->researchArea,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'budget' => $this->budget,
        ];
    }
}
