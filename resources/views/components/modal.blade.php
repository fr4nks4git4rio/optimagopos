@props(['formAction' => false])
<div>
    @if($formAction)
        <form wire:submit.prevent="{{ $formAction }}">
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

            <div class="modal-footer">
                {{ $buttons }}
            </div>
            @if($formAction)
        </form>
    @endif
</div>
