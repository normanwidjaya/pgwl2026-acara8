<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema='public' ORDER BY table_name");
print_r($tables);
