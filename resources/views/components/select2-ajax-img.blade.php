@props([
    'label' => null,
    'options' => [],
    'model',
    'lazy' => false,
    'placeholder' => '',
    'dynamic' => false,
    'url' => '',
    'onChange' => 'false',
    'index' => ''
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
    <div wire:ignore x-data="{ }" x-init="() => {
    $(document).on('select2:open', () => {
        document.querySelector('.select2-container--open .select2-search__field').focus();
    });
    reApplySelect2();
	$('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}.select2').on('change', function(e) {
	    let elementName = $(this).attr('id').replaceAll('-', '.');
	    @this.set(elementName, e.target.value);
	    if('{{$onChange}}' !== 'false')
	        @this.emit('{{$onChange}}', '{{$index}}');
	    reApplySelect2();
    });
    window.addEventListener('reApplySelect2', event => {
        reApplySelect2();
    });
    window.addEventListener('set-data-{{\Illuminate\Support\Str::replace(".", "-", $model)}}', (event) => {
        select2 = $('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}.select2').data('select2');
        select2.destroy();
        select2.$element.find('option').remove();
        reApplySelect2();

        event.detail.data.forEach(function(element){
            select2.$element.append(new Option(element.text, element.id, false, false));
            $(select2.$element).find('option').attr('data-photo', element.photo)
        });
    });
    window.addEventListener('clear-data-{{\Illuminate\Support\Str::replace(".", "-", $model)}}', (event) => {
        select2 = $('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}.select2').data('select2');
        select2.destroy();
        select2.$element.find('option').remove();
        reApplySelect2();
    });

    function formatState (state) {
        if (!state.id) {
            return state.text;
        }

        var $state = $('<span><img/> <span></span></span>');

        // Use .text() instead of HTML string concatenation to avoid script injection issues
        $state.find('span').text(state.text);
        $state.find('img').attr('src', state.photo ?? state.element.dataset.photo);
        $state.find('img').css('height', '50px')

        return $state;
    };
    function reApplySelect2() {
        setTimeout(() => {
            $('#{{\Illuminate\Support\Str::replace(".", "-", $model)}}.select2').select2({
                ajax: {
                    url: '{{$url}}',
                    dataType: 'json',
                    processResults: function(data) {
                       return {
                           results: data.items
                       };
                    },
                    cache: false,
                },
                templateSelection: formatState,
                templateResult: formatState,
                placeholder: '{{$placeholder}}',
                allowClear: true,
                language: 'es'
            });
        }, 100);
    }
}">
        @if($label)
            <label for="{{ $model }}" class="">{{ $label }}</label>
        @endif

        <select {{ $attributes }} style="width: 100%; height: auto">
            <option value="">{{$placeholder}}</option>

            @foreach($options as $option)
                <option @if(isset($option['photo'])) data-photo="{{$option['photo']}}" @endif value="{{ $option['id'] }}">{{ $option['text'] }}</option>
            @endforeach
        </select>
    </div>
    @error($model)
    <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
