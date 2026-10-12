@if ($paginator->hasPages())
    <nav class="pager" aria-label="Pagination">
        <a class="btn-ghost {{ $paginator->onFirstPage() ? 'disabled' : '' }}" href="{{ $paginator->previousPageUrl() ?? '#' }}" rel="prev">&larr; Previous</a>
        <span class="page-ind">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
        <a class="btn-ghost {{ $paginator->hasMorePages() ? '' : 'disabled' }}" href="{{ $paginator->nextPageUrl() ?? '#' }}" rel="next">Next &rarr;</a>
    </nav>
@endif
