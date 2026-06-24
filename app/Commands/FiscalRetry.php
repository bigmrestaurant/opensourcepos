<?php

namespace App\Commands;

use App\Libraries\PraFbrService;
use App\Models\Sale;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Spark command: fiscal:retry
 *
 * Retries PRA and/or FBR invoice number requests for any sale that has a
 * failed fiscal status (pra_fiscal_status = 0 or fbr_fiscal_status = 0).
 * Only the failed authority is contacted — the one that already succeeded is
 * left untouched.
 *
 * Run manually:  php spark fiscal:retry
 * Scheduled via: app/Config/Tasks.php (every 30 minutes)
 */
class FiscalRetry extends BaseCommand
{
    protected $group       = 'Tasks';
    protected $name        = 'fiscal:retry';
    protected $description = 'Retry failed PRA/FBR fiscal invoice number requests.';

    public function run(array $params): void
    {
        $saleModel = new Sale();
        $pending   = $saleModel->get_sales_pending_fiscal_retry();

        if (empty($pending)) {
            CLI::write('[fiscal:retry] No pending fiscal retries.', 'green');

            return;
        }

        CLI::write('[fiscal:retry] Found ' . count($pending) . ' sale(s) pending retry.', 'yellow');

        $service           = new PraFbrService();
        $fullyResolved     = 0;
        $partiallyResolved = 0;
        $stillFailing      = 0;

        foreach ($pending as $saleData) {
            $saleId   = (int) $saleData['sale_id_num'];
            $retryPra = ($saleData['pra_fiscal_status'] === 0);
            $retryFbr = ($saleData['fbr_fiscal_status'] === 0);

            $result = $service->retryInvoiceNumbers($saleData, $retryPra, $retryFbr);

            $update      = [];
            $praResolved = ! $retryPra; // already OK if we didn't need to retry it
            $fbrResolved = ! $retryFbr;

            if ($retryPra) {
                if ($result['pra_invoice_number'] !== '') {
                    $update['pra_invoice_number'] = $result['pra_invoice_number'];
                    $update['pra_fiscal_status']  = 1;
                    $praResolved                  = true;
                    CLI::write("  Sale #{$saleId}: PRA OK → {$result['pra_invoice_number']}", 'green');
                } else {
                    log_message('warning', "fiscal:retry - PRA still failing for sale #{$saleId}");
                    CLI::write("  Sale #{$saleId}: PRA still failing.", 'red');
                }
            }

            if ($retryFbr) {
                if ($result['fbr_invoice_number'] !== '') {
                    $update['fbr_invoice_number'] = $result['fbr_invoice_number'];
                    $update['fbr_fiscal_status']  = 1;
                    $fbrResolved                  = true;
                    CLI::write("  Sale #{$saleId}: FBR OK → {$result['fbr_invoice_number']}", 'green');
                } else {
                    log_message('warning', "fiscal:retry - FBR still failing for sale #{$saleId}");
                    CLI::write("  Sale #{$saleId}: FBR still failing.", 'red');
                }
            }

            if (! empty($update)) {
                $saleModel->update($saleId, $update);
            }

            if ($praResolved && $fbrResolved) {
                $fullyResolved++;
            } elseif ($praResolved || $fbrResolved) {
                $partiallyResolved++;
            } else {
                $stillFailing++;
            }
        }

        CLI::write(
            "[fiscal:retry] Done. Fully resolved: {$fullyResolved}, " .
            "partially resolved: {$partiallyResolved}, " .
            "still failing: {$stillFailing}.",
            'cyan',
        );
    }
}
