<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TbPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('role_has_permissions')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('model_has_permissions')->delete();
        DB::table('roles')->delete();
        DB::table('permissions')->delete();

        DB::table('permissions')->insert(['name' => 'dashboardResume-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'dashboardResume-filterData', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'dashboardResume-viewPOSOperations', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'dashboardResume-viewSummary', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'dashboardResume-viewTransactions', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'dashboardResume-viewProducts', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'dashboardResume-viewPayments', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'dashboardResume-viewCorrections', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'dashboardResume-VideoKitchen', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'clients-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'clients-view', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'clients-create', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'clients-update', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'clients-delete', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'clients-restore', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'myCompany-view', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'myCompany-update', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'settings-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'settings-view', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'settings-update', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'quarantine-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'quarantine-fix', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'quarantine-delete', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'modules-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'modules-create', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'modules-update', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'modules-delete', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'modules-restore', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'packages-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'packages-create', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'packages-update', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'packages-delete', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'packages-restore', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'branches-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'branches-create', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'branches-update', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'branches-setPaymentForms', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'branches-setConfigs', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'branches-delete', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'branches-restore', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'subscriptions-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'subscriptions-view', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'subscriptions-create', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'subscriptions-update', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'users-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'users-view', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'users-create', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'users-update', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'users-assignPermissions', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'users-setBranches', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'users-delete', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'users-restore', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'roles-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'roles-view', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'roles-create', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'roles-update', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'roles-assignPermissions', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'roles-delete', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'terminals-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'terminals-view', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'terminals-create', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'terminals-update', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'terminals-delete', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'terminals-restore', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'logs-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'logs-view', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'panelPac-view', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'panelPac-update', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'invoiceHeader-view', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'invoiceHeader-update', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'invoices-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'invoices-view', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'invoices-createInvoice', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'invoices-createComplement', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'invoices-createCreditNote', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'invoices-updateInvoice', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'invoices-updateComplement', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'invoices-updateCreditNote', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'invoices-stamp', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'invoices-cancel', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'invoices-delete', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'accountsReceivable-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'accountsReceivable-view', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'reportsIncome-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'reportsIncome-view', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'reportsIncome-print', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'reportsIncome-export', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        //TODO Reportes
        DB::table('permissions')->insert(['name' => 'reportsDataReceived-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'reportsArticlesSold-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'reportsArticlesSold-print', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'reportsArticlesSold-export', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'reportsBestSellingProducts-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'reportsOperationsHistory-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'reportsVKTicketHistory-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'reportsVKTicketHistory-print', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'reportsVKTicketHistory-export', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'reportsDailySales-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'reportsDailySales-print', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'reportsDailySales-export', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'reportsSalesByOperator-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'reportsSalesByOperator-print', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('permissions')->insert(['name' => 'reportsSalesByOperator-export', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('permissions')->insert(['name' => 'reportsTestingOperationsHistory-viewAny', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('roles')->insert(['name' => 'SuperAdmin', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('roles')->insert(['name' => 'Admin', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('roles')->insert(['name' => 'Accountant', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);
        DB::table('roles')->insert(['name' => 'Manager', 'guard_name' => 'web', 'created_at' => now()->format('Y-m-d H:i:s')]);

        $roleSuperAdmin = Role::findByName('SuperAdmin');
        $roleSuperAdmin->syncPermissions(DB::table('permissions')
            ->pluck('name')
            ->toArray());

        $roleAccountant = Role::findByName('Accountant');
        $roleAccountant->syncPermissions(DB::table('permissions')
            ->whereRaw("
            name like 'clients-%'
            || name like 'invoices-%'
            || name like 'branches-%'
            ")
            ->pluck('name')
            ->toArray());

        $roleAdmin = Role::findByName('Admin');
        $roleAdmin->syncPermissions(DB::table('permissions')
            ->whereRaw("
            name NOT LIKE 'clients-%'
            and name not like 'settings-%'
            and name not like 'quarantine-%'
            and name not like 'modules-%'
            and name not like 'packages-%'
            and name not like 'accountsReceivable-%'
            and name not like 'reportsIncome-%'
            and name != 'branches-create'
            and name != 'terminals-create'
            and name != 'invoices-createComplement'
            and name != 'invoices-createCreditNote'
            and name != 'invoices-updateComplement'
            and name != 'invoices-updateCreditNote'
            or name = 'roles-viewAny'
            or name = 'roles-view'
            or name = 'roles-assignPermissions'
            ")
            ->pluck('name')
            ->toArray());
    }
}
