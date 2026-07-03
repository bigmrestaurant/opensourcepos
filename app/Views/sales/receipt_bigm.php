<?php
/**
 * BigM Restaurant receipt template with PRA/FBR fiscal sections.
 *
 * @var string $transaction_time
 * @var string $sale_id
 * @var string $employee
 * @var array  $cart
 * @var float  $subtotal
 * @var array  $taxes
 * @var float  $total
 * @var float  $service_charge
 * @var float  $bill_discount
 * @var array  $payments
 * @var float  $amount_change
 * @var array  $config
 * @var string $walkin_name
 * @var string $walkin_phone
 * @var string $walkin_cnic
 * @var string $pra_invoice_number
 * @var string $fbr_invoice_number
 */
$receiptFontSize = max(1, (int) ($config['receipt_font_size'] ?? 12));
?>

<style type="text/css">
    /* BigM thermal print layout (FIT FP-1100 Raster). Sides keep item columns separated. */
    #receipt_wrapper {
        box-sizing: border-box;
        padding-left: 10px;
        padding-right: 10px;
        padding-bottom: 10px;
    }
    #receipt_wrapper #receipt_header {
        padding-top: 0;
    }
    #receipt_wrapper .company_logo img {
        display: block;
        margin: 0 auto;
        max-width: 140px;
        max-height: 70px;
        width: auto;
        height: auto;
    }
    #receipt_wrapper #receipt_items th,
    #receipt_wrapper #receipt_items td {
        padding: 2px 6px;
    }
    /* Extra separation right where Price meets Qty. */
    #receipt_wrapper #receipt_items th:nth-child(2),
    #receipt_wrapper #receipt_items td:nth-child(2) {
        padding-right: 6px;
    }
    #receipt_wrapper #receipt_items th:nth-child(3),
    #receipt_wrapper #receipt_items td:nth-child(3) {
        padding-left: 4px;
    }
    /* Keep numeric columns on one line (e.g. Qty 99, Rs 9,999.00). */
    #receipt_wrapper #receipt_items th:nth-child(2),
    #receipt_wrapper #receipt_items td:nth-child(2),
    #receipt_wrapper #receipt_items th:nth-child(3),
    #receipt_wrapper #receipt_items td:nth-child(3),
    #receipt_wrapper #receipt_items th:nth-child(4),
    #receipt_wrapper #receipt_items td:nth-child(4) {
        white-space: nowrap;
    }
    .receipt-bottom-spacer {
        height: 0;
        overflow: hidden;
    }
    @media print {
        /* No size: 80mm here — the OS paper size (set to 80mm roll in the printer
           driver) already controls page width. Setting size:80mm in CSS activates
           Bootstrap's xs-breakpoint at 302px which triggers .row { margin:0 -15px }
           and pushes content 15px off BOTH edges, causing the left/right clipping.
           margin:0 lets the printer driver own the hardware margin. */
        @page {
            margin: 0;
        }

        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Bootstrap .container has padding:0 15px and .row has margin:0 -15px.
           These must both be zeroed for the full page width to be usable.
           :has() is unreliable in print engines, so reset all containers/rows. */
        .container,
        .container-fluid,
        .row {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        #receipt_wrapper {
            box-sizing: border-box;
            width: 100% !important;
            max-width: 100% !important;
            padding-left: 15px !important;
            padding-right: 35px !important;
        }

        #receipt_wrapper #receipt_header {
            padding-top: 0;
            margin-top: 0;
        }

        #receipt_wrapper #receipt_general_info {
            padding-left: 0;
            padding-right: 0;
        }

        #receipt_wrapper #receipt_items {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0;
            margin-right: 0;
        }

        #receipt_wrapper #receipt_items th,
        #receipt_wrapper #receipt_items td {
            padding: 1px 2px !important;
        }

        #receipt_wrapper #receipt_items th:nth-child(1),
        #receipt_wrapper #receipt_items td:nth-child(1) {
            width: 30% !important;
        }

        #receipt_wrapper #receipt_items th:nth-child(2),
        #receipt_wrapper #receipt_items td:nth-child(2) {
            /* 26% gives ~64px at 247px content width — enough for "Rs 1,300.00" */
            width: 26% !important;
            padding-right: 2px !important;
        }

        #receipt_wrapper #receipt_items th:nth-child(3),
        #receipt_wrapper #receipt_items td:nth-child(3) {
            width: 10% !important;
            padding-left: 2px !important;
        }

        #receipt_wrapper #receipt_items th:nth-child(4),
        #receipt_wrapper #receipt_items td:nth-child(4) {
            /* 34% gives ~84px — ample for "Rs 3,580.00" */
            width: 34% !important;
            padding-right: 0 !important;
        }

        .receipt-bottom-spacer {
            height: 30px !important;
            overflow: visible !important;
        }
    }
</style>

<div id="receipt_wrapper" style="font-size: <?= $receiptFontSize ?>px;">
    <div id="receipt_header">
        <?php if (($config['company_logo'] ?? '') !== '') { ?>
            <div class="company_logo" style="text-align:center;">
                <img src="<?= base_url('uploads/' . esc($config['company_logo'], 'url')) ?>" alt="company_logo">
            </div>
        <?php } ?>

        <?php if ($config['receipt_show_company_name'] ?? false) { ?>
            <div id="company_name"><?= nl2br(esc($config['company'] ?? '')) ?></div>
        <?php } ?>

        <div id="company_address"><?= nl2br(esc($config['address'] ?? '')) ?></div>
        <div id="company_phone"><?= esc($config['phone'] ?? '') ?></div>
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
        <div id="sale_id"><?= lang('Sales.id') . esc(": {$sale_id}") ?></div>
        <div id="employee"><?= lang('Employees.employee') . esc(": {$employee}") ?></div>
    </div>

    <table id="receipt_items" style="table-layout:fixed; width:100%; word-break:break-word;">
        <thead>
        <tr>
            <th style="width:30%; text-align:left;"><?= lang('Items.item') ?></th>
            <th style="width:22%; text-align:right;"><?= lang('Sales.price') ?></th>
            <th style="width:11%; text-align:center;">Qty</th>
            <th style="width:37%; text-align:right;"><?= lang('Sales.total') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($cart as $item) {
            if ((int) $item['print_option'] === PRINT_YES) {
                ?>
                <tr>
                    <td style="word-break:break-word;"><?= esc($item['name']) ?></td>
                    <td style="text-align:right;"><?= to_currency($item['price']) ?></td>
                    <td style="text-align:center;"><?= to_quantity_decimals($item['quantity']) ?></td>
                    <td style="text-align:right;"><?= to_currency($item['discounted_total']) ?></td>
                </tr>
        <?php
            }
        }
?>
        <tr>
            <td colspan="3" style="text-align:right; border-top:2px solid #000;"><?= lang('Sales.sub_total') ?></td>
            <td style="text-align:right; border-top:2px solid #000;"><?= to_currency($subtotal) ?></td>
        </tr>

        <?php foreach ($taxes as $tax) { ?>
            <tr>
                <td colspan="3" style="text-align:right;"><?= esc((float) ($tax['tax_rate'] ?? 0) . '% ' . ($tax['tax_group'] ?? 'GST')) ?></td>
                <td style="text-align:right;"><?= to_currency_tax($tax['sale_tax_amount'] ?? 0) ?></td>
            </tr>
        <?php } ?>

        <?php if ((float) ($service_charge ?? 0) != 0) { ?>
            <tr>
                <td colspan="3" style="text-align:right;"><?= lang('Sales.service_charge') ?></td>
                <td style="text-align:right;"><?= to_currency($service_charge) ?></td>
            </tr>
        <?php } ?>

        <?php if ((float) ($bill_discount ?? 0) != 0) { ?>
            <tr>
                <td colspan="3" style="text-align:right;"><?= lang('Sales.bill_discount') ?></td>
                <td style="text-align:right;"><?= to_currency($bill_discount * -1) ?></td>
            </tr>
        <?php } ?>

        <tr>
            <td colspan="3" style="text-align:right;"><?= lang('Sales.total') ?></td>
            <td style="text-align:right;"><?= to_currency($total) ?></td>
        </tr>

        <?php if (count($payments) === 1) {
            $splitpayment = explode(':', $payments[0]['payment_type']);
            ?>
            <tr>
                <td colspan="3" style="text-align:right;"><?= lang('Sales.payment') ?></td>
                <td style="text-align:right;"><?= esc($splitpayment[0]) ?></td>
            </tr>
        <?php } else {
            foreach ($payments as $payment) {
                $splitpayment = explode(':', $payment['payment_type']);
                ?>
                <tr>
                    <td colspan="3" style="text-align:right;"><?= lang('Sales.payment') . ' (' . esc($splitpayment[0]) . ')' ?></td>
                    <td style="text-align:right;"><?= to_currency($payment['payment_amount'] ?? 0) ?></td>
                </tr>
            <?php }
            } ?>

        <?php if ((float) ($amount_change ?? 0) > 0) { ?>
            <tr>
                <td colspan="3" style="text-align:right;"><?= lang('Sales.change_due') ?></td>
                <td style="text-align:right;"><?= to_currency($amount_change) ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <div id="sale_return_policy">
        <?= nl2br(esc($config['return_policy'] ?? '')) ?>
    </div>

    <?php
    $hasPra = ($pra_invoice_number ?? '') !== '';
$hasFbr     = ($fbr_invoice_number ?? '') !== '';
if ($hasPra || $hasFbr) { ?>
    <div id="barcode" style="display:table; width:100%; table-layout:fixed; margin:10px auto 0; page-break-inside:avoid;">
        <?php if ($hasPra) { ?>
        <div style="display:table-cell; width:<?= $hasFbr ? '50%' : '100%' ?>; text-align:center; vertical-align:top; padding:6px; page-break-inside:avoid;">
            <img src="<?= base_url('images/pra-pos.jpeg') ?>" alt="PRA Logo" style="display:block; height:45px; width:auto; max-width:100%; margin:0 auto 6px;">
            <div id="qrcode-pra" style="width:100px; height:100px; max-width:100%; margin:0 auto;"
                 data-qr-text="<?= esc('PRA Invoice# ' . $pra_invoice_number, 'attr') ?>"></div>
            <div style="font-size:9pt; margin-top:6px; word-break:break-word;"><?= 'PRA Invoice# ' . esc($pra_invoice_number) ?></div>
        </div>
        <?php } ?>
        <?php if ($hasFbr) { ?>
        <div style="display:table-cell; width:<?= $hasPra ? '50%' : '100%' ?>; text-align:center; vertical-align:top; padding:6px; page-break-inside:avoid;">
            <img src="<?= base_url('images/fbr-pos.png') ?>" alt="FBR Logo" style="display:block; height:45px; width:auto; max-width:100%; margin:0 auto 6px;">
            <div id="qrcode-fbr" style="width:100px; height:100px; max-width:100%; margin:0 auto;"
                 data-qr-text="<?= esc('FBR Invoice# ' . $fbr_invoice_number, 'attr') ?>"></div>
            <div style="font-size:9pt; margin-top:6px; word-break:break-word;"><?= 'FBR Invoice# ' . esc($fbr_invoice_number) ?></div>
        </div>
        <?php } ?>
    </div>
    <?php } ?>
    <div class="receipt-bottom-spacer"></div>
</div>

<?php if ($hasPra || $hasFbr) { ?>
<script src="<?= base_url('js/qrcode.min.js') ?>"></script>
<script type="text/javascript">
    $(document).ready(function() {
        if (typeof QRCode === 'undefined') { return; }
        ['qrcode-pra', 'qrcode-fbr'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                new QRCode(el, { text: el.getAttribute('data-qr-text'), width: 100, height: 100 });
            }
        });
    });
</script>
<?php } ?>
