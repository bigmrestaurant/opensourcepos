<?php

namespace Tests\Libraries;

use App\Libraries\PraFbrService;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionMethod;

class PraFbrServiceTest extends CIUnitTestCase
{
    public function testBuildPayloadIncludesBillAdjustmentsInTotal(): void
    {
        $service = new PraFbrService();
        $method = new ReflectionMethod(PraFbrService::class, 'buildPayload');
        $method->setAccessible(true);

        $saleData = [
            'subtotal' => '2700.00',
            'service_charge' => '100.00',
            'bill_discount' => '32.00',
            'total' => '3200.00',
            'payments' => [
                ['payment_type' => lang('Sales.cash')],
            ],
            'cart' => [
                [
                    'item_number' => '1001',
                    'name' => 'Chicken Steak',
                    'price' => '1900.00',
                    'quantity' => '1',
                    'discount' => 0,
                ],
                [
                    'item_number' => '1002',
                    'name' => 'Burger',
                    'price' => '800.00',
                    'quantity' => '1',
                    'discount' => 0,
                ],
            ],
        ];

        $payload = $method->invoke($service, $saleData, '128963', 'BIGM-TEST');

        $itemsTotal = array_sum(array_column($payload['Items'], 'TotalAmount'));
        $expectedTotal = $itemsTotal + 100.00 - 32.00;

        $this->assertSame(32.0, $payload['Discount']);
        $this->assertEqualsWithDelta($expectedTotal, $payload['TotalBillAmount'], 0.01);
        $this->assertEqualsWithDelta(3200.00, $payload['TotalBillAmount'], 0.01);
    }
}
