{{-- PC MENU --}}
{{-- PC MENU --}}
{{-- {{$sidebar_with}} --}}
<div class="col-auto px-0 sidebar-menu no-padding hidden-xs border-end border-2 border-top-0" :class="sidebar_with"
    id="sidebar-menu" wire:ignore>
    <div class="d-flex flex-column align-items-center align-items-sm-start pt-2 text-white min-vh-100 bg-custom-light"
        style="min-height: 100vh !important;overflow-y: auto; overflow-x: hidden; height: 100%">
        {{-- <a href="/" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none"> --}}
        {{-- <span class="fs-5 d-none d-sm-inline">Menu</span> --}}
        {{-- </a> --}}
        <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100"
            id="menu_principal">
            @if (user()->cliente_id)
                @include('livewire.layouts.sidebars.client')
            @else
                @include('livewire.layouts.sidebars.owner')
            @endif
        </ul>
        {{-- <hr> --}}
        {{-- <div class="dropdown pb-4"> --}}
        {{-- <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" --}}
        {{-- id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false"> --}}
        {{-- <img src="https://github.com/mdo.png" alt="hugenerd" width="30" height="30" class="rounded-circle"> --}}
        {{-- <span class="d-none {{$display}} mx-1">loser</span> --}}
        {{-- </a> --}}
        {{-- <ul class="dropdown-menu dropdown-menu-dark text-small shadow"> --}}
        {{-- <li><a class="dropdown-item" href="#">New project...</a></li> --}}
        {{-- <li><a class="dropdown-item" href="#">Settings</a></li> --}}
        {{-- <li><a class="dropdown-item" href="#">Profile</a></li> --}}
        {{-- <li> --}}
        {{-- <hr class="dropdown-divider"> --}}
        {{-- </li> --}}
        {{-- <li><a class="dropdown-item" href="#">Sign out</a></li> --}}
        {{-- </ul> --}}
        {{-- </div> --}}
    </div>
</div>
