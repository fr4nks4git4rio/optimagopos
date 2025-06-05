<?php

namespace App\Http\Livewire\Layouts;

use App\Models\Compras\GastoOperacion;
use App\Models\Cotizador\Poliza;
use App\Models\Cotizador\Proyecto;
use App\Models\Facturacion\Factura;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Livewire\Component;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Nav extends Component
{
    protected $listeners = ['$refresh', 'newNotification' => 'loadNotifications', 'profileChanged' => 'changeProfile', 'markNotificationAsRead', 'markNotificationsAllAsRead'];
    public $class_logo;
    public $show_title;
    public $notifications;
    public $movile_menu_hidden = 'left-hidden';
    public $page_width;
    public $time_type;
    public $time;
    public $user;
    public $is_admin;
    public $is_staff;
    public $is_guest;
    protected $profile = '';

    public function mount()
    {
        $this->show_title = true;
        $this->class_logo = '';
        $this->notifications = user()->unreadNotifications;
        $this->user = \auth()->user();
    }

    public function render()
    {
        return view('livewire.layouts.nav');
    }

    public function logout()
    {
        $user = User::find(user()->id);
        activity('Logout Usuario')
            ->on($user)
            ->event('login')
            ->withProperties(User::parseData(Arr::except(
                $user->toArray(),
                ['password','created_at', 'updated_at', 'deleted_at']
            )))
            ->log("El usuario con email $user->email se ha desconectado.");

        Auth::logout();

        return redirect()->to('/');
    }

    public function loadNotifications()
    {
        $this->notifications = user()->unreadNotifications;
    }

    public function markNotificationAsRead($id)
    {
        DB::table('notifications')->where('id', $id)->update(['read_at' => now()]);
        $this->notifications = \auth()->user()->unreadNotifications;
        $this->emit('$refresh');
    }

    public function markNotificationsAllAsRead()
    {
        $this->user->unreadNotifications->markAsRead();
        $this->notifications = \auth()->user()->unreadNotifications;
        $this->emit('$refresh');
    }

    public function goToLink($id)
    {
        DB::table('notifications')->where('id', $id)->update(['read_at' => now()]);
        return $this->redirect(json_decode(DB::table('notifications')->where('id', $id)->first()->data)->link);
    }

    public function changeProfile($profile)
    {
        $this->profile = $profile;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getProfileRoleProperty()
    {
        //        if(user()->roles()->count() == 1)
        //            return user()->role_str;
        return $this->profile;
    }

    public function getProfileExistProperty(): bool
    {
        return $this->profile_role != '';
    }

    public function getHasNotificationsProperty(): bool
    {
        return $this->notifications->count() > 0;
    }
}
