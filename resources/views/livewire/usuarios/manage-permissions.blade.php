<x-modal>
    <x-slot:title>
        <span class="text-capitalize">{{ __('site.users.manage_permissions.title') }}</span>
    </x-slot:title>

    <x-slot:content>
        <div class="card border-0 border border-primary border-start border-4 shadow-sm mb-3 mt-3">
            <div class="card-body">
                <h4 class="mb-0">{{ __('site.users.manage_permissions.role') }}: {{ $this->rol }}</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">{{ __('site.users.manage_permissions.role_permissions') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @forelse ($rolPermissionsGroup as $index => $permissions)
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
                                                            <input class="form-check-input" type="checkbox"
                                                                role="switch" disabled
                                                                id="chkAsociar-{{ $index }}-{{ $i }}"
                                                                wire:model="rolPermissionsGroup.{{ $index }}.{{ $i }}">
                                                            <label class="form-check-label fw-bold">
                                                                {{ __('site.permissions.' . $permission) }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="alert alert-info">
                                    {{ __('site.common.results_not_found') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">{{ __('site.users.manage_permissions.others_permissions') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @forelse ($othersPermissionsGroup as $index => $permissions)
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
                                                            <input class="form-check-input" type="checkbox"
                                                                role="switch"
                                                                id="chkAsociar-{{ $index }}-{{ $i }}"
                                                                wire:model.live="othersPermissionsGroup.{{ $index }}.{{ $i }}.selected">
                                                            <label class="form-check-label fw-bold"
                                                                style="cursor:pointer;"
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
                            @empty
                                <div class="alert alert-info">
                                    {{ __('site.common.results_not_found') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:content>
    <x-slot:buttons>
        <button type="button" class="btn btn-secondary text-capitalize" data-bs-dismiss="modal"
            wire:click="$dispatch('closeModal')">{{ __('site.common.close') }}</button>
    </x-slot:buttons>
</x-modal>
