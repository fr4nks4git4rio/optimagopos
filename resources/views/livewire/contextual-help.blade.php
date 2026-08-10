<div>
    <div class="list-group list-group-flush">
        @forelse($terms as $item)
            <div class="list-group-item px-0 py-3">
                <h6 class="mb-1 text-primary fw-bold">{{ $item['term'] }}</h6>
                <p class="mb-0 small text-secondary">{{ $item['definition'] }}</p>
            </div>
        @empty
            <p class="text-muted small">{{ __('site.contextual_help.no_data') }}</p>
        @endforelse
    </div>
</div>
