@props([
    'key' => '',
])
<div>
    @isset($jsPath)
        <script>
            {!! file_get_contents($jsPath) !!}
        </script>
    @endisset
    @isset($cssPath)
        <style>
            {!! file_get_contents($cssPath) !!}
        </style>
    @endisset

    <!-- Modal Principal -->
    <div x-data="LivewireUIModal()" x-init="init();" wire:key="{{ $key }}"
        x-on:close.stop="setShowPropertyTo(false)" x-show="show" class="modal fade"
        x-bind:class="{ 'show d-block': show }" tabindex="-1"
        x-bind:style="show ? 'background-color: rgba(0, 0, 0, 0.5);' : ''">
        <div class="modal-dialog modal-dialog-centered" x-bind:class="modalWidth">
            <div x-show="show && showActiveComponent" id="modal-container" aria-modal="true" role="dialog"
                class="modal-content position-relative">
                <!-- Botón Cerrar -->
                <button x-show="show" x-on:click="closeModalOnClickAway()"
                    style="margin: 4px; right: 4px; top: 4px; z-index: 1091" type="button"
                    class="btn-close position-absolute" aria-label="Close"></button>

                <!-- Componentes Livewire -->
                @forelse($components as $id => $component)
                    <div x-show="getActiveComponent() == '{{ $id }}' || activeComponent == '{{ $id }}'"
                        x-ref="{{ $id }}" wire:key="{{ $id }}" class="w-100">
                        @livewire($component['name'], $component['attributes'], key($id))
                    </div>
                @empty
                @endforelse
            </div>
        </div>
    </div>
</div>
