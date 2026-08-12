<div>
    @isset($jsPath)
        <script>
            {!! file_get_contents($jsPath) !!}
        </script>
    @endisset

    <div x-data="LivewireUIModal()" x-on:close.stop="setShowPropertyTo(false)"
        x-on:keydown.escape.window="show && closeModalOnEscape()" x-show="show" x-cloak
        style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 99999; display: flex; align-items: center; justify-content: center; overflow-y: auto;">
        {{-- Fondo oscuro / Backdrop --}}
        <div x-show="show" x-on:click="closeModalOnClickAway()"
            style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(0, 0, 0, 0.5); z-index: 99998;">
        </div>

        {{-- Contenedor del Modal con ANCHO DINÁMICO --}}
        <div class="modal-dialog my-4"
            :class="$wire.components[activeComponent]?.modalAttributes?.maxWidthClass ??
                $wire.components[activeComponent]?.attributes?.maxWidthClass ??
                $wire.components[activeComponent]?.arguments?.maxWidthClass ??
                'modal-xl'"
            style="position: relative; z-index: 99999; pointer-events: auto; width: 90%; transition: max-width 0.3s ease;">
            <div class="modal-content shadow-lg border-0 rounded-3 bg-white" id="modal-container">
                @forelse($components as $id => $component)
                    <div x-show="activeComponent == '{{ $id }}'" x-ref="{{ $id }}"
                        wire:key="{{ $id }}">
                        @livewire($component['name'], $component['arguments'], key($id))
                    </div>
                @empty
                @endforelse
            </div>
        </div>
    </div>
</div>
