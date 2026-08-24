<x-modal form-action="save">
    <x-slot:title>
        {{ __('site.roles.manage_permissions.title') }}
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init" class="container py-4">
            <div class="card border-0 border-start border-primary border-4 mb-3 shadow-sm">
                <div class="card-body">
                    <h4 class="mb-0">{{ __('site.roles.manage_permissions.role') }}:
                        {{ __('site.roles.values.' . $role->name) }}</h4>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header">
                    {{ __('site.roles.manage_permissions.available_permissions') }}
                </div>
                <div class="card-body">
                    @foreach ($permissionsGroups as $index => $permissions)
                        <div class="card border border-1 border-success mb-4">
                            <div class="card-body position-relative">
                                <div class="position-absolute px-2"
                                    style="top: -10px;left: 2px;background: #f8fafc;border-radius: 5px;">
                                    <h5 class="mb-0">{{ __('site.roles.manage_permissions.' . $index) }}</h5>
                                </div>
                                <div class="row gap-2">
                                    @foreach ($permissions as $i => $permission)
                                        <div class="col-auto px-2">
                                            <div
                                                class="border border-1 border-success bg-site-primary-subtle b-radius-10 px-3">
                                                <div class="form-check form-switch mb-2 mt-2">
                                                    <input class="form-check-input" type="checkbox" role="switch"
                                                        id="chkAsociar-{{ $index }}-{{ $i }}"
                                                        wire:model.live="permissionsGroups.{{ $index }}.{{ $i }}.selected">
                                                    <label class="form-check-label fw-bold" style="cursor:pointer;"
                                                        for="chkAsociar-{{ $index }}-{{ $i }}">
                                                        {{ __('site.permissions.' . $permission['name']) }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
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
            wire:click="$dispatch('closeModal')">
            {{ __('site.common.close') }}
        </button>
    </x-slot:buttons>
</x-modal>
