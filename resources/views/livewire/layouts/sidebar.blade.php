{{-- PC MENU --}}
{{-- PC MENU --}}
{{-- {{$sidebar_with}} --}}
<div id="sidebar-menu" class="sidebar-menu bg-custom-light border-end border-2" :class="[sidebar_with, sidebar_mobile]"
    wire:ignore>
    <div class="d-flex flex-column align-items-center align-items-sm-start pt-2 text-white min-vh-100 bg-custom-light"
        style="min-height: 100vh !important;overflow-y: auto; overflow-x: hidden; height: 100%">
        {{-- <a href="/" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none"> --}}
        {{-- <span class="fs-5 d-none d-sm-inline">Menu</span> --}}
        {{-- </a> --}}
        <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100"
            id="menu_principal">
            @if (user()->hasAnyRole(['Admin', 'Manager']))
                @include('livewire.layouts.sidebars.client')
            @else
                @include('livewire.layouts.sidebars.owner')
            @endif
        </ul>
    </div>
</div>
