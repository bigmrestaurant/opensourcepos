<?php

namespace App\Libraries;

/**
 * PRA / FBR fiscal invoice integration for BigM Restaurant.
 */
class PraFbrService
{
    private string $praUrl;
    private string $fbrUrl;
    private string $praPosId;
    private string $fbrPosId;
    private Sale_lib $sale_lib;

    public function __construct()
    {
        $this->praUrl = env('fiscal.pra.url', '');
        $this->fbrUrl = env('fiscal.fbr.url', '');
        $this->praPosId = env('fiscal.pra.posId', '128963');
        $this->fbrPosId = env('fiscal.fbr.posId', '186268');
        $this->sale_lib = new Sale_lib();
    }

    /**
     * Request PRA and FBR invoice numbers for a completed sale.
     *
     * @return array{pra_invoice_number: string, fbr_invoice_number: string}
     */
    public function requestInvoiceNumbers(array $saleData): array
    {
        // Seed with the (unique) sale id so two sales in the same second cannot collide.
        $saleId = (int) ($saleData['sale_id_num'] ?? 0);
        $usin = 'BIGM-' . date('YmdHis') . '-' . ($saleId > 0 ? $saleId : mt_rand(1000, 9999));

        return [
            'pra_invoice_number' => $this->requestInvoiceNumber($saleData, $this->praUrl, $this->praPosId, $usin, 'PRA'),
            'fbr_invoice_number' => $this->requestInvoiceNumber($saleData, $this->fbrUrl, $this->fbrPosId, $usin . '-FBR', 'FBR'),
        ];
    }

    /**
     * Request a single fiscal invoice number from the given endpoint.
     */
    private function requestInvoiceNumber(array $saleData, string $url, string $posId, string $usin, string $authority): string
    {
        if ($url === '') {
            log_message('warning', "{$authority} fiscal URL not configured in .env (fiscal." . strtolower($authority) . '.url)');

            return '';
        }

        $response = $this->callPostRequest($url, $this->buildPayload($saleData, $posId, $usin));
        $data = json_decode($response, true);

        return is_array($data) && isset($data['InvoiceNumber']) ? (string) $data['InvoiceNumber'] : '';
    }

    private function callPostRequest(string $url, array $data): string
    {
        $json = json_encode($data);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json),
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // Keep the register responsive: fail fast if the fiscal endpoint is slow/down.
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 8);
        $response = curl_exec($curl);

        if ($response === false) {
            log_message('error', 'PRA/FBR request failed: ' . curl_error($curl));
        }

        curl_close($curl);

        return $response === false ? '' : $response;
    }

    private function buildPayload(array $saleData, string $posId, string $usin): array
    {
        $payments = $saleData['payments'] ?? [];
        $isCash = $this->paymentIncludesCash($payments);
        $taxRate = $isCash ? 16 : 5;
        $serviceCharge = (float) ($saleData['service_charge'] ?? 0);
        $billDiscount = (float) ($saleData['bill_discount'] ?? 0);

        // Accumulate the bill-level tax from the same per-item rule used below
        // so the line items always reconcile with TotalTaxCharged in the payload.
        $totalTax = 0.0;
        $itemsTotal = 0.0;
        $items = [];
        foreach ($saleData['cart'] as $foodItem) {
            $lineSubtotal = ((float) $foodItem['price'] * (float) $foodItem['quantity']);
            if (!empty($foodItem['discount'])) {
                $lineSubtotal -= $lineSubtotal * ((float) $foodItem['discount'] / 100);
            }

            $taxCharged = $lineSubtotal * ($taxRate / 100);
            $totalTax += $taxCharged;
            $lineTotal = $lineSubtotal + $taxCharged;
            $itemsTotal += $lineTotal;
            $digits = preg_replace('/[^0-9]/', '', (string) ($foodItem['item_number'] ?? ''));
            $items[] = [
                'ItemCode'     => $foodItem['item_number'] ?? '',
                'ItemName'     => $foodItem['name'] ?? '',
                'PCTCode'      => str_pad(substr($digits, 0, 8), 8, '0', STR_PAD_RIGHT),
                'Quantity'     => $foodItem['quantity'],
                'TaxRate'      => $taxRate,
                'SaleValue'    => $foodItem['price'],
                'TaxCharged'   => $taxCharged,
                'TotalAmount'  => $lineTotal,
                'InvoiceType'  => 1,
            ];
        }

        // Bill-level service charge and discount are not line items; fold them into the
        // fiscal total so TotalBillAmount matches the POS sale total.
        $totalBillAmount = $itemsTotal + $serviceCharge - $billDiscount;

        return [
            'InvoiceNumber'   => '',
            'POSID'           => $posId,
            'USIN'            => $usin,
            'DateTime'        => date('Y-m-d\TH:i:s'),
            'TotalBillAmount' => $totalBillAmount,
            'TotalQuantity'   => count($saleData['cart']),
            'TotalSaleValue'  => $saleData['subtotal'] ?? 0,
            'TotalTaxCharged' => $totalTax,
            'Discount'        => $billDiscount,
            'PaymentMode'     => $isCash ? 1 : 2,
            'InvoiceType'     => 1,
            'Items'           => $items,
        ];
    }

    private function paymentIncludesCash(array $payments): bool
    {
        $cashLabel = lang('Sales.cash');

        foreach ($payments as $payment) {
            $type = is_array($payment) ? ($payment['payment_type'] ?? '') : '';
            if ($type === $cashLabel || str_starts_with($type, $cashLabel . ':')) {
                return true;
            }
        }

        return false;
    }
}
