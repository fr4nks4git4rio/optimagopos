<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters: catalog tables with no dependencies go first,
     * then tables whose foreign keys point at tables seeded above.
     * TbEstadosSeeder must run before TbLocalidadesSeeder and
     * TbMunicipiosSeeder, since both reference tb_estados.id via
     * their estado_id foreign key.
     */
    public function run(): void
    {

        $this->call([
            TbEstadosSeeder::class,
            TbClaveUnidadesSeeder::class,
            TbClaveProdServsSeeder::class,
            TbCfdisSeeder::class,
            TbFormaPagosSeeder::class,
            TbMetodoPagosSeeder::class,
            TbMesesSeeder::class,
            TbModulosSeeder::class,
            TbMonedasSeeder::class,
            TbMotivosCancelacionFacturaSeeder::class,
            TbObjetosImpuestoSeeder::class,
            TbPeriodicidadesFacturaSeeder::class,
            TbRegimenFiscalesSeeder::class,
            TbSeriesSeeder::class,
            TbTipoComprobantesSeeder::class,
            TbTipoRelacionFacturasSeeder::class,
            TbLocalidadesSeeder::class,
            TbMunicipiosSeeder::class,
            TbOwnerSeeder::class,
            TbConfigsSeeder::class,
            TbPermissionsSeeder::class,
            TbUserSeeder::class,
        ]);
    }
}
