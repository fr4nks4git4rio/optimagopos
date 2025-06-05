<div x-data="{show:false, show_closable: false}"
     @loading-show.window="show = {{$close ? 'false' : 'true'}};"
     @mouseenter="show_closable = false"
     @mouseleave="show_closable = false"
     x-show="show"
     x-cloak
     class="position-fixed" style="top: 50px; right: 10px; z-index: 1055"
>
    <div class="alert alert-info">{{ $message }}</div>
</div>
