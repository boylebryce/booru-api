<?php

    /*
        When starting a new instance of this API, fill in the required
        credentials and resave this as config.php
    */

    // database credentials
    define('DSN',       '');
    define('DB_USER',   '');
    define('DB_PW',     '');

    // upload directory
    define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/booru/img/');

    // default error message
    define('INVALID_REQUEST', 'Invalid API call');
?>