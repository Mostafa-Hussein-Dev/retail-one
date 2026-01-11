<nav style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-top: 1px solid #e0e0e0; margin-top: 1rem;">
    <!-- Total Count -->
    <div style="color: #7f8c8d; font-size: 0.875rem;">
        إجمالي السجلات : {{ $paginator->total() }}
    </div>

    <!-- Pagination Controls -->
    <div style="display: flex; align-items: center; gap: 1rem;">
        <!-- Rows Per Page Selector -->
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="color: #7f8c8d; font-size: 0.875rem;">عدد السجلات في الصفحة</span>
            <select onchange="window.location.href=this.value" style="padding: 6px 32px 6px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 0.875rem; cursor: pointer; background: white; appearance: none; min-width: 60px;">
                @foreach([10, 20, 50, 100] as $option)
                    <option value="{{ Request::fullUrlWithQuery(['per_page' => $option]) }}" {{ Request::get('per_page', 10) == $option ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Displayed Rows Range -->
        <span style="color: #7f8c8d; font-size: 0.875rem;">
            {{ ($paginator->currentPage() - 1) * $paginator->perPage() + 1 }}-{{ min($paginator->currentPage() * $paginator->perPage(), $paginator->total()) }} من {{ $paginator->total() }}
        </span>

        <!-- Navigation Actions -->
        <div style="display: flex; gap: 0.25rem;">
            @if (!$paginator->onFirstPage())
                <a href="{{ $paginator->previousPageUrl() }}" style="display: flex; align-items: center; justify-content: center; padding: 6px 12px; border: 1px solid #e0e0e0; background: white; border-radius: 4px; cursor: pointer; text-decoration: none; color: #7f8c8d;">
                    ‹
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" style="display: flex; align-items: center; justify-content: center; padding: 6px 12px; border: 1px solid #e0e0e0; background: white; border-radius: 4px; cursor: pointer; text-decoration: none; color: #7f8c8d;">
                    ›
                </a>
            @endif
        </div>
    </div>
</nav>
