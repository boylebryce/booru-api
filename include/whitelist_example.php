<?php

    /*
        When starting a new instance of this API, fill in any origins
        and hosts you want to allow and resave this as whitelist.php
    */

    $allowed_origins = [
        'https://example.com',
        'http://localhost'
    ];

    $allowed_hosts = [
        'localhost'
    ];

    // POST requests
    if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    }

    // GET requests
    else if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], $allowed_hosts)) {

    }
    else {
        echo json_encode(['error' => 'Whitelist error: unauthorized API request']);
        exit();
    }

?>