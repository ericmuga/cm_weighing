<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\SlaughterController;
use Illuminate\Support\Facades\DB;
use Mockery;
use Carbon\Carbon;

class SlaughterPublishDateTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testWriteToDbFormatsDateAsYmd()
    {
        $controller = new SlaughterController();

        $weights = [
            [
                'entry_id' => 1,
                'product_code' => 'BG1054',
                'product_name' => 'Hide (cow)',
                'bc_code' => 'BJ31100378',
                'invoice_weight' => 10.0,
                'unit_price' => 68.97,
                'line_amount' => 689.7,
                // Simulate created_at as a string with datetime and fractional seconds
                'created_at' => '2026-01-02 10:23:29.1766667',
            ],
        ];

        $data = [
            'extdocno' => 'IV-TEST-20260102',
            'customer_name' => 'Dummy Customer',
            'customer_code' => 'DUMMY',
            'weights' => $weights,
        ];

        // Mock the bc240 connection and capture the insert payload
        $mockConnection = Mockery::mock();
        $mockTable = Mockery::mock();

        DB::shouldReceive('connection')
            ->with('bc240')
            ->andReturn($mockConnection);

        // transaction should execute the provided closure
        $mockConnection->shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($closure) {
                $closure();
                return null;
            });

        $mockConnection->shouldReceive('table')
            ->with(Mockery::type('string'))
            ->andReturn($mockTable);

        // Assert that the Date field is correctly formatted as Y-m-d
        $mockTable->shouldReceive('insert')
            ->once()
            ->with(Mockery::on(function ($payload) {
                return isset($payload['Date']) && $payload['Date'] === '2026-01-02';
            }))
            ->andReturn(true);

        // Execute
        $controllerRef = new \ReflectionClass(SlaughterController::class);
        $method = $controllerRef->getMethod('writeToDb');
        $method->setAccessible(true);
        $method->invoke($controller, $data);

        $this->assertTrue(true); // If we reach here, expectations were satisfied
    }
}
