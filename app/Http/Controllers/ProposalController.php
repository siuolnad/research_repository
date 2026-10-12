<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProposalStatus;
use App\Http\Requests\Proposal\DisapproveProposalRequest;
use App\Http\Requests\Proposal\ListProposalRequest;
use App\Http\Requests\Proposal\ProposalRequest;
use App\Http\Requests\Proposal\UploadProposalFilesRequest;
use App\Services\Proposal\ProposalService;
use Illuminate\Http\RedirectResponse;
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

        return view('proposals.index', [
            'proposals' => $this->service->list($filters),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('proposals.create');
    }

    public function store(ProposalRequest $request): RedirectResponse
    {
        $proposal = $this->service->create($request->toDto());

        return redirect()->route('proposals.show', $proposal->id)
            ->with('status', 'Proposal added.');
    }

    public function show(int $proposal): View
    {
        return view('proposals.show', ['proposal' => $this->service->get($proposal)]);
    }

    public function edit(int $proposal): View
    {
        return view('proposals.edit', ['proposal' => $this->service->getEditable($proposal)]);
    }

    public function update(ProposalRequest $request, int $proposal): RedirectResponse
    {
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
        $this->service->addFiles($proposal, $request->file('files'));

        return back()->with('status', 'Files uploaded.');
    }

    public function downloadFile(int $proposal, int $file): StreamedResponse
    {
        $record = $this->service->findFile($proposal, $file);

        return Storage::download($record->path, $record->name);
    }

    public function destroyFile(int $proposal, int $file): RedirectResponse
    {
        $this->service->deleteFile($proposal, $file);

        return back()->with('status', 'File removed.');
    }

    public function destroy(int $proposal): RedirectResponse
    {
        $this->service->delete($proposal);

        return redirect()->route('proposals.index')
            ->with('status', 'Proposal deleted.');
    }
}
