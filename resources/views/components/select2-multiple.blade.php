@props([
    'label' => null,
    'options' => [],
    'model',
    'lazy' => false,
    'placeholder' => '',
    'dynamic' => false,
    'tags' => false,
    'maxSelections' => null,
])

@php
    //    $options = Arr::isAssoc($options) ? $options : array_combine($options, $options);
    $label = $label ? "$label:" : null;
    if ($lazy) {
        $bind = '.lazy';
    } else {
        $bind = '.defer';
    }

    $id = \Illuminate\Support\Str::replace('.', '-', $model);
    $attributes = $attributes->class(['form-select', 'select2', 'is-invalid' => $errors->has($model)])->merge([
        'id' => $id,
        'wire:model' . $bind => $model,
    ]);
    $maxSelectionsJs = $maxSelections !== null ? (int) $maxSelections : -1;
@endphp

<div class="mb-1">
    <div @if (!$dynamic) wire:ignore @endif x-data="{}" x-init="() => {
        const maximumSelectionLength = {{ $maxSelectionsJs }};

        $(document).on('select2:open', () => {
            document.querySelector('.select2-container--open .select2-search__field').focus();
        });
        setTimeout(() => {
            $('#{{ $id }}.select2').select2({
                {{--            matcher: matchCustom, --}}
                placeholder: '{{ $placeholder }}',
                allowClear: true,
                language: 'es',
                multiple: true,
                tags: '{{ $tags }}',
                maximumSelectionLength: maximumSelectionLength
            });
        }, 100);
        $('#{{ $id }}.select2').on('change', function(e) {
            let elementName = $(this).attr('id').replaceAll('-', '.');
            let closeButton = $('.select2-selection__clear')[0];
            if (typeof(closeButton) != 'undefined') {
                if ($(this).val().length <= 0) {
                    $('.select2-selection__clear')[0].children[0].innerHTML = '';
                } else {
                    $('.select2-selection__clear')[0].children[0].innerHTML = 'x';
                }
            }
            if ($(this).val().length == 1 && $(this).val()[0] == '') {
                @this.set(elementName, []);
            } else {
                @this.set(elementName, $(this).val());
            }
            Livewire.hook('message.processed', (m, component) => {
                $('#{{ $id }}.select2').select2({
                    placeholder: '{{ $placeholder }}',
                    language: 'es',
                    multiple: true,
                    allowClear: true,
                    tags: '{{ $tags }}',
                    maximumSelectionLength: maximumSelectionLength
                });
            })
        });
        window.addEventListener('reApplySelect2', event => {
            $('#{{ $id }}.select2').select2({
                {{--                matcher: matchCustom, --}}
                placeholder: '{{ $placeholder }}',
                language: 'es',
                multiple: true,
                allowClear: true,
                tags: '{{ $tags }}',
                maximumSelectionLength: maximumSelectionLength
            });
        });

        function matchCustom(params, data) {
            // If there are no search terms, return all of the data
            if ($.trim(params.term) === '') {
                return [];
            }

            // Do not display the item if there is no 'text' property
            if (typeof data.text === 'undefined') {
                return null;
            }

            // `params.term` should be the term that is used for searching
            // `data.text` is the text that is displayed for the data object
            if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                var modifiedData = $.extend({}, data, true);
                //modifiedData.text += ' (matched)';

                // You can return modified objects from here
                // This includes matching the `children` how you want in nested data sets
                return modifiedData;
            }

            // Return `null` if the term should not be displayed
            return null;
        }
    }">
        @if ($label)
            <label for="{{ $model }}" class="text-capitalize">{{ $label }}</label>
        @endif

        <select {{ $attributes }} name="{{ $model }}[]" multiple="multiple" style="width: 100%;">
            <option value="">{{ $placeholder }}</option>

            @foreach ($options as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
    </div>
    @if ($maxSelections !== null)
        <div class="fs-8 text-muted mt-1">
            <i class="bi bi-info-circle me-1"></i> Máximo {{ $maxSelections }} selección(es).
        </div>
    @endif
    @error($model)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
