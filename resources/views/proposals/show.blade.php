@extends('layouts.app')

@section('title', $proposal->title . ' — Research Repository')

@section('content')
    <div class="content-head">
        <button type="button" class="menu-btn" id="menu-btn" aria-expanded="false" aria-controls="sidebar">Menu</button>
        <a class="back-link" href="{{ route('proposals.index') }}">&larr; All proposals</a>
        @include('proposals._actions')
    </div>

    <article class="panel detail">
        <div class="case-kicker">
            <span class="status-pill {{ $proposal->status->value }}">{{ $proposal->status->label() }}</span>
            <span class="division-chip">{{ $proposal->research_area->label() }}</span>
        </div>

        <h1>{{ $proposal->title }}</h1>
        <div class="byline">{{ implode(', ', $proposal->proponents) }}</div>

        @if ($proposal->status === \App\Enums\ProposalStatus::Disapproved && $proposal->remarks)
            <div class="remarks">
                <div class="remarks-title">Reason for disapproval</div>
                <p>{{ $proposal->remarks }}</p>
            </div>
        @endif

        <p class="abstract-full">{{ $proposal->abstract }}</p>

        <dl>
            <dt>Objectives</dt>
            <dd>
                <ol>
                    @foreach ($proposal->objectives as $objective)
                        <li>{{ $objective }}</li>
                    @endforeach
                </ol>
            </dd>

            <dt>Start date</dt>
            <dd>{{ $proposal->start_date->format('F j, Y') }}</dd>

            <dt>End date</dt>
            <dd>{{ $proposal->end_date->format('F j, Y') }}</dd>

            <dt>Budget</dt>
            <dd>₱{{ number_format((float) $proposal->budget, 2) }}</dd>

            @if ($proposal->keywords)
                <dt>Keywords</dt>
                <dd class="keywords">
                    @foreach ($proposal->keywords as $keyword)
                        <span class="kw">{{ $keyword }}</span>
                    @endforeach
                </dd>
            @endif
        </dl>
    </article>

    <section class="panel files-card" aria-labelledby="files-title">
        <h2 id="files-title">Supporting files</h2>

        <div class="files-list">
            @forelse ($proposal->files as $file)
                <div class="file-row">
                    <a href="{{ route('proposals.files.download', [$proposal->id, $file->id]) }}">{{ $file->name }}</a>
                    <span class="file-size">{{ \Illuminate\Support\Number::fileSize($file->size) }}</span>
                    <form method="POST" action="{{ route('proposals.files.destroy', [$proposal->id, $file->id]) }}" onsubmit="return confirm('Remove this file?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Remove</button>
                    </form>
                </div>
            @empty
                <p class="files-empty">No files yet. Upload a PDF, Word or Excel file below.</p>
            @endforelse
        </div>

        <form class="files-upload {{ $errors->has('files') || $errors->has('files.*') ? 'has-err' : '' }}" method="POST" action="{{ route('proposals.files.store', $proposal->id) }}" enctype="multipart/form-data">
            @csrf
            <div class="field">
                <label for="in-files">Upload files <span class="hint">PDF, DOC, DOCX or XLSX. Up to 10 files, 10 MB each.</span></label>
                <input id="in-files" name="files[]" type="file" multiple required accept=".pdf,.doc,.docx,.xlsx">
                @error('files')<div class="err">{{ $message }}</div>@enderror
                @foreach ($errors->get('files.*') as $messages)
                    @foreach ($messages as $message)<div class="err">{{ $message }}</div>@endforeach
                @endforeach
            </div>
            <button type="submit" class="btn-primary">Upload</button>
        </form>
    </section>
@endsection
