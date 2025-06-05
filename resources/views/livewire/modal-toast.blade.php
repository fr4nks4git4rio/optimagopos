<x-modal form-action="delete">
    <x-slot:title>
        {{$title}}
    </x-slot:title>

    <x-slot:content>
        @foreach ($messages as $message)
        <x-alert icon="iconMessage('{{$message['type']}}')" alert="{{$message['type']}}">
            {{$message['text']}}
        </x-alert>
        @endforeach
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" wire:click="close">Cerrar</button>
    </x-slot:buttons>
</x-modal>
