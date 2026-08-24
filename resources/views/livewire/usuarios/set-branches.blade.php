<x-modal>
    <x-slot:title>
        <span class="text-capitalize">{{ __('site.users.set_branches.title') }}</span>
    </x-slot:title>

    <x-slot:content>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row">
                    @foreach ($sucursales as $index => $sucursal)
                        <div class="col-auto px-2">
                            <div class="border border-1 border-success bg-site-primary-subtle b-radius-10 px-3">
                                <div class="form-check form-switch mb-2 mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="chkAsociar-{{ $index }}"
                                        wire:model.live="sucursales.{{ $index }}.seleccionada">
                                    <label class="form-check-label fw-bold" style="cursor:pointer;"
                                        for="chkAsociar-{{ $index }}">
                                        {{ $sucursal['nombre_comercial'] }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-slot:content>
    <x-slot:buttons>
        <button type="button" class="btn btn-secondary text-capitalize" data-bs-dismiss="modal"
            wire:click="$dispatch('closeModal')">{{ __('site.common.close') }}</button>
    </x-slot:buttons>
</x-modal>
