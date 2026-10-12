<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProposalStatus;
use App\Enums\UserRole;
use App\Http\Requests\Proposal\DisapproveProposalRequest;
use App\Http\Requests\Proposal\ListProposalRequest;
use App\Http\Requests\Proposal\ProposalRequest;
use App\Http\Requests\Proposal\UploadProposalFilesRequest;
use App\Models\Proposal;
use App\Services\Proposal\ProposalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProposalController extends Controller
{
    public function __construct(
        private readonly ProposalService $service,
    ) {}

    public function index(ListProposalRequest $request): View
    {
        $filters = $request->validated();

        $scoped = $request->user()->role === UserRole::Proponent
            ? $filters + ['user_id' => $request->user()->id]
            : $filters;

        return view('proposals.index', [
            'proposals' => $this->service->list($scoped),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('proposals.create');
    }

    public function store(ProposalRequest $request): RedirectResponse
    {
        $proposal = $this->service->create($request->toDto(), $request->user()->id);

        return redirect()->route('proposals.show', $proposal->id)
            ->with('status', 'Proposal added.');
    }

    public function show(int $proposal): View
    {
        return view('proposals.show', ['proposal' => $this->authorized($proposal)]);
    }

    public function edit(int $proposal): View
    {
        $this->authorized($proposal);

        return view('proposals.edit', ['proposal' => $this->service->getEditable($proposal)]);
    }

    public function update(ProposalRequest $request, int $proposal): RedirectResponse
    {
        $this->authorized($proposal);

        $updated = $this->service->update($proposal, $request->toDto());

        return redirect()->route('proposals.show', $updated->id)
            ->with('status', 'Proposal updated.');
    }

    public function approve(int $proposal): RedirectResponse
    {
        $this->service->decide($proposal, ProposalStatus::Approved);

        return back()->with('status', 'Proposal approved.');
    }

    public function disapprove(DisapproveProposalRequest $request, int $proposal): RedirectResponse
    {
        $this->service->decide($proposal, ProposalStatus::Disapproved, $request->validated('remarks'));

        return back()->with('status', 'Proposal disapproved.');
    }

    public function uploadFiles(UploadProposalFilesRequest $request, int $proposal): RedirectResponse
    {
        $this->authorized($proposal);

        $this->service->addFiles($proposal, $request->file('files'));

        return back()->with('status', 'Files uploaded.');
    }

    public function downloadFile(int $proposal, int $file): StreamedResponse
    {
        $this->authorized($proposal);

        $record = $this->service->findFile($proposal, $file);

        return Storage::download($record->path, $record->name);
    }

    public function destroyFile(int $proposal, int $file): RedirectResponse
    {
        $this->authorized($proposal);

        $this->service->deleteFile($proposal, $file);

        return back()->with('status', 'File removed.');
    }

    public function destroy(int $proposal): RedirectResponse
    {
        $this->authorized($proposal);

        $this->service->delete($proposal);

        return redirect()->route('proposals.index')
            ->with('status', 'Proposal deleted.');
    }

    /**
     * Load a proposal the signed-in user is allowed to work with.
     */
    private function authorized(int $id): Proposal
    {
        $proposal = $this->service->get($id);

        Gate::authorize('manage', $proposal);

        return $proposal;
    }
}
