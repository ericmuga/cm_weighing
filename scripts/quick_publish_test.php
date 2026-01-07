<?php

use App\Http\Controllers\SlaughterController;
use Illuminate\Support\Facades\DB;
use Mockery;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function runTest() {
    $controller = new SlaughterController();

    // Two lines with same bc_code should be grouped into a single insert
    $weights = [
        [
            'entry_id' => 1,
            'product_code' => 'BG1060',
            'product_name' => 'Example Offal',
            'bc_code' => 'BJ31100262',
            'invoice_weight' => 10.82,
            'unit_price' => 120.00,
            'line_amount' => round(10.82 * 120.00, 2),
            'created_at' => '2026-01-02 10:23:29.1766667',
        ],
        [
            'entry_id' => 2,
            'product_code' => 'BG1060',
            'product_name' => 'Example Offal',
            'bc_code' => 'BJ31100262',
            'invoice_weight' => 27.40,
            'unit_price' => 120.00,
            'line_amount' => round(27.40 * 120.00, 2),
            'created_at' => '2026-01-02 11:00:00.0000000',
        ],
    ];

    $data = [
        'extdocno' => 'IV-TEST-20260102',
        'customer_name' => 'Dummy Customer',
        'customer_code' => 'DUMMY',
        'weights' => $weights,
    ];

    $mockConnection = Mockery::mock();
    $mockTable = Mockery::mock();

    DB::shouldReceive('connection')
        ->with('bc240')
        ->andReturn($mockConnection);

    $mockConnection->shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($closure) {
            $closure();
            return null;
        });

    $mockConnection->shouldReceive('table')
        ->with(Mockery::type('string'))
        ->andReturn($mockTable);

    $mockTable->shouldReceive('insert')
        ->once()
        ->with(Mockery::on(function ($payload) {
            // Expect single grouped insert
            $expectedBcCode = 'BJ31100262';
            $expectedQty    = round(10.82 + 27.40, 2);
            $expectedAmount = round((10.82 * 120.00) + (27.40 * 120.00), 2);

            if (($payload['ItemNo'] ?? null) !== $expectedBcCode) {
                echo "FAIL: ItemNo expected '{$expectedBcCode}', got '{$payload['ItemNo']}'\n";
                return false;
            }
            if (abs(($payload['Qty'] ?? 0) - $expectedQty) > 0.0001) {
                echo "FAIL: Qty expected '{$expectedQty}', got '{$payload['Qty']}'\n";
                return false;
            }
            if (abs(($payload['LineAmount'] ?? 0) - $expectedAmount) > 0.0001) {
                echo "FAIL: LineAmount expected '{$expectedAmount}', got '{$payload['LineAmount']}'\n";
                return false;
            }
            // Date should be derived from first occurrence
            if (($payload['Date'] ?? null) !== '2026-01-02') {
                echo "FAIL: Date expected '2026-01-02', got '{$payload['Date']}'\n";
                return false;
            }
            return true;
        }))
        ->andReturn(true);

    $controllerRef = new \ReflectionClass(SlaughterController::class);
    $method = $controllerRef->getMethod('writeToDb');
    $method->setAccessible(true);

    try {
        $method->invoke($controller, $data);
        echo "PASS: Grouped insert with summed Qty and LineAmount\n";
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

runTest();
