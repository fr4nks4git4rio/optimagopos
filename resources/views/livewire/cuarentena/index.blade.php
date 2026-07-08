@section('title', 'Cuarentena')

<div>
    <h1 class="fs-1 mb-2 text-capitalize">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-lg-auto mb-3">
            <div class="input-group">
                <span class="input-group-text"><x-icon name="search" /></span>
                <input type="search" placeholder="Buscar Ticket" class="form-control text-capitalize"
                    wire:model.debounce.500ms="search">
            </div>
        </div>
        <div class="col-lg-auto mb-3">

            <x-dropdown icon="eye" :label="$perPage">
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
        <table class="table table-striped">
            <thead>
                <tr>
                    @foreach ($sorts as $sort)
                        <th class="text-left cursor-pointer text-uppercase" style="white-space: nowrap !important"
                            wire:click="changeSort('{{ $sort }}')">
                            <span>
                                @if ($this->sort == $sort)
                                    <i class="{{ $this->class_sort }}"></i>
                                @endif {{ $sort }}
                            </span>
                        </th>
                    @endforeach
                    <th class="text-center text-uppercase">{{ __('site.common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->fecha }}</td>
                        <td>{{ $ticket->texto }}</td>
                        <td>{{ $ticket->ip }}</td>
                        <td>{{ $ticket->cliente }}</td>
                        <td>{{ $ticket->sucursal }}</td>
                        <td>{{ $ticket->terminal }}</td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                @can('fix', App\Models\Cuarentena::find($ticket->id))
                                    <li class="list-inline-item">
                                        <x-action icon="tools" title="{{ __('site.common.edit') }}"
                                            click="$emit('openModal', 'cuarentena.fix', {registro: {{ $ticket->id }}})" />
                                    </li>
                                @endcan
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="list-group-item">
                                {{ __('site.common.results_not_found') }}
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :links="$tickets" :count="true" />
</div>
