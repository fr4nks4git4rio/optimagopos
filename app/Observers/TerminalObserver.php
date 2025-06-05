<?php

namespace App\Observers;

use App\Models\Administracion\Traza;
use App\Models\Terminal;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TerminalObserver
{
    private $attr_except = ['id', 'created_at', 'updated_at', 'deleted_at'];
    /**
     * Handle the terminal "created" event.
     *
     * @param  Terminal $terminal
     * @return void
     */
    public function created(Terminal $terminal)
    {
        activity("Terminal Creada")
            ->on($terminal)
            ->event('created')
            ->withProperties(Terminal::parseData(Arr::except(
                $terminal->toArray(),
                $this->attr_except
            )))
            ->log('Terminal: ' . $terminal->identificador . ' ha sido creada.');
    }

    /**
     * Handle the terminal "updated" event.
     *
     * @param Terminal $terminal
     * @return void
     */
    public function updated(Terminal $terminal)
    {
        $attributes = Arr::except(
            $terminal->getDirty(),
            $this->attr_except
        );
        activity('Terminal Actualizada')
            ->on($terminal)
            ->event('updated')
            ->withProperty('attributes', Terminal::parseData($attributes))
            ->withProperty('old', Terminal::parseData(Arr::only($terminal->getOriginal(), array_keys($attributes))))
            ->log('Terminal: ' . $terminal->identificador . ' ha sido actualizada.');
    }

    /**
     * Handle the terminal "deleted" event.
     *
     * @param  Terminal $terminal
     * @return void
     */
    public function deleted(Terminal $terminal)
    {
        activity('Terminal Desactivada')
            ->on($terminal)
            ->event('deleted')
            ->withProperties(Terminal::parseData(Arr::except(
                $terminal->toArray(),
                $this->attr_except
            )))
            ->log('Terminal: ' . $terminal->identificador . ' ha sido desactivada.');
    }

    /**
     * Handle the terminal "restored" event.
     *
     * @param  Terminal $terminal
     * @return void
     */
    public function restored(Terminal $terminal)
    {
        activity('Terminal Restaurada')
            ->on($terminal)
            ->event('restored')
            ->withProperties(Terminal::parseData(Arr::except(
                $terminal->toArray(),
                $this->attr_except
            )))
            ->log('Terminal: ' . $terminal->identificador . ' ha sido restaurada.');
    }

    /**
     * Handle the terminal "force deleted" event.
     *
     * @param  Terminal $terminal
     * @return void
     */
    public function forceDeleted(Terminal $terminal)
    {
        activity('Terminal Eliminada Permanentemente')
            ->on($terminal)
            ->withProperties(Terminal::parseData(Arr::except(
                $terminal->toArray(),
                $this->attr_except
            )))
            ->log('Terminal: ' . $terminal->identificador . ' ha sido eliminada permanentemente.');
    }
}
