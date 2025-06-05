@props([
    'key' => ''
])
<div>
    @isset($jsPath)
        <script>{!! file_get_contents($jsPath) !!}</script>
    @endisset
    @isset($cssPath)
        <style>{!! file_get_contents($cssPath) !!}</style>
    @endisset
    <style>
        .show {
            display: inline-block;
        }
    </style>
    <div
        x-data="LivewireUIModal()"
        x-init="init();"
        wire:key="{{$key}}"
        x-on:close.stop="setShowPropertyTo(false)"
        {{--            x-on:keydown.escape.window="closeModalOnEscape()"--}}
        x-show="show"
        class="modal show"
    >
        <div class="modal-dialog" x-bind:class="modalWidth">
            <div
                x-show="show && showActiveComponent"
                {{--                x-transition:enter="ease-out duration-300"--}}
                {{--                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"--}}
                {{--                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"--}}
                {{--                x-transition:leave="ease-in duration-200"--}}
                {{--                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"--}}
                {{--                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"--}}
                {{--                x-bind:class="modalWidth"--}}
                id="modal-container"
                x-trap.noscroll.inert="show && showActiveComponent"
                aria-modal="true" class="modal-content">
                <button
                    x-show="show"
                    x-on:click="closeModalOnClickAway()"
                    {{--                        x-transition:enter="ease-out duration-300"--}}
                    {{--                        x-transition:enter-start="opacity-0"--}}
                    {{--                        x-transition:enter-end="opacity-100"--}}
                    {{--                        x-transition:leave="ease-in duration-200"--}}
                    {{--                        x-transition:leave-start="opacity-100"--}}
                    {{--                        x-transition:leave-end="opacity-0"--}}
                    style="margin: 4px; right: 4px; top: 4px; z-index: 1055"
                    type="button" class="btn-close position-absolute" data-bs-dismiss="modal"
                    aria-label="Close"></button>
                @forelse($components as $id => $component)
                    <template x-if="activeComponent == '{{ $id }}'">
                        <div x-show.immediate="activeComponent == '{{ $id }}'" x-ref="{{ $id }}" wire:key="{{ $id }}"
                             class="active" style="display: block !important">
                            @livewire($component['name'], $component['attributes'], key($id))
                        </div>
                    </template>
                    <template x-if="activeComponent != '{{ $id }}'">
                        <div x-show.immediate="activeComponent == '{{ $id }}'" x-ref="{{ $id }}" wire:key="{{ $id }}" style="display: none !important;">
                            @livewire($component['name'], $component['attributes'], key($id))
                        </div>
                    </template>
                @empty
                @endforelse
            </div>
        </div>
    </div>
</div>
