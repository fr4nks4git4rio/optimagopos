<div x-data="{
    editing: false
    }" class="nav-link mt-1 ">
    <a href="javascript:void(0)" class="nav-link pt-0 float-start">
        <span x-show="!is_mobile_screen">Tipo de Cambio:</span>
        <span x-show="is_mobile_screen">TC:</span>
    </a>
    @if($this->hay_tipo_cambio)
        <span x-show="!editing"
              @click="editing = true; setTimeout(() => {$refs.input.focus()}, 100);">&nbsp;{{$tipo_cambio}}</span>
        <input x-show="editing" x-ref="input" wire:model.lazy="tipo_cambio" type="text" @blur="editing = false"
               @keydown.enter="editing = false; $wire.emit('saveTipoCambio')"
               style="max-width: 70px;border-radius: 5px;line-height: 10px;border: none; color: #000;">
    @else
        @if($this->loading)
            <i class="spinner-border spinner-border-sm"></i>&nbsp;Cargando...
        @else
            <a x-show="!editing" href="javascript:void(0)" class="btn btn-primary" style="padding: 1px 4px; margin-top: -3px"
               @click="editing = true; setTimeout(() => {$refs.input2.focus()}, 100);"
               title="Entrar Manualmente"><i
                    class="bi bi-pencil"></i></a>
            <a x-show="!editing" href="javascript:void(0)" class="btn btn-success" style="padding: 1px 4px; margin-top: -3px"
               wire:click="searchDof"
               title="Obtener del DOF"><i
                    class="bi bi-cloud-download"></i></a>

            <input type="text" wire:model.lazy="tipo_cambio" x-ref="input2" x-show="editing" autofocus
                   wire:change="$emit('saveTipoCambio')" @blur="editing = false"
                   style="max-width: 70px;border-radius: 5px;line-height: 10px;border: none;">
        @endif
    @endif
</div>
