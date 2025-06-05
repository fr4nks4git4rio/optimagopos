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

<div class="mb-1">
    <div @if(!$dynamic) wire:ignore @endif x-data="{ }" x-init="() => {
{{--    $(document).on('select2:open', () => {--}}
{{--        document.querySelector('.select2-container--open .select2-search__field').focus();--}}
{{--    });--}}
    let parent = $el.parentElement;
    let found = false;
    let id = '';
    do{
        if(parent.classList.contains('modal')){
            if(parent.id){
                id = parent.id;
            }else{
                id = 'modal-'+Math.floor(Math.random() * 9999)
                parent.id = id;
            }
            found = true;
        }else{
            parent = parent.parentElement;
        }
    }while(!found);
    setTimeout(() => {
        console.log(id);
        $('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}').select2({
            dropdownParent: $('#'+id),
{{--            matcher: matchCustom,--}}
            placeholder: '{{ $placeholder }}',
            allowClear: true,
            language: 'es'
        });
    }, 100);
	$('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}').on('change', function(e) {
	    let elementName = $(this).attr('id').replace('-', '.');
	    @this.set(elementName, e.target.value);
        Livewire.hook('message.processed', (m, component) => {
            $('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}').select2({
                dropdownParent: $('#'+id),
{{--                matcher: matchCustom,--}}
                placeholder: '{{ $placeholder }}',
                allowClear: true,
                language: 'es'
            });
        })
    });
    window.addEventListener('reApplySelect2', event => {
        $('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}').select2({
            dropdownParent: $('#'+id),
{{--            matcher: matchCustom,--}}
            placeholder: '{{ $placeholder }}',
            allowClear: true,
            language: 'es'
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
        @if($label)
            <label for="{{ $model }}">{{ $label }}</label>
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
