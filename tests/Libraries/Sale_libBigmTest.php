<?php

namespace Tests\Libraries;

use App\Libraries\Sale_lib;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Guards the BigM card-payment detection that switches GST from 16% to 5%.
 *
 * The downstream total math (get_card_total / apply_card_tax_details) reads the
 * customer model and OSPOS settings, so it belongs in a DB-backed integration
 * test; here we lock down the gate that decides whether that path runs at all.
 */
class Sale_libBigmTest extends CIUnitTestCase
{
    private Sale_lib $saleLib;

    protected function setUp(): void
    {
        parent::setUp();
        $this->saleLib = new Sale_lib();
    }

    public function testIsCardPaymentDetectsCreditAndDebit(): void
    {
        $this->assertTrue($this->saleLib->is_card_payment(lang('Sales.credit')));
        $this->assertTrue($this->saleLib->is_card_payment(lang('Sales.debit')));
    }

    public function testIsCardPaymentRejectsCash(): void
    {
        $this->assertFalse($this->saleLib->is_card_payment(lang('Sales.cash')));
    }

    public function testPaymentsIncludeCardDetectsCardAmongMixedPayments(): void
    {
        $payments = [
            ['payment_type' => lang('Sales.cash')],
            ['payment_type' => lang('Sales.credit')],
        ];

        $this->assertTrue($this->saleLib->payments_include_card($payments));
    }

    public function testPaymentsIncludeCardMatchesSuffixedCardTypes(): void
    {
        $payments = [
            ['payment_type' => lang('Sales.credit') . ':1234'],
        ];

        $this->assertTrue($this->saleLib->payments_include_card($payments));
    }

    public function testPaymentsIncludeCardReturnsFalseForCashOnly(): void
    {
        $payments = [
            ['payment_type' => lang('Sales.cash')],
        ];

        $this->assertFalse($this->saleLib->payments_include_card($payments));
    }
}
