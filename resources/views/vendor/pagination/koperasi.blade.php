@if ($paginator->hasPages())
<nav class="koperasi-pagination" aria-label="Navigasi halaman">
  <div class="paging-info">
    Menampilkan
    <strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong>
    dari <strong>{{ $paginator->total() }}</strong> data
  </div>
  <ul class="paging-list">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
      <li class="paging-item disabled">
        <span class="paging-link"><i class="bi bi-chevron-left"></i></span>
      </li>
    @else
      <li class="paging-item">
        <a class="paging-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">
          <i class="bi bi-chevron-left"></i>
        </a>
      </li>
    @endif

    {{-- Page numbers --}}
    @foreach ($elements as $element)
      @if (is_string($element))
        <li class="paging-item disabled"><span class="paging-link paging-dots">…</span></li>
      @endif
      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <li class="paging-item active" aria-current="page">
              <span class="paging-link">{{ $page }}</span>
            </li>
          @else
            <li class="paging-item">
              <a class="paging-link" href="{{ $url }}">{{ $page }}</a>
            </li>
          @endif
        @endforeach
      @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
      <li class="paging-item">
        <a class="paging-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">
          <i class="bi bi-chevron-right"></i>
        </a>
      </li>
    @else
      <li class="paging-item disabled">
        <span class="paging-link"><i class="bi bi-chevron-right"></i></span>
      </li>
    @endif
  </ul>
</nav>
@endif
