<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BigmCustomSalesFields extends Migration
{
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/20260608000000_bigm_custom_sales_fields.sql');

        $this->db->table('app_config')
            ->where('key', 'receipt_template')
            ->update(['value' => 'receipt_bigm']);
    }

    public function down(): void
    {
        $this->forge->dropColumn('sales', [
            'walkin_name',
            'walkin_phone',
            'walkin_cnic',
            'pra_invoice_number',
            'fbr_invoice_number',
        ]);
    }
}
