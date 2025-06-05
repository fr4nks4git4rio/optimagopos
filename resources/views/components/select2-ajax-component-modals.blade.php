@props([
    'label' => null,
    'options' => [],
    'model',
    'lazy' => false,
    'placeholder' => '',
    'dynamic' => false,
    'url' => '',
    'parent' => 'modal-container',
     'on_change' => null
])

@php
    //    $options = Arr::isAssoc($options) ? $options : array_combine($options, $options);

        $label = $label ? "$label:" : null;
        if ($lazy) $bind = '.lazy';
        else $bind = '.defer';

        $id = \Illuminate\Support\Str::replace(".", "-", $model);
        $attributes = $attributes->class([
            'form-select',
            'select2',
            'is-invalid' => $errors->has($model),
        ])->merge([
            'id' => $id,
            'wire:model' . $bind => $model,
        ]);
        if ($on_change) $attributes['wire:change'] = $on_change;
@endphp

<div class="mb-1">
    <div @if(!$dynamic) wire:ignore @endif x-data="{ }" x-init="() => {
    $(document).on('select2:open', () => {
        document.querySelector('.select2-container--open .select2-search__field').focus();
    });
    setTimeout(() => {
        $('#{{$id}}.select2').select2({
            dropdownParent: $('#{{$parent}}'),
            ajax: {
                url: '{{$url}}',
                dataType: 'json',
                processResults: function(data) {
                   return {
                       results: data.items
                   };
                },
                cache: false
            },
            placeholder: '{{$placeholder}}',
            allowClear: true,
            language: 'es'
        });
    }, 100);
	$('#{{$id}}.select2').on('change', function(e) {
	    let elementName = $(this).attr('id').replaceAll('-', '.');
	    @this.set(elementName, e.target.value);
        Livewire.hook('message.processed', (m, component) => {
            setTimeout(() => {
                $('#{{$id}}.select2').select2({
                    dropdownParent: $('#{{$parent}}'),
                    ajax: {
                        url: '{{$url}}',
                        dataType: 'json',
                        processResults: function(data) {
                        return {
                            results: data.items
                        };
                        },
                        cache: false
                    },
                    placeholder: '{{$placeholder}}',
                    allowClear: true,
                    language: 'es'
                });
            }, 100);
        })
    });
{{--    $('#{{$model}}.select2').on('select2:open', function(e) {--}}
{{--        let elementName = $(this).attr('id').replace('-', '_');--}}
{{--        $('input.select2-search__field').on('input', function(){--}}
{{--            if($(this).val().length >= 3) {--}}
{{--                @this.call('fetchData_'+elementName, $(this).val());--}}
{{--            }--}}
{{--        });--}}
{{--    });--}}
    window.addEventListener('reApplySelect2', event => {
        setTimeout(() => {
            $('#{{$id}}.select2').select2({
                dropdownParent: $('#{{$parent}}'),
                ajax: {
                    url: '{{$url}}',
                    dataType: 'json',
                    processResults: function(data) {
                    return {
                        results: data.items
                    };
                    },
                    cache: true
                },
                placeholder: '{{$placeholder}}',
                allowClear: true,
                language: 'es'
            });
        }, 100);
    });
    window.addEventListener('set-data-{{$id}}', (event) => {
        select2 = $('#{{$id}}.select2').data('select2');
        if(select2){
            select2.destroy();
            select2.$element.find('option').remove();
            $('#{{$id}}.select2').select2({
                dropdownParent: $('#{{$parent}}'),
                ajax: {
                    url: '{{$url}}',
                    dataType: 'json',
                    processResults: function(data) {
                    return {
                        results: data.items
                    };
                    },
                    cache: true
                },
                placeholder: '{{$placeholder}}',
                allowClear: true,
                language: 'es'
            });
            event.detail.data.forEach(function(element){
                select2.$element.append(new Option(element.text, element.id, false, false));
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
            <label for="{{ $id }}" class="">{{ $label }}</label>
        @endif

        <select {{ $attributes }} style="width: 100%;">
            <option value="">{{$placeholder}}</option>

            @foreach($options as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
    </div>
    @error($model)
    <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
