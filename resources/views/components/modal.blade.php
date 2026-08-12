@props(['formAction' => false])
<div class="p-4 bg-white rounded">
    @if ($formAction)
        <form wire:submit="{{ $formAction }}">
    @endif
    @isset($title)
        <div class="modal-header">
            <h3 class="modal-title fs-3">
                {{ $title }}
            </h3>
        </div>
    @endisset
    <div class="modal-body">
        {{ $content }}
    </div>

    <div class="modal-footer pt-2 gap-2">
        {{ $buttons }}
    </div>
    @if ($formAction)
        </form>
    @endif

    @isset($modals)
        {{ $modals }}
    @endisset
</div>
