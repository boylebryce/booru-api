<?php

    define('WEB_ROOT', $_SERVER['DOCUMENT_ROOT']);
    define('INCLUDE_DIR', dirname(WEB_ROOT));
    define('CONFIG_DIR', INCLUDE_DIR . '/config');

    echo WEB_ROOT . '<br>';
    echo INCLUDE_DIR . '<br>';
    echo CONFIG_DIR . '<br>';

?>