<?php

namespace Tests\Libraries;

use App\Libraries\Sale_lib;
use CodeIgniter\Test\CIUnitTestCase;

class Sale_libBillAdjustmentsTest extends CIUnitTestCase
{
    private Sale_lib $saleLib;

    protected function setUp(): void
    {
        parent::setUp();
        $this->saleLib = new Sale_lib();
        $this->saleLib->clear_all();
    }

    public function testCapBillDiscountReturnsZeroForEmptyCart(): void
    {
        $this->assertSame('0', $this->saleLib->cap_bill_discount('50'));
    }

    public function testCapServiceChargeReturnsZeroForEmptyCart(): void
    {
        $this->assertSame('0', $this->saleLib->cap_service_charge('100'));
    }

    public function testCapBillDiscountRejectsNegativeValues(): void
    {
        $this->assertSame('0', $this->saleLib->cap_bill_discount('-10'));
    }

    public function testCapServiceChargeRejectsNegativeValues(): void
    {
        $this->assertSame('0', $this->saleLib->cap_service_charge('-5'));
    }
}
