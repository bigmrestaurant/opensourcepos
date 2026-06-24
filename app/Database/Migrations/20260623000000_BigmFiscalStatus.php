<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BigmFiscalStatus extends Migration
{
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/20260623000000_bigm_fiscal_status.sql');
    }

    public function down(): void
    {
        $this->forge->dropColumn('sales', [
            'pra_fiscal_status',
            'fbr_fiscal_status',
        ]);
    }
}
