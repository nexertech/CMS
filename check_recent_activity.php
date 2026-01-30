<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check for recently updated complaints
$complaints = \App\Models\Complaint::orderBy('updated_at', 'desc')->take(3)->get();

echo "--- Recent Complaints ---\n";
foreach ($complaints as $c) {
    echo "ID: " . $c->id . " | Status: " . $c->status . " | Updated: " . $c->updated_at . "\n";
    if ($c->house) {
        echo "  - House ID: " . $c->house->id . " | Name: " . $c->house->name . "\n";
        echo "  - FCM Token: " . ($c->house->fcm_token ? substr($c->house->fcm_token, 0, 10).'...' : 'NULL') . "\n";
    } else {
        echo "  - House: NULL (No notification will be sent)\n";
    }
    echo "--------------------------\n";
}
