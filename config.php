<?php

    // needs to be in web root directory to work

    define('WEB_ROOT', $_SERVER['DOCUMENT_ROOT']);
    define('INCLUDE_DIR', dirname(WEB_ROOT) . '/include/booru-api');
    define('CONFIG_DIR', INCLUDE_DIR . '/config');

?>