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
        </div>

        <h1>{{ $proposal->title }}</h1>
        <div class="byline">{{ $proposal->proponent }}</div>

        @if ($proposal->status === \App\Enums\ProposalStatus::Disapproved && $proposal->remarks)
            <div class="remarks">
                <div class="remarks-title">Reason for disapproval</div>
                <p>{{ $proposal->remarks }}</p>
            </div>
        @endif

        <h2 class="detail-heading">Abstract</h2>
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

            <dt>Research area</dt>
            <dd>{{ $proposal->research_area->label() }}</dd>

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

    <div class="detail-cards">
    <section class="panel files-card" aria-labelledby="files-title">
        <header class="panel-head">
            <h2 id="files-title">Supporting files</h2>
            <span class="panel-count">{{ $proposal->files->count() }}</span>
        </header>

        <div class="files-list">
            @forelse ($proposal->files as $file)
                @php($ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION)))
                <div class="file-row">
                    <span class="file-type {{ $ext }}">{{ $ext }}</span>
                    <div class="file-info">
                        <a href="{{ route('proposals.files.download', [$proposal->id, $file->id]) }}" title="{{ $file->name }}">{{ $file->name }}</a>
                        <span class="file-size">{{ \Illuminate\Support\Number::fileSize($file->size) }}</span>
                    </div>
                    <form method="POST" action="{{ route('proposals.files.destroy', [$proposal->id, $file->id]) }}" onsubmit="return confirm('Remove this file?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="icon-btn" title="Remove file" aria-label="Remove {{ $file->name }}">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M2.5 4.5h11M6.5 4.5V3a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1.5M4 4.5l.6 8a1 1 0 0 0 1 .9h4.8a1 1 0 0 0 1-.9l.6-8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </form>
                </div>
            @empty
                <div class="files-empty">
                    <svg width="28" height="28" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M9.5 1.5H4.5a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1v-8l-3-4Z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/>
                        <path d="M9.5 1.5v4h3" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/>
                    </svg>
                    <span>No supporting files yet</span>
                </div>
            @endforelse
        </div>

        <form class="files-upload {{ $errors->has('files') || $errors->has('files.*') ? 'has-err' : '' }}" method="POST" action="{{ route('proposals.files.store', $proposal->id) }}" enctype="multipart/form-data">
            @csrf
            <label class="dropzone" for="in-files" id="dropzone">
                <svg width="26" height="26" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M8 10.5V2.5M5 5.2 8 2.2l3 3M2.5 10v2.5a1 1 0 0 0 1 1h9a1 1 0 0 0 1-1V10" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="dropzone-title">Drop files here or <span class="dropzone-link">browse</span></span>
                <span class="dropzone-hint">PDF, DOC, DOCX or XLSX. Up to 10 files, 10 MB each.</span>
                <input id="in-files" name="files[]" type="file" multiple required accept=".pdf,.doc,.docx,.xlsx">
            </label>

            @error('files')<div class="err">{{ $message }}</div>@enderror
            @foreach ($errors->get('files.*') as $messages)
                @foreach ($messages as $message)<div class="err">{{ $message }}</div>@endforeach
            @endforeach

            <ul class="picked" id="picked" aria-live="polite"></ul>

            <button type="submit" class="btn-primary" id="upload-btn" disabled>Upload</button>
        </form>
        <script>
        (function () {
            var input = document.getElementById('in-files');
            var zone = document.getElementById('dropzone');
            var list = document.getElementById('picked');
            var btn = document.getElementById('upload-btn');
            var allowed = ['pdf', 'doc', 'docx', 'xlsx'];
            var maxBytes = 10 * 1024 * 1024;

            function size(bytes) {
                return bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
            }

            function render() {
                list.innerHTML = '';
                var files = Array.prototype.slice.call(input.files);
                var ok = files.length > 0 && files.length <= 10;

                files.forEach(function (f) {
                    var ext = f.name.split('.').pop().toLowerCase();
                    var problem = allowed.indexOf(ext) === -1 ? 'Not an allowed type' : (f.size > maxBytes ? 'Over 10 MB' : '');
                    if (problem) ok = false;

                    var li = document.createElement('li');
                    if (problem) li.className = 'bad';
                    var chip = document.createElement('span');
                    chip.className = 'file-type ' + ext;
                    chip.textContent = ext;
                    var name = document.createElement('span');
                    name.className = 'picked-name';
                    name.textContent = f.name;
                    var meta = document.createElement('span');
                    meta.className = 'file-size';
                    meta.textContent = problem || size(f.size);
                    li.append(chip, name, meta);
                    list.appendChild(li);
                });

                if (files.length > 10) {
                    var warn = document.createElement('li');
                    warn.className = 'bad';
                    warn.textContent = 'Choose at most 10 files.';
                    list.appendChild(warn);
                }

                btn.disabled = !ok;
                btn.textContent = files.length > 1 ? 'Upload ' + files.length + ' files' : 'Upload';
            }

            input.addEventListener('change', render);
            ['dragenter', 'dragover'].forEach(function (t) {
                zone.addEventListener(t, function (e) { e.preventDefault(); zone.classList.add('over'); });
            });
            ['dragleave', 'drop'].forEach(function (t) {
                zone.addEventListener(t, function (e) { e.preventDefault(); zone.classList.remove('over'); });
            });
            zone.addEventListener('drop', function (e) {
                if (e.dataTransfer && e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    render();
                }
            });
        })();
        </script>
    </section>

    <section class="panel history-card" aria-labelledby="history-title">
        <header class="panel-head">
            <h2 id="history-title">History</h2>
            <span class="panel-count">{{ $proposal->history->count() }}</span>
        </header>

        <ol class="timeline">
            @foreach ($proposal->history as $entry)
                <li class="tl-item">
                    <time class="tl-time" datetime="{{ $entry->created_at->toIso8601String() }}">
                        {{ $entry->created_at->format('M j, Y') }}
                        <span>{{ $entry->created_at->format('g:i A') }}</span>
                    </time>
                    <div class="tl-axis" aria-hidden="true">
                        <span class="tl-dot {{ $entry->event->value }}"></span>
                        <span class="tl-line"></span>
                    </div>
                    <div class="tl-content">
                        <div class="tl-title">{{ $entry->event->label() }}</div>
                        @if ($entry->description)
                            <p class="tl-detail">{{ $entry->description }}</p>
                        @endif
                    </div>
                </li>
            @endforeach

            @if ($proposal->status === \App\Enums\ProposalStatus::New)
                <li class="tl-item pending">
                    <span class="tl-time">In progress</span>
                    <div class="tl-axis" aria-hidden="true">
                        <span class="tl-dot loading"></span>
                        <span class="tl-line"></span>
                    </div>
                    <div class="tl-content">
                        <div class="tl-title">Awaiting decision</div>
                        <p class="tl-detail">
                            {{ \App\Enums\UserRole::current()->canDecide() ? 'Approve or disapprove this proposal from Actions.' : 'A reviewer will approve or disapprove this proposal.' }}
                        </p>
                    </div>
                </li>
            @endif
        </ol>
    </section>
    </div>
@endsection
