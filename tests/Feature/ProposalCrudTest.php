<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProposalStatus;
use App\Enums\ResearchArea;
use App\Enums\UserRole;
use App\Models\Proposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProposalCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withSession(['role' => 'reviewer']);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Effects of sleep on memory',
            'abstract' => str_repeat('Sleep consolidates memory. ', 5),
            'proponents' => 'Ada Lovelace, Alan Turing',
            'objectives' => "Measure recall\nCompare sleep groups",
            'keywords' => 'sleep, memory',
            'research_area' => 'health',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'budget' => '250000.50',
        ], $overrides);
    }

    public function test_root_redirects_to_index(): void
    {
        $this->get('/')->assertRedirect('/proposals');
    }

    public function test_index_lists_and_filters_proposals(): void
    {
        Proposal::factory()->approved()->create(['title' => 'Visible Approved']);
        Proposal::factory()->unreviewed()->create(['title' => 'Hidden New']);

        $this->get('/proposals?status=approved')
            ->assertOk()
            ->assertSee('Visible Approved')
            ->assertDontSee('Hidden New');
    }

    public function test_index_searches_title(): void
    {
        Proposal::factory()->create(['title' => 'Quantum entanglement']);
        Proposal::factory()->create(['title' => 'Marine biology']);

        $this->get('/proposals?search=quantum')
            ->assertOk()
            ->assertSee('Quantum entanglement')
            ->assertDontSee('Marine biology');
    }

    public function test_store_creates_new_proposal_with_split_lists(): void
    {
        $response = $this->post('/proposals', $this->payload());

        $proposal = Proposal::firstOrFail();
        $response->assertRedirect(route('proposals.show', $proposal->id));
        $this->assertSame(['Ada Lovelace', 'Alan Turing'], $proposal->proponents);
        $this->assertSame(['Measure recall', 'Compare sleep groups'], $proposal->objectives);
        $this->assertSame(['sleep', 'memory'], $proposal->keywords);
        $this->assertSame(ResearchArea::Health, $proposal->research_area);
        $this->assertSame('250000.50', $proposal->budget);
        $this->assertSame(ProposalStatus::New, $proposal->status);
    }

    public function test_store_validates_input(): void
    {
        $this->post('/proposals', ['title' => '', 'abstract' => 'short'])
            ->assertSessionHasErrors([
                'title', 'abstract', 'proponents', 'objectives',
                'research_area', 'start_date', 'end_date', 'budget',
            ]);

        $this->assertDatabaseCount('proposals', 0);
    }

    public function test_store_rejects_end_date_before_start_date(): void
    {
        $this->post('/proposals', $this->payload(['end_date' => '2025-12-31']))
            ->assertSessionHasErrors('end_date');
    }

    public function test_store_rejects_negative_budget_and_unknown_area(): void
    {
        $this->post('/proposals', $this->payload(['budget' => '-1', 'research_area' => 'astrology']))
            ->assertSessionHasErrors(['budget', 'research_area']);
    }

    public function test_show_and_edit_render(): void
    {
        $proposal = Proposal::factory()->create();

        $this->get("/proposals/{$proposal->id}")->assertOk()->assertSee($proposal->title);
        $this->get("/proposals/{$proposal->id}/edit")->assertOk();
        $this->get('/proposals/create')->assertOk();
    }

    public function test_show_missing_proposal_is_404(): void
    {
        $this->get('/proposals/999')->assertNotFound();
    }

    public function test_update_changes_proposal(): void
    {
        $proposal = Proposal::factory()->create();

        $this->put("/proposals/{$proposal->id}", $this->payload(['title' => 'Renamed']))
            ->assertRedirect(route('proposals.show', $proposal->id));

        $this->assertSame('Renamed', $proposal->fresh()->title);
    }

    public function test_new_proposal_can_be_approved(): void
    {
        $proposal = Proposal::factory()->unreviewed()->create();

        $this->from("/proposals")->post("/proposals/{$proposal->id}/approve")
            ->assertRedirect("/proposals");

        $this->assertSame(ProposalStatus::Approved, $proposal->fresh()->status);
    }

    public function test_new_proposal_can_be_disapproved(): void
    {
        $proposal = Proposal::factory()->unreviewed()->create();

        $this->from("/proposals")->post("/proposals/{$proposal->id}/disapprove", ["remarks" => "Budget not justified."])
            ->assertRedirect("/proposals");

        $this->assertSame(ProposalStatus::Disapproved, $proposal->fresh()->status);
    }

    public function test_decided_proposal_cannot_be_decided_again(): void
    {
        $proposal = Proposal::factory()->approved()->create();

        $this->post("/proposals/{$proposal->id}/disapprove", ["remarks" => "Late."])->assertRedirect();

        $this->assertSame(ProposalStatus::Approved, $proposal->fresh()->status);
    }

    public function test_decision_buttons_only_show_for_new_proposals(): void
    {
        $new = Proposal::factory()->unreviewed()->create();
        $approved = Proposal::factory()->approved()->create();

        $this->get("/proposals/{$new->id}")->assertSee("/proposals/{$new->id}/disapprove");
        $this->get("/proposals/{$approved->id}")->assertDontSee("/proposals/{$approved->id}/disapprove");
    }

    public function test_files_can_be_uploaded_downloaded_and_removed(): void
    {
        Storage::fake();
        $proposal = Proposal::factory()->create();

        $this->post("/proposals/{$proposal->id}/files", [
            'files' => [
                UploadedFile::fake()->create('budget.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('plan.docx', 50),
                UploadedFile::fake()->create('costs.xlsx', 50),
            ],
        ])->assertSessionHasNoErrors();

        $this->assertCount(3, $proposal->files);
        $file = $proposal->files()->where('name', 'budget.pdf')->firstOrFail();
        Storage::assertExists($file->path);

        $this->get("/proposals/{$proposal->id}/files/{$file->id}")->assertOk()->assertDownload('budget.pdf');

        $this->delete("/proposals/{$proposal->id}/files/{$file->id}")->assertRedirect();

        $this->assertDatabaseCount('proposal_files', 2);
        Storage::assertMissing($file->path);
    }

    public function test_files_can_be_uploaded_and_removed_after_a_decision(): void
    {
        Storage::fake();
        $proposal = Proposal::factory()->approved()->create();

        $this->post("/proposals/{$proposal->id}/files", [
            'files' => [UploadedFile::fake()->create('late.pdf', 10, 'application/pdf')],
        ])->assertSessionHasNoErrors();

        $file = $proposal->files()->firstOrFail();
        $this->delete("/proposals/{$proposal->id}/files/{$file->id}")->assertRedirect();

        $this->assertDatabaseCount('proposal_files', 0);
    }

    public function test_upload_rejects_other_types_and_oversized_files(): void
    {
        Storage::fake();
        $proposal = Proposal::factory()->create();

        $this->post("/proposals/{$proposal->id}/files", [
            'files' => [
                UploadedFile::fake()->create('slides.pptx', 10),
                UploadedFile::fake()->create('old.xls', 10),
                UploadedFile::fake()->create('huge.pdf', 10241, 'application/pdf'),
            ],
        ])->assertSessionHasErrors(['files.0', 'files.1', 'files.2']);

        $this->assertDatabaseCount('proposal_files', 0);
    }

    public function test_upload_requires_a_file(): void
    {
        $proposal = Proposal::factory()->create();

        $this->post("/proposals/{$proposal->id}/files", [])->assertSessionHasErrors('files');
    }

    /**
     * @return array<int, string>
     */
    private function events(Proposal $proposal): array
    {
        return $proposal->history()->get()->map(fn ($h) => $h->event->value)->all();
    }

    public function test_history_records_the_whole_lifecycle(): void
    {
        Storage::fake();

        $this->post('/proposals', $this->payload());
        $proposal = Proposal::firstOrFail();

        $this->put("/proposals/{$proposal->id}", $this->payload(['title' => 'Renamed', 'budget' => '1000']));
        $this->post("/proposals/{$proposal->id}/files", [
            'files' => [UploadedFile::fake()->create('plan.pdf', 10, 'application/pdf')],
        ]);
        $this->delete("/proposals/{$proposal->id}/files/{$proposal->files()->firstOrFail()->id}");
        $this->post("/proposals/{$proposal->id}/disapprove", ['remarks' => 'Too costly.']);

        $this->assertSame(
            ['created', 'updated', 'file_added', 'file_removed', 'disapproved'],
            $this->events($proposal),
        );

        $history = $proposal->history()->get();
        $this->assertSame('Changed title, budget.', $history[1]->description);
        $this->assertSame('plan.pdf', $history[2]->description);
        $this->assertSame('Too costly.', $history[4]->description);
    }

    public function test_edit_without_changes_is_still_logged(): void
    {
        $this->post('/proposals', $this->payload());
        $proposal = Proposal::firstOrFail();

        $this->put("/proposals/{$proposal->id}", $this->payload());

        $this->assertSame(['created', 'updated'], $this->events($proposal));
        $this->assertSame('Saved with no changes.', $proposal->history()->get()[1]->description);
    }

    public function test_approval_is_logged(): void
    {
        $proposal = Proposal::factory()->unreviewed()->create();

        $this->post("/proposals/{$proposal->id}/approve");

        $this->assertSame(['approved'], $this->events($proposal));
    }

    public function test_show_renders_timeline_with_pending_step_only_while_new(): void
    {
        $new = Proposal::factory()->unreviewed()->create();
        $approved = Proposal::factory()->unreviewed()->create();
        $this->post("/proposals/{$approved->id}/approve");

        $this->get("/proposals/{$new->id}")->assertSee('Awaiting decision');
        $this->get("/proposals/{$approved->id}")
            ->assertSee('Proposal approved')
            ->assertDontSee('Awaiting decision');
    }

    public function test_file_of_another_proposal_is_not_reachable(): void
    {
        Storage::fake();
        $owner = Proposal::factory()->create();
        $other = Proposal::factory()->create();
        $file = $owner->files()->create(['name' => 'a.pdf', 'path' => 'proposals/1/a.pdf', 'mime_type' => 'application/pdf', 'size' => 1]);

        $this->get("/proposals/{$other->id}/files/{$file->id}")->assertNotFound();
    }

    public function test_disapproval_saves_remarks_and_shows_them(): void
    {
        $proposal = Proposal::factory()->unreviewed()->create();

        $this->post("/proposals/{$proposal->id}/disapprove", ['remarks' => 'Budget not justified.']);

        $this->assertSame('Budget not justified.', $proposal->fresh()->remarks);
        $this->get("/proposals/{$proposal->id}")->assertSee('Budget not justified.');
    }

    public function test_disapproval_requires_remarks(): void
    {
        $proposal = Proposal::factory()->unreviewed()->create();

        $this->post("/proposals/{$proposal->id}/disapprove", ['remarks' => ''])
            ->assertSessionHasErrors('remarks');

        $this->assertSame(ProposalStatus::New, $proposal->fresh()->status);
    }

    public function test_decided_proposal_cannot_be_edited(): void
    {
        $proposal = Proposal::factory()->approved()->create(['title' => 'Original']);

        $this->get("/proposals/{$proposal->id}/edit")->assertRedirect();
        $this->put("/proposals/{$proposal->id}", $this->payload(['title' => 'Changed']))->assertRedirect();

        $this->assertSame('Original', $proposal->fresh()->title);
        $this->get("/proposals/{$proposal->id}")->assertDontSee("/proposals/{$proposal->id}/edit");
    }

    public function test_role_defaults_to_proponent_and_can_be_switched(): void
    {
        $this->flushSession();

        $this->get('/proposals')->assertSee('Viewing as');
        $this->assertSame(UserRole::Proponent, UserRole::current());

        $this->post('/role', ['role' => 'reviewer'])->assertSessionHas('role', 'reviewer');
        $this->post('/role', ['role' => 'admin'])->assertSessionHasErrors('role');
    }

    public function test_proponent_cannot_approve_or_disapprove(): void
    {
        $proposal = Proposal::factory()->unreviewed()->create();
        $this->withSession(['role' => 'proponent']);

        $this->post("/proposals/{$proposal->id}/approve")->assertForbidden();
        $this->post("/proposals/{$proposal->id}/disapprove", ['remarks' => 'No.'])->assertForbidden();

        $this->assertSame(ProposalStatus::New, $proposal->fresh()->status);
        $this->assertDatabaseCount('proposal_histories', 0);
    }

    public function test_proponent_does_not_see_decision_actions_but_can_still_edit(): void
    {
        $proposal = Proposal::factory()->unreviewed()->create();
        $this->withSession(['role' => 'proponent']);

        $this->get("/proposals/{$proposal->id}")
            ->assertDontSee("/proposals/{$proposal->id}/approve")
            ->assertDontSee("/proposals/{$proposal->id}/disapprove")
            ->assertSee("/proposals/{$proposal->id}/edit")
            ->assertSee('A reviewer will approve or disapprove this proposal.');

        $this->put("/proposals/{$proposal->id}", $this->payload(['title' => 'By proponent']))
            ->assertRedirect(route('proposals.show', $proposal->id));
        $this->assertSame('By proponent', $proposal->fresh()->title);
    }

    public function test_destroy_soft_deletes(): void
    {
        $proposal = Proposal::factory()->create();

        $this->delete("/proposals/{$proposal->id}")->assertRedirect(route('proposals.index'));

        $this->assertSoftDeleted($proposal);
    }
}
