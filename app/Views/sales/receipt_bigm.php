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
 * @var string $barcode
 * @var array $config
 * @var string $walkin_name
 * @var string $walkin_phone
 * @var string $walkin_cnic
 * @var string $pra_invoice_number
 * @var string $fbr_invoice_number
 */
?>

<div id="receipt_wrapper" style="font-size: <?= $config['receipt_font_size'] ?>px;">
    <div id="receipt_header">
        <?php if ($config['company_logo'] != '') { ?>
            <div id="company_name">
                <img id="image" src="<?= base_url('uploads/' . esc($config['company_logo'], 'url')) ?>" alt="company_logo">
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
        <div id="customerName"><?= 'Customer: ' . esc($walkin_name !== '' ? $walkin_name : '--------') ?></div>
        <div id="customerPhone"><?= 'Customer Phone# ' . esc($walkin_phone !== '' ? $walkin_phone : '--------') ?></div>
        <div id="customerCnic"><?= 'Customer CNIC: ' . esc($walkin_cnic !== '' ? $walkin_cnic : '--------') ?></div>
        <div id="sale_id"><?= lang('Sales.id') . esc(": $sale_id") ?></div>
        <div id="employee"><?= lang('Employees.employee') . esc(": $employee") ?></div>
    </div>

    <table id="receipt_items">
        <tr>
            <th style="width:20%; text-align:left;"><?= lang('Sales.item_number') ?></th>
            <th style="width:36%; text-align:left;"><?= lang('Items.item') ?></th>
            <th style="width:18%; text-align:right;"><?= lang('Sales.price') ?></th>
            <th style="width:8%; text-align:center;"><?= lang('Sales.quantity') ?></th>
            <th style="width:18%; text-align:right;"><?= lang('Sales.total') ?></th>
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

    <div id="barcode" style="display:table; width:100%; table-layout:fixed; margin:10px auto 0;">
        <div style="display:table-cell; width:50%; text-align:center; vertical-align:top; padding:10px;">
            <img src="<?= base_url('images/pra-pos.jpeg') ?>" alt="PRA Logo" style="display:block; height:55px; width:auto; margin:0 auto 8px;">
            <div id="qrcode-pra" style="width:120px; height:120px; margin:0 auto;"
                 data-qr-text="<?= esc('PRA Invoice# ' . ($pra_invoice_number ?? ''), 'attr') ?>"></div>
            <div style="font-size:10pt; margin-top:6px;"><?= 'PRA Invoice# ' . esc($pra_invoice_number ?? '') ?></div>
        </div>
        <div style="display:table-cell; width:50%; text-align:center; vertical-align:top; padding:10px;">
            <img src="<?= base_url('images/fbr-pos.png') ?>" alt="FBR Logo" style="display:block; height:55px; width:auto; margin:0 auto 8px;">
            <div id="qrcode-fbr" style="width:120px; height:120px; margin:0 auto;"
                 data-qr-text="<?= esc('FBR Invoice# ' . ($fbr_invoice_number ?? ''), 'attr') ?>"></div>
            <div style="font-size:10pt; margin-top:6px;"><?= 'FBR Invoice# ' . esc($fbr_invoice_number ?? '') ?></div>
        </div>
    </div>

    <div style="text-align:center; font-size:7.5pt; margin-top:10px;">Powered by DOStechnologies.com</div>

    <div id="barcode_footer">
        <?= $barcode ?><br>
        <?= esc($sale_id) ?>
    </div>
</div>

<script src="<?= base_url('js/qrcode.min.js') ?>"></script>
<script type="text/javascript">
    $(document).ready(function() {
        var praEl = document.getElementById('qrcode-pra');
        var fbrEl = document.getElementById('qrcode-fbr');
        if (typeof QRCode !== 'undefined' && praEl && fbrEl) {
            new QRCode(praEl, { text: praEl.getAttribute('data-qr-text'), width: 120, height: 120 });
            new QRCode(fbrEl, { text: fbrEl.getAttribute('data-qr-text'), width: 120, height: 120 });
        }
    });
</script>
