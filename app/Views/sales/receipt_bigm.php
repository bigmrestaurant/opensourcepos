<?php
/**
 * BigM Restaurant receipt template with PRA/FBR fiscal sections.
 *
 * @var string $transaction_time
 * @var string $sale_id
 * @var string $employee
 * @var array $cart
 * @var float $subtotal
 * @var array $taxes
 * @var float $total
 * @var float $service_charge
 * @var float $bill_discount
 * @var array $payments
 * @var float $amount_change
 * @var array $config
 * @var string $walkin_name
 * @var string $walkin_phone
 * @var string $walkin_cnic
 * @var string $pra_invoice_number
 * @var string $fbr_invoice_number
 */
?>

<style type="text/css">
    <?php $printFontSize = max(11, (int) ($config['receipt_font_size'] ?? 12)); ?>
    /* BigM thermal receipt (FIT FP-1100 / 80mm). Balance readable size vs edge clipping. */
    #receipt_wrapper {
        box-sizing: border-box;
        width: 100%;
        max-width: 80mm;
        margin: 0 auto;
        padding-left: 2mm;
        padding-right: 2mm;
    }

    #receipt_wrapper #receipt_header {
        padding-top: 0;
        text-align: center;
    }

    #receipt_wrapper #company_phone {
        margin-bottom: 8px;
    }

    /* Override global receipt.css (150%) so the header fits 80mm paper. */
    #receipt_wrapper #company_name {
        font-size: 1.2em;
        font-weight: bold;
        line-height: 1.25;
    }

    #receipt_wrapper .company_logo img {
        display: block;
        margin: 0 auto;
        max-width: 130px;
        max-height: 62px;
        width: auto;
        height: auto;
    }

    #receipt_wrapper #receipt_general_info,
    #receipt_wrapper #receipt_header,
    #receipt_wrapper #sale_return_policy {
        text-align: center;
    }

    #receipt_wrapper #receipt_general_info {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    #receipt_wrapper #receipt_items {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        margin-top: 10px;
        margin-bottom: 10px;
    }

    #receipt_wrapper #receipt_items th,
    #receipt_wrapper #receipt_items td {
        padding: 2px 1px;
        vertical-align: top;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    #receipt_wrapper #qrcode-pra canvas,
    #receipt_wrapper #qrcode-fbr canvas,
    #receipt_wrapper #qrcode-pra img,
    #receipt_wrapper #qrcode-fbr img {
        display: block;
        margin: 0 auto;
    }

    @media print {
        @page {
            size: 80mm auto;
            margin: 0;
        }

        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Cancel global ospos_print.css 75% shrink on receipts. */
        #receipt_wrapper {
            width: 100% !important;
            max-width: 80mm !important;
            padding-left: 2mm !important;
            padding-right: 2mm !important;
            margin: 0 auto !important;
            font-size: <?= $printFontSize ?>px !important;
        }

        #receipt_wrapper #receipt_header {
            padding-top: 0;
            margin-top: 0;
        }

        #receipt_wrapper #company_phone {
            margin-bottom: 4px;
        }

        #receipt_wrapper #company_name {
            font-size: 1.15em;
            line-height: 1.25;
        }

        #receipt_wrapper #receipt_items {
            margin-top: 6px;
            margin-bottom: 6px;
        }

        #receipt_wrapper #receipt_items th,
        #receipt_wrapper #receipt_items td {
            padding: 2px 1px;
            font-size: inherit;
            line-height: 1.25;
        }

        #receipt_wrapper .company_logo img {
            max-width: 115px;
            max-height: 55px;
        }

        #receipt_wrapper #barcode {
            margin-top: 8px;
        }

        #receipt_wrapper #barcode > div {
            padding: 3px !important;
        }

        #receipt_wrapper #barcode .fiscal-invoice-label {
            font-size: 8px;
            line-height: 1.2;
            margin-top: 4px;
            word-break: break-word;
        }

        #receipt_wrapper #qrcode-pra,
        #receipt_wrapper #qrcode-fbr {
            width: 115px !important;
            height: 115px !important;
        }

        #receipt_wrapper #qrcode-pra canvas,
        #receipt_wrapper #qrcode-fbr canvas,
        #receipt_wrapper #qrcode-pra img,
        #receipt_wrapper #qrcode-fbr img {
            width: 115px !important;
            height: 115px !important;
        }

        #receipt_wrapper #barcode img {
            height: 40px !important;
        }
    }
</style>

<div id="receipt_wrapper" style="font-size: <?= $config['receipt_font_size'] ?>px;">
    <div id="receipt_header">
        <?php if ($config['company_logo'] != '') { ?>
            <div class="company_logo" style="text-align:center;">
                <img src="<?= base_url('uploads/' . esc($config['company_logo'], 'url')) ?>" alt="company_logo">
            </div>
        <?php } ?>

        <?php if ($config['receipt_show_company_name']) { ?>
            <div id="company_name"><?= nl2br(esc($config['company'])) ?></div>
        <?php } ?>

        <div id="company_address"><?= nl2br(esc($config['address'])) ?></div>
        <div id="company_phone"><?= esc($config['phone']) ?></div>
        <div id="sale_receipt"><?= lang('Sales.receipt') ?></div>
        <div id="sale_time"><?= esc($transaction_time) ?></div>
    </div>

    <div id="receipt_general_info">
        <?php if (($walkin_name ?? '') !== '') { ?>
            <div id="customerName"><?= 'Customer: ' . esc($walkin_name) ?></div>
        <?php } ?>
        <?php if (($walkin_phone ?? '') !== '') { ?>
            <div id="customerPhone"><?= 'Customer Phone# ' . esc($walkin_phone) ?></div>
        <?php } ?>
        <?php if (($walkin_cnic ?? '') !== '') { ?>
            <div id="customerCnic"><?= 'Customer CNIC: ' . esc($walkin_cnic) ?></div>
        <?php } ?>
        <div id="sale_id"><?= lang('Sales.id') . esc(": $sale_id") ?></div>
        <div id="employee"><?= lang('Employees.employee') . esc(": $employee") ?></div>
    </div>

    <table id="receipt_items">
        <tr>
            <th style="width:18%; text-align:left;">#</th>
            <th style="width:25%; text-align:left;"><?= lang('Items.item') ?></th>
            <th style="width:17%; text-align:right;"><?= lang('Sales.price') ?></th>
            <th style="width:12%; text-align:center;">Qty</th>
            <th style="width:23%; text-align:right;"><?= lang('Sales.total') ?></th>
        </tr>
        <?php
        foreach ($cart as $item) {
            if ($item['print_option'] == PRINT_YES) {
                $lineTotal = (float) $item['price'] * (float) $item['quantity'];
                if (!empty($item['discount'])) {
                    $lineTotal -= $lineTotal * ((float) $item['discount'] / 100);
                }
        ?>
                <tr>
                    <td><?= esc($item['item_number'] ?? '') ?></td>
                    <td><?= esc($item['name']) ?></td>
                    <td style="text-align:right;"><?= to_currency($item['price']) ?></td>
                    <td style="text-align:center;"><?= to_quantity_decimals($item['quantity']) ?></td>
                    <td style="text-align:right;"><?= to_currency($lineTotal) ?></td>
                </tr>
        <?php
            }
        }
        ?>
        <tr>
            <td colspan="4" style="text-align:right; border-top:2px solid #000;"><?= lang('Sales.sub_total') ?></td>
            <td style="text-align:right; border-top:2px solid #000;"><?= to_currency($subtotal) ?></td>
        </tr>

        <?php foreach ($taxes as $tax) { ?>
            <tr>
                <td colspan="4" style="text-align:right;"><?= esc((float) ($tax['tax_rate'] ?? 0) . '% ' . ($tax['tax_group'] ?? 'GST')) ?></td>
                <td style="text-align:right;"><?= to_currency_tax($tax['sale_tax_amount'] ?? 0) ?></td>
            </tr>
        <?php } ?>

        <?php if ((float) ($service_charge ?? 0) != 0) { ?>
            <tr>
                <td colspan="4" style="text-align:right;"><?= lang('Sales.service_charge') ?></td>
                <td style="text-align:right;"><?= to_currency($service_charge) ?></td>
            </tr>
        <?php } ?>

        <?php if ((float) ($bill_discount ?? 0) != 0) { ?>
            <tr>
                <td colspan="4" style="text-align:right;"><?= lang('Sales.bill_discount') ?></td>
                <td style="text-align:right;"><?= to_currency($bill_discount * -1) ?></td>
            </tr>
        <?php } ?>

        <tr>
            <td colspan="4" style="text-align:right;"><?= lang('Sales.total') ?></td>
            <td style="text-align:right;"><?= to_currency($total) ?></td>
        </tr>

        <?php foreach ($payments as $payment) {
            $splitpayment = explode(':', $payment['payment_type']);
        ?>
            <tr>
                <td colspan="4" style="text-align:right;"><?= lang('Sales.payment') ?></td>
                <td style="text-align:right;"><?= esc($splitpayment[0]) ?></td>
            </tr>
        <?php } ?>
    </table>

    <div id="sale_return_policy">
        <?= nl2br(esc($config['return_policy'])) ?>
    </div>

    <div id="barcode" style="display:table; width:100%; table-layout:fixed; margin:10px auto 0; page-break-inside:avoid;">
        <div style="display:table-cell; width:50%; text-align:center; vertical-align:top; padding:6px; page-break-inside:avoid;">
            <img src="<?= base_url('images/pra-pos.jpeg') ?>" alt="PRA Logo" style="display:block; height:45px; width:auto; max-width:100%; margin:0 auto 6px;">
            <div id="qrcode-pra" class="fiscal-qrcode" style="width:115px; height:115px; max-width:100%; margin:0 auto;"
                 data-qr-text="<?= esc('PRA Invoice# ' . ($pra_invoice_number ?? ''), 'attr') ?>"></div>
            <div class="fiscal-invoice-label"><?= 'PRA Invoice# ' . esc($pra_invoice_number ?? '') ?></div>
        </div>
        <div style="display:table-cell; width:50%; text-align:center; vertical-align:top; padding:6px; page-break-inside:avoid;">
            <img src="<?= base_url('images/fbr-pos.png') ?>" alt="FBR Logo" style="display:block; height:45px; width:auto; max-width:100%; margin:0 auto 6px;">
            <div id="qrcode-fbr" class="fiscal-qrcode" style="width:115px; height:115px; max-width:100%; margin:0 auto;"
                 data-qr-text="<?= esc('FBR Invoice# ' . ($fbr_invoice_number ?? ''), 'attr') ?>"></div>
            <div class="fiscal-invoice-label"><?= 'FBR Invoice# ' . esc($fbr_invoice_number ?? '') ?></div>
        </div>
    </div>
</div>

<script src="<?= base_url('js/qrcode.min.js') ?>"></script>
<script type="text/javascript">
    $(document).ready(function() {
        var praEl = document.getElementById('qrcode-pra');
        var fbrEl = document.getElementById('qrcode-fbr');
        var qrSize = 115;
        if (typeof QRCode !== 'undefined' && praEl && fbrEl) {
            new QRCode(praEl, {
                text: praEl.getAttribute('data-qr-text'),
                width: qrSize,
                height: qrSize,
                correctLevel: QRCode.CorrectLevel.M
            });
            new QRCode(fbrEl, {
                text: fbrEl.getAttribute('data-qr-text'),
                width: qrSize,
                height: qrSize,
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    });
</script>
