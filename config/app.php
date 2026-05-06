<?php
return [
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => in_array(strtolower(getenv('APP_DEBUG') ?: 'false'), ['1', 'true', 'on'], true) ? true : false,
    'url' => getenv('APP_URL') ?: 'https://facturador.pccurico.cl',
    'timezone' => 'America/Santiago',
];
