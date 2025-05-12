<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security‑Headers Inspector options
    |--------------------------------------------------------------------------
    |
    | If you need to call servers with self‑signed certificates in dev,
    | set INSPECTOR_VERIFY_SSL=false in your .env.  Leave it true for
    | production so you don’t silently ignore MITM risk.
    |
    */

    'verify_ssl' => env('INSPECTOR_VERIFY_SSL', true),

];
