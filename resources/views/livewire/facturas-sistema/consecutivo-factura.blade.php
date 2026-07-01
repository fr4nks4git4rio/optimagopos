<div class="modal {{$show}}" id="{{$modal_id}}">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{$title}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        wire:click="$set('show', '')"></button>
            </div>
            <div class="modal-body">
                <x-input type="text" model="consecutivo" label="Consecutivo"/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$set('show', '')">
                    Cerrar
                </button>
                <button type="button" wire:click="guardar()" class="btn btn-success">Guardar</button>
            </div>
        </div>
    </div>
</div>
