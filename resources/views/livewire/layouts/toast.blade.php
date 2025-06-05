<div x-data="{show:false,
            show_closable: false,
            can_close:true,
            timeout: @entangle('timeout'),
            closeProcess(){
                this.can_close = true;
                setTimeout(() => {
                        if(this.can_close)this.show=false
                    },this.timeout);
            }
        }"
     @toast-message-show.window="show = true; closeProcess();"
     @mouseenter="show_closable = true; can_close = false"
     @mouseleave="show_closable = false; closeProcess();"
     x-show="show"
     x-cloak
     class="position-fixed" style="top: 50px; right: 10px; z-index: 1066; max-width: 600px"
>
    <div class="{{ $alertTypeClasses[$alertType] }} pb-0 pt-2">
        <x-icon class="fs-3 align-middle position-absolute" style="top: 0; left:0" name="{{$icons[$alertType]}}"></x-icon>
        <p style="margin-left: 35px; margin-top: 5px; margin-bottom: 15px">{!! $message !!}</p>
        <a x-show="show_closable"
           @click="show=false"
           href="javascript:void(0)" class="position-absolute fs-5 text-decoration-none" style="top: 0; right: 4px"><x-icon name="x-circle"></x-icon></a>
    </div>
</div>
