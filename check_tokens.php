<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \App\Models\House::whereNotNull('fcm_token')->where('fcm_token', '!=', '')->count();
echo "Houses with non-empty FCM tokens: " . $count . "\n";

$sample = \App\Models\House::whereNotNull('fcm_token')->where('fcm_token', '!=', '')->first();
if ($sample) {
    echo "Sample House ID: " . $sample->id . "\n";
    echo "Sample Token (first 10 chars): " . substr($sample->fcm_token, 0, 10) . "...\n";
} else {
    echo "No samples found.\n";
}
