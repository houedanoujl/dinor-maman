<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Boot the framework via a dummy request
$bootReq = Illuminate\Http\Request::create('/', 'GET');
$kernel->handle($bootReq);

$user = \App\Models\User::first();
if (!$user) { echo "no user\n"; exit; }
\Illuminate\Support\Facades\Auth::login($user);

foreach (['/admin', '/admin/contest-settings', '/admin/participants'] as $path) {
    try {
        $req = Illuminate\Http\Request::create($path, 'GET');
        $req->setUserResolver(fn() => $user);
        $resp = $kernel->handle($req);
        $code = $resp->getStatusCode();
        echo "$path => $code".PHP_EOL;
        if ($code >= 500) echo substr(strip_tags($resp->getContent()), 0, 800).PHP_EOL;
    } catch (\Throwable $e) {
        echo "$path => ERR ".$e->getMessage().' @ '.$e->getFile().':'.$e->getLine().PHP_EOL;
    }
}
