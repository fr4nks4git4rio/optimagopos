<x-modal>
    <x-slot:title>
        Detalles de Traza
    </x-slot:title>

    <x-slot:content>
        <div class="row table-responsive">
            @if(isset(json_decode($log->properties)->old) && isset(json_decode($log->properties)->attributes))
            <div class="row">
                <div class="col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="fs-4">Antes</h4>
                        </div>
                        <div class="card-body">
                            <dl>
                                @foreach($this->getAttributes(json_decode($log->properties)->old) as $index => $item)
                                <dt>{{ __($index) }}:</dt>
                                <dd>{{ json_encode($item) == 'null' ? '-' : $this->plainText($index, json_encode($item)) }}</dd>
                                @endforeach
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="fs-4">Después</h4>
                        </div>
                        <div class="card-body">
                            <dl>
                                @foreach($this->getAttributes(json_decode($log->properties)->attributes) as $index => $item)
                                <dt>{{ __($index) }}:</dt>
                                <dd>{{ json_encode($item) == 'null' ? '-' : $this->plainText($index, json_encode($item)) }}</dd>
                                @endforeach
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            @elseif(isset(json_decode($log->properties)->attributes))
            <div class="card">
                <div class="card-header">
                    <h4 class="fs-4">Detalles</h4>
                </div>
                <div class="card-body">
                    <dl>
                        @foreach($this->getAttributes(json_decode($log->properties)->attributes) as $index => $item)
                        <dt>{{ __($index) }}:</dt>
                        <dd>{{ json_encode($item) == 'null' ? '-' : $this->plainText($index, json_encode($item)) }}</dd>
                        @endforeach
                    </dl>
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-header">
                    <h4 class="fs-4">Detalles</h4>
                </div>
                <div class="card-body">
                    <dl>
                        @foreach($this->getAttributes(json_decode($log->properties)) as $index => $item)
                        <dt>{{ __($index) }}:</dt>
                        <dd>{{ json_encode($item) == 'null' ? '-' : $this->plainText($index, json_encode($item)) }}</dd>
                        @endforeach
                    </dl>
                </div>
            </div>
            @endif
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            Cerrar
        </button>
    </x-slot:buttons>
</x-modal>
