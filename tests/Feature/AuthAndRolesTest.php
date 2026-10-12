<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AuthAndRolesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Effects of sleep on memory',
            'abstract' => str_repeat('Sleep consolidates memory. ', 5),
            'proponents' => 'Ada Lovelace',
            'objectives' => 'Measure recall',
            'research_area' => 'health',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'budget' => '1000',
        ], $overrides);
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/proposals')->assertRedirect('/login');
        $this->get('/')->assertRedirect('/login');
        $this->post('/proposals', $this->payload())->assertRedirect('/login');
    }

    public function test_user_can_sign_in_and_out(): void
    {
        $user = User::factory()->proponent()->create(['email' => 'p@example.com']);

        $this->get('/login')->assertOk()->assertSee('Sign in');

        $this->post('/login', ['email' => 'p@example.com', 'password' => 'password'])
            ->assertRedirect('/proposals');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'p@example.com']);

        $this->post('/login', ['email' => 'p@example.com', 'password' => 'nope'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_signed_in_user_is_shown_with_their_role(): void
    {
        $this->actingAs(User::factory()->reviewer()->create(['name' => 'Rita Reviewer']))
            ->get('/proposals')
            ->assertSee('Rita Reviewer')
            ->assertSee('Reviewer');
    }

    public function test_new_proposal_belongs_to_its_creator(): void
    {
        $proponent = User::factory()->proponent()->create();

        $this->actingAs($proponent)->post('/proposals', $this->payload())->assertSessionHasNoErrors();

        $this->assertSame($proponent->id, Proposal::firstOrFail()->user_id);
    }

    public function test_proponent_only_sees_their_own_proposals(): void
    {
        $me = User::factory()->proponent()->create();
        $other = User::factory()->proponent()->create();
        Proposal::factory()->create(['user_id' => $me->id, 'title' => 'My proposal']);
        $theirs = Proposal::factory()->create(['user_id' => $other->id, 'title' => 'Their proposal']);

        $this->actingAs($me)->get('/proposals')
            ->assertSee('My proposal')
            ->assertDontSee('Their proposal');

        $this->get("/proposals/{$theirs->id}")->assertNotFound();
        $this->get("/proposals/{$theirs->id}/edit")->assertNotFound();
        $this->put("/proposals/{$theirs->id}", $this->payload())->assertNotFound();
        $this->delete("/proposals/{$theirs->id}")->assertNotFound();
        $this->post("/proposals/{$theirs->id}/files", [
            'files' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')],
        ])->assertNotFound();
    }

    public function test_reviewer_sees_every_proposal(): void
    {
        $proponent = User::factory()->proponent()->create();
        Proposal::factory()->create(['user_id' => $proponent->id, 'title' => 'From a proponent']);

        $this->actingAs(User::factory()->reviewer()->create())
            ->get('/proposals')
            ->assertSee('From a proponent');
    }

    public function test_proponent_cannot_approve_or_disapprove_even_their_own(): void
    {
        $proponent = User::factory()->proponent()->create();
        $proposal = Proposal::factory()->unreviewed()->create(['user_id' => $proponent->id]);

        $this->actingAs($proponent);

        $this->post("/proposals/{$proposal->id}/approve")->assertForbidden();
        $this->post("/proposals/{$proposal->id}/disapprove", ['remarks' => 'No.'])->assertForbidden();

        $this->assertSame(ProposalStatus::New, $proposal->fresh()->status);
        $this->assertDatabaseCount('proposal_histories', 0);
    }

    public function test_proponent_does_not_see_decision_actions_but_can_still_edit(): void
    {
        $proponent = User::factory()->proponent()->create();
        $proposal = Proposal::factory()->unreviewed()->create(['user_id' => $proponent->id]);

        $this->actingAs($proponent)->get("/proposals/{$proposal->id}")
            ->assertDontSee("/proposals/{$proposal->id}/approve")
            ->assertDontSee("/proposals/{$proposal->id}/disapprove")
            ->assertSee("/proposals/{$proposal->id}/edit")
            ->assertSee('A reviewer will approve or disapprove this proposal.');

        $this->put("/proposals/{$proposal->id}", $this->payload(['title' => 'By proponent']))
            ->assertRedirect(route('proposals.show', $proposal->id));
        $this->assertSame('By proponent', $proposal->fresh()->title);
    }
}
