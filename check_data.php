<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Check Spares table issued_quantity
$sparesWithIssued = \App\Models\Spare::where('issued_quantity', '>', 0)->count();
echo "Spares with Issued Quantity > 0: " . $sparesWithIssued . "\n";

$totalIssued = \App\Models\Spare::sum('issued_quantity');
echo "Total Issued Quantity in Spares Table: " . $totalIssued . "\n";

// Check Stock Logs
$outLogsCount = \App\Models\SpareStockLog::where('change_type', 'out')->count();
echo "Stock Logs (Out) Count: " . $outLogsCount . "\n";

$outLogsSum = \App\Models\SpareStockLog::where('change_type', 'out')->sum('quantity');
echo "Stock Logs (Out) Sum: " . $outLogsSum . "\n";

// Show a sample log
$sampleLog = \App\Models\SpareStockLog::where('change_type', 'out')->first();
if ($sampleLog) {
    echo "Sample Log: " . json_encode($sampleLog->toArray()) . "\n";
}
