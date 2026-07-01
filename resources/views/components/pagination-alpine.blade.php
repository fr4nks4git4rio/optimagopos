@props(['pagination', 'onChange'])

<nav x-show="{{ $pagination }} && {{ $pagination }}.last_page > 1"
     x-cloak
     class="d-flex justify-content-between align-items-center w-100">

    <div class="text-muted">
        Mostrando:
        <span x-text="{{ $pagination }}.from"></span> -
        <span x-text="{{ $pagination }}.to"></span> de
        <span x-text="{{ $pagination }}.total"></span>
    </div>

    <ul class="pagination mb-0">

        <li class="page-item" :class="{ 'disabled': {{ $pagination }}.current_page === 1 }">
            <a href="#" class="page-link"
                @click.prevent="
                    if ({{ $pagination }}.current_page > 1) {
                        {{ $onChange }}({{ $pagination }}.current_page - 1)
                    }
                ">
                <i class="bi bi-caret-left"></i>
            </a>
        </li>

        <template x-for="page in {{ $pagination }}.last_page" :key="page">
            <li class="page-item" :class="{ 'active': page === {{ $pagination }}.current_page }">
                <a href="#" class="page-link" x-text="page" @click.prevent="{{ $onChange }}(page)">
                </a>
            </li>
        </template>

        <li class="page-item"
            :class="{ 'disabled': {{ $pagination }}.current_page === {{ $pagination }}.last_page }">
            <a href="#" class="page-link"
                @click.prevent="
                    if ({{ $pagination }}.current_page < {{ $pagination }}.last_page) {
                        {{ $onChange }}({{ $pagination }}.current_page + 1)
                    }
                ">
                <i class="bi bi-caret-right"></i>
            </a>
        </li>

    </ul>
</nav>
