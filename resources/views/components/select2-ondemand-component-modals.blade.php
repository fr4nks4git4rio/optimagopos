@props([
    'label' => null,
    'options' => [],
    'model',
    'lazy' => false,
    'placeholder' => '',
    'dynamic' => false
])

@php
    //    $options = Arr::isAssoc($options) ? $options : array_combine($options, $options);
        $label = $label ? "$label:" : null;
        if ($lazy) $bind = '.lazy';
        else $bind = '.defer';

        $attributes = $attributes->class([
            'form-select',
            'select2',
            'is-invalid' => $errors->has($model),
        ])->merge([
            'id' => \Illuminate\Support\Str::replace(".", "-", $model),
            'wire:model' . $bind => $model,
        ]);
@endphp

<div class="mb-3">
    <div @if(!$dynamic) wire:ignore @endif x-data="{ }" x-init="() => {
    $(document).on('select2:open', () => {
        document.querySelector('.select2-container--open .select2-search__field').focus();
    });
    setTimeout(() => {
        $('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}.select2').select2({
            dropdownParent: $('#modal-container'),
            placeholder: '{{$placeholder}}',
            allowClear: true,
            language: 'es',
        });
    }, 100);
	$('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}.select2').on('change', function(e) {
	    let elementName = $(this).attr('id').replace('-', '.');
	    @this.set(elementName, e.target.value);
        Livewire.hook('message.processed', (m, component) => {
            $('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}.select2').select2({
                dropdownParent: $('#modal-container'),
                placeholder: '{{$placeholder}}',
                allowClear: true,
                language: 'es',
            });
        })
    });
    $('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}.select2').on('select2:open', function(e) {
        let elementName = $(this).attr('id').replace('-', '_');
        $('input.select2-search__field').on('input', function(){
            if($(this).val().length >= 3) {
                @this.call('fetchData_'+elementName, $(this).val());
            }
        });
    });
    window.addEventListener('reApplySelect2', (event) => {
        $('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}.select2').select2({
            dropdownParent: $('#modal-container'),
            placeholder: '{{$placeholder}}',
            allowClear: true,
            language: 'es',
        });
    });
    window.addEventListener('set-data-{{\Illuminate\Support\Str::replace(".", "_", $model)}}', (event) => {
        select2 = $('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}.select2').data('select2');
        select2.destroy();
        select2.$element.find('option').remove();
        $('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}.select2').select2({
            dropdownParent: $('#modal-container'),
            placeholder: '{{$placeholder}}',
            allowClear: true,
            language: 'es',
        });

        if(event.detail.term.length > 0)
            select2.$element.append(new Option('Seleccione', '', false, false));

        event.detail.data.forEach(function(element){
            select2.$element.append(new Option(element.label, element.value, false, false));
        });
        if(event.detail.term.length > 0) {
            $('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}.select2').select2('open');
            $('input.select2-search__field').val(event.detail.term);

            $('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}.select2').on('change', function(e) {
                let elementName = $(this).attr('id').replace('-', '.');
                @this.set(elementName, e.target.value);
                Livewire.hook('message.processed', (m, component) => {
                    $('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}.select2').select2({
                        dropdownParent: $('#modal-container'),
                        placeholder: '{{$placeholder}}',
                        allowClear: true,
                        language: 'es',
                    });
                })
            });
        }
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
        @if($label)
            <label for="{{ $model }}" class="form-label">{{ $label }}</label>
        @endif

        <select {{ $attributes }} style="width: 100%;">
            <option value="">{{$placeholder}}</option>

            @foreach($options as $option)
                <option @if(isset($option['photo'])) data-photo="{{$option['photo']}}"
                        @endif value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
    </div>
    @error($model)
    <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
