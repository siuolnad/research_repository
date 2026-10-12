@extends('layouts.app')

@section('title', 'Research Repository')

@section('content')
    <form class="content-head" method="GET" action="{{ route('proposals.index') }}">
        <button type="button" class="menu-btn" id="menu-btn" aria-expanded="false" aria-controls="sidebar">Menu</button>
        <h1 class="view-title">
            {{ ($filters['status'] ?? null) ? \App\Enums\ProposalStatus::from($filters['status'])->label() : 'All proposals' }}
        </h1>

        <div class="head-actions">
            @if (! empty($filters['status']))
                <input type="hidden" name="status" value="{{ $filters['status'] }}">
            @endif
            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search title or abstract" aria-label="Search proposals">
            <input type="number" name="year" value="{{ $filters['year'] ?? '' }}" placeholder="Year" min="1900" max="2100" aria-label="Filter by start year">
            <button type="submit" class="btn-ghost">Filter</button>
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
