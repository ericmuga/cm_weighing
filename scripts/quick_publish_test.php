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

    $weights = [
        [
            'entry_id' => 1,
            'product_code' => 'BG1054',
            'product_name' => 'Hide (cow)',
            'bc_code' => 'BJ31100378',
            'invoice_weight' => 10.0,
            'unit_price' => 68.97,
            'line_amount' => 689.7,
            'created_at' => '2026-01-02 10:23:29.1766667',
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
            if (!isset($payload['Date'])) {
                echo "FAIL: Date key missing\n";
                return false;
            }
            if ($payload['Date'] !== '2026-01-02') {
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
        echo "PASS: Date formatted correctly as Y-m-d\n";
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

runTest();
