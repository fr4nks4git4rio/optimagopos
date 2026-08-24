@section('title', __('site.reports.data_received.title'))

<div wire:init="init">
    <div wire:loading.delay>
        <div class="loading">
            <img src="{{ asset('img/loading.gif') }}" />
        </div>
    </div>
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-lg-auto mb-3 row">
            <div class="col-auto">
                <x-input label="{{ __('site.reports.data_received.start_date') }}" type="date" model="fechaInicio"
                    :lazy="true"></x-input>
            </div>
            <div class="col-auto">
                <x-input label="{{ __('site.reports.data_received.end_date') }}" type="date" model="fechaFin"
                    :lazy="true"></x-input>
            </div>
            <div class="col-auto">
                <div class="input-group pt-4">
                    <span class="input-group-text"><x-icon name="search" /></span>
                    <input type="search" placeholder="{{ __('site.reports.data_received.search') }}"
                        class="form-control" wire:model.live.debounce.500ms="search">
                </div>
            </div>
        </div>
        <div class="col-lg-auto mb-3">
            <x-dropdown icon="eye" :label="__($perPage)">
                @foreach ($perPages as $perPage)
                    @if ($perPage == $this->perPage)
                        <x-dropdown-item label="{{ $perPage }}" class="active"
                            click="$set('perPage', '{{ $perPage }}')" />
                    @else
                        <x-dropdown-item label="{{ $perPage }}" click="$set('perPage', '{{ $perPage }}')" />
                    @endif
                @endforeach
            </x-dropdown>
        </div>
    </div>

    <div class="list-group mb-3 table-responsive">
        <table class="table table-responsive table-striped">
            <thead>
                <tr>
                    @foreach ($sorts as $sort)
                        <th class="text-center align-middle cursor-pointer" rowspan="2"
                            style="white-space: nowrap !important" wire:click="changeSort('{{ $sort }}')">
                            <span>
                                @if ($this->sort == $sort)
                                    <i class="{{ $this->class_sort }}"></i>
                                @endif {{ $sort }}
                            </span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->fecha }}</td>
                        <td>{{ $log->log }}</td>
                        <td>{{ $log->data }}</td>
                        <td>{{ $log->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="list-group-item">
                                {{ __('site.common.results_not_found') }}...
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$logs" :count="true" />
</div>
