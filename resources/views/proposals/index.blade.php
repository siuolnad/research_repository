@extends('layouts.app')

@section('title', 'Research Repository')

@section('content')
    <form class="content-head" method="GET" action="{{ route('proposals.index') }}">
        <button type="button" class="menu-btn" id="menu-btn" aria-expanded="false" aria-controls="sidebar">Menu</button>
        <h1 class="view-title">
            {{ ($filters['status'] ?? null) ? \App\Enums\ProposalStatus::from($filters['status'])->label() : 'All proposals' }}
        </h1>

        @php($isReviewer = \App\Enums\UserRole::current()->canDecide())

        <div class="head-actions">
            @if (! $isReviewer && ! empty($filters['status']))
                <input type="hidden" name="status" value="{{ $filters['status'] }}">
            @endif
            <div class="filter-bar">
                <svg class="filter-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <circle cx="7" cy="7" r="4.5" stroke="currentColor" stroke-width="1.5"/>
                    <path d="m10.5 10.5 3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search title or abstract" aria-label="Search proposals">
                @if ($isReviewer)
                    <span class="filter-divider" aria-hidden="true"></span>
                    <label class="filter-select">
                        <span class="status-dot {{ $filters['status'] ?? 'all' }}" aria-hidden="true"></span>
                        <select name="status" aria-label="Filter by status" onchange="this.form.requestSubmit()">
                            <option value="">All statuses</option>
                            @foreach (\App\Enums\ProposalStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <svg class="filter-chevron" width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="m4 6 4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </label>
                @endif
                <span class="filter-divider" aria-hidden="true"></span>
                <input type="number" name="year" value="{{ $filters['year'] ?? '' }}" placeholder="Year" min="1900" max="2100" aria-label="Filter by start year">
                <button type="submit" class="filter-submit">Filter</button>
            </div>
            @if (! empty($filters['search']) || ! empty($filters['year']) || ($isReviewer && ! empty($filters['status'])))
                <a class="filter-clear" href="{{ route('proposals.index', $isReviewer ? [] : array_filter(['status' => $filters['status'] ?? null])) }}">Clear</a>
            @endif
            <a class="btn-primary" href="{{ route('proposals.create') }}">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>Add proposal
            </a>
        </div>
    </form>

    <div class="count-line">
        {{ $proposals->total() }} {{ \Illuminate\Support\Str::plural('proposal', $proposals->total()) }}
    </div>

    <section aria-live="polite">
        @forelse ($proposals as $proposal)
            <article class="card">
                <div class="card-head">
                    <div>
                        <div class="case-kicker">
                            <span class="division-chip">{{ $proposal->research_area->label() }}</span>
                            <span class="docket-badge">₱{{ number_format((float) $proposal->budget, 2) }}</span>
                        </div>
                        <h2><a href="{{ route('proposals.show', $proposal->id) }}">{{ $proposal->title }}</a></h2>
                        <div class="byline">{{ implode(', ', $proposal->proponents) }}</div>
                        <div class="meta-line">{{ $proposal->start_date->format('M j, Y') }} – {{ $proposal->end_date->format('M j, Y') }}</div>
                    </div>
                    <span class="status-pill {{ $proposal->status->value }}">{{ $proposal->status->label() }}</span>
                </div>

                <p class="abstract">{{ $proposal->abstract }}</p>

                <div class="card-foot">
                    <div class="keywords">
                        @foreach ($proposal->keywords ?? [] as $keyword)
                            <span class="kw">{{ $keyword }}</span>
                        @endforeach
                    </div>
                    @include('proposals._actions')
                </div>
            </article>
        @empty
            <div class="empty">
                <div class="big">No proposals found</div>
                <div>Try a different filter, or add the first proposal.</div>
            </div>
        @endforelse
    </section>

    {{ $proposals->links('pagination.pager') }}
@endsection
