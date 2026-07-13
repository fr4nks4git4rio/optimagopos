<x-modal>
    <x-slot:title>
        {{ __('site.logs.show.title') }}
    </x-slot:title>

    <x-slot:content>
        @php
            $properties = json_decode($log->properties);
            $hasOld = isset($properties->old);
            $hasAttributes = isset($properties->attributes);
        @endphp

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                @if ($hasOld && $hasAttributes)
                    <div class="d-flex align-items-center mb-3 gap-2">
                        <i class="bi bi-arrow-left-right text-primary fs-5"></i>
                        <h4 class="h5 fw-bold text-dark mb-0">{{ __('site.logs.show.changes_made') }}</h4>
                    </div>

                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="table-light text-uppercase tracking-wider" style="font-size: 0.75rem;">
                                <tr>
                                    <th class="ps-4 text-muted fw-semibold" style="width: 30%;">{{ __('site.logs.show.property') }}
                                    </th>
                                    <th class="text-danger fw-semibold" style="width: 35%;">{{ __('site.logs.show.before') }}</th>
                                    <th class="text-success fw-semibold" style="width: 35%;">{{ __('site.logs.show.after') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $oldAttributes = (array)$this->getAttributes($properties->old);
                                    $newAttributes = (array)$this->getAttributes($properties->attributes);
                                @endphp

                                @foreach ($newAttributes as $index => $newItem)
                                    @php
                                        $oldItem = $oldAttributes[$index] ?? null;
                                        $oldText =
                                            json_encode($oldItem) == 'null'
                                                ? '-'
                                                : $this->plainText($index, json_encode($oldItem));
                                        $newText =
                                            json_encode($newItem) == 'null'
                                                ? '-'
                                                : $this->plainText($index, json_encode($newItem));
                                        $isChanged = $oldText !== $newText;
                                    @endphp
                                    <tr class="{{ $isChanged ? 'table-warning-subtle' : '' }}">
                                        <td class="ps-4 fw-medium text-secondary">{{ __($index) }}</td>
                                        <td class="text-muted text-decoration-line-through">{{ $oldText }}</td>
                                        <td class="fw-semibold text-dark">{{ $newText }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    @php
                        $targetAttributes = $hasAttributes ? $properties->attributes : $properties;
                        $attributesList = $this->getAttributes($targetAttributes);
                    @endphp

                    <div class="d-flex align-items-center mb-3 gap-2">
                        <i class="bi bi-info-circle text-primary fs-5"></i>
                        <h4 class="h5 fw-bold text-dark mb-0">{{ __('site.logs.show.register_details') }}</h4>
                    </div>

                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="table-light text-uppercase tracking-wider" style="font-size: 0.75rem;">
                                <tr>
                                    <th class="ps-4 text-muted fw-semibold" style="width: 40%;">{{ __('site.logs.show.property') }}
                                    </th>
                                    <th class="text-muted fw-semibold" style="width: 60%;">{{ __('site.logs.show.value') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attributesList as $index => $item)
                                    <tr>
                                        <td class="ps-4 fw-medium text-secondary">{{ __($index) }}</td>
                                        <td class="text-dark fw-semibold">
                                            {{ json_encode($item) == 'null' ? '-' : $this->plainText($index, json_encode($item)) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            {{__('site.common.close')}}
        </button>
    </x-slot:buttons>
</x-modal>
