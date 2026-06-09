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
    /* BigM thermal print layout (FIT FP-1100 Raster). Give the body breathing room
       on the left/right and force a real gap between the item columns so Price and
       Quantity never collide on narrow paper. */
    #receipt_wrapper {
        box-sizing: border-box;
        padding-left: 10px;
        padding-right: 10px;
    }
    #receipt_wrapper #receipt_items th,
    #receipt_wrapper #receipt_items td {
        padding: 2px 6px;
    }
    /* Extra separation right where Price meets Quantity. */
    #receipt_wrapper #receipt_items th:nth-child(3),
    #receipt_wrapper #receipt_items td:nth-child(3) {
        padding-right: 10px;
    }
    #receipt_wrapper #receipt_items th:nth-child(4),
    #receipt_wrapper #receipt_items td:nth-child(4) {
        padding-left: 10px;
    }
    @media print {
        #receipt_wrapper {
            padding-left: 8px;
            padding-right: 8px;
        }
    }
</style>

<div id="receipt_wrapper" style="font-size: <?= $config['receipt_font_size'] ?>px;">
    <div id="receipt_header">
        <?php if ($config['company_logo'] != '') { ?>
            <div id="company_name" style="text-align:center;">
                <img id="image" src="<?= base_url('uploads/' . esc($config['company_logo'], 'url')) ?>" alt="company_logo" style="max-width:140px; max-height:70px; width:auto; height:auto;">
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

    <table id="receipt_items" style="table-layout:fixed; width:100%; word-break:break-word;">
        <tr>
            <th style="width:22%; text-align:left;"><?= lang('Sales.item_number') ?></th>
            <th style="width:30%; text-align:left;"><?= lang('Items.item') ?></th>
            <th style="width:18%; text-align:right; white-space:nowrap;"><?= lang('Sales.price') ?></th>
            <th style="width:12%; text-align:center;"><?= lang('Sales.quantity') ?></th>
            <th style="width:18%; text-align:right; white-space:nowrap;"><?= lang('Sales.total') ?></th>
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
                    <td style="word-break:break-word;"><?= esc($item['item_number'] ?? '') ?></td>
                    <td style="word-break:break-word;"><?= esc($item['name']) ?></td>
                    <td style="text-align:right; white-space:nowrap;"><?= to_currency($item['price']) ?></td>
                    <td style="text-align:center;"><?= to_quantity_decimals($item['quantity']) ?></td>
                    <td style="text-align:right; white-space:nowrap;"><?= to_currency($lineTotal) ?></td>
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
            <div id="qrcode-pra" style="width:100px; height:100px; max-width:100%; margin:0 auto;"
                 data-qr-text="<?= esc('PRA Invoice# ' . ($pra_invoice_number ?? ''), 'attr') ?>"></div>
            <div style="font-size:9pt; margin-top:6px; word-break:break-word;"><?= 'PRA Invoice# ' . esc($pra_invoice_number ?? '') ?></div>
        </div>
        <div style="display:table-cell; width:50%; text-align:center; vertical-align:top; padding:6px; page-break-inside:avoid;">
            <img src="<?= base_url('images/fbr-pos.png') ?>" alt="FBR Logo" style="display:block; height:45px; width:auto; max-width:100%; margin:0 auto 6px;">
            <div id="qrcode-fbr" style="width:100px; height:100px; max-width:100%; margin:0 auto;"
                 data-qr-text="<?= esc('FBR Invoice# ' . ($fbr_invoice_number ?? ''), 'attr') ?>"></div>
            <div style="font-size:9pt; margin-top:6px; word-break:break-word;"><?= 'FBR Invoice# ' . esc($fbr_invoice_number ?? '') ?></div>
        </div>
    </div>
</div>

<script src="<?= base_url('js/qrcode.min.js') ?>"></script>
<script type="text/javascript">
    $(document).ready(function() {
        var praEl = document.getElementById('qrcode-pra');
        var fbrEl = document.getElementById('qrcode-fbr');
        if (typeof QRCode !== 'undefined' && praEl && fbrEl) {
            new QRCode(praEl, { text: praEl.getAttribute('data-qr-text'), width: 100, height: 100 });
            new QRCode(fbrEl, { text: fbrEl.getAttribute('data-qr-text'), width: 100, height: 100 });
        }
    });
</script>
