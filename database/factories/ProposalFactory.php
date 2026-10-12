<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProposalStatus;
use App\Enums\ResearchArea;
use App\Models\Proposal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proposal>
 */
final class ProposalFactory extends Factory
{
    protected $model = Proposal::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-3 years', '+1 year');

        return [
            'title' => fake()->sentence(6),
            'abstract' => fake()->paragraphs(3, asText: true),
            'objectives' => fake()->sentences(3),
            'keywords' => fake()->words(3),
            'research_area' => fake()->randomElement(ResearchArea::cases()),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => (clone $start)->modify('+'.fake()->numberBetween(3, 24).' months')->format('Y-m-d'),
            'budget' => fake()->randomFloat(2, 10000, 5000000),
            'status' => ProposalStatus::New,
        ];
    }

    public function approved(): self
    {
        return $this->state(fn (): array => ['status' => ProposalStatus::Approved]);
    }

    public function unreviewed(): self
    {
        return $this->state(fn (): array => ['status' => ProposalStatus::New]);
    }
}
