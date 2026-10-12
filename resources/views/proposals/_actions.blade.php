<details class="menu">
    <summary class="menu-trigger" aria-haspopup="menu">
        Actions
        <svg class="menu-chevron" width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <path d="m4 6 4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </summary>
    <div class="menu-list" role="menu">
        @unless (request()->routeIs('proposals.show'))
            <a class="menu-item" role="menuitem" href="{{ route('proposals.show', $proposal->id) }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M1.5 8S4 3.5 8 3.5 14.5 8 14.5 8 12 12.5 8 12.5 1.5 8 1.5 8Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                    <circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.5"/>
                </svg>View proposal
            </a>
        @endunless

        @if ($proposal->status === \App\Enums\ProposalStatus::New)
            <a class="menu-item" role="menuitem" href="{{ route('proposals.edit', $proposal->id) }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M10.8 2.7a1.4 1.4 0 0 1 2 2L5.5 12l-3 .8.8-3 7.5-7.1Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                </svg>Edit
            </a>
            <div class="menu-sep" role="separator"></div>
            <form method="POST" action="{{ route('proposals.approve', $proposal->id) }}" onsubmit="return confirm('Approve this proposal? This decision is final.')">
                @csrf
                <button type="submit" class="menu-item approve" role="menuitem">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                        <path d="m5.4 8.2 1.9 1.9 3.4-3.8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>Approve
                </button>
            </form>
            <button type="button" class="menu-item disapprove" role="menuitem" data-disapprove-url="{{ route('proposals.disapprove', $proposal->id) }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                    <path d="m5.8 5.8 4.4 4.4M10.2 5.8l-4.4 4.4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>Disapprove
            </button>
        @endif

        <div class="menu-sep" role="separator"></div>
        <form method="POST" action="{{ route('proposals.destroy', $proposal->id) }}" onsubmit="return confirm('Delete this proposal?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="menu-item danger" role="menuitem">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M2.5 4.5h11M6.5 4.5V3a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1.5M4 4.5l.6 8a1 1 0 0 0 1 .9h4.8a1 1 0 0 0 1-.9l.6-8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>Delete
            </button>
        </form>
    </div>
</details>
