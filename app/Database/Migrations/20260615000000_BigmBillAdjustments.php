<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BigmBillAdjustments extends Migration
{
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/20260615000000_bigm_bill_adjustments.sql');
    }

    public function down(): void
    {
        $this->forge->dropColumn('sales', [
            'bill_discount',
            'service_charge',
        ]);
    }
}
