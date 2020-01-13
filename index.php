<?php

    // contains paths to resources relative to web root
    require_once 'paths.php';

    // contains various settings, such as database information
    require_once $_SERVER['DOCUMENT_ROOT'] . '/booru-api/include/config.php';

    $readme = file_get_contents('readme.md');
    echo $readme ? $readme : 'Error: readme.md could not be read.';

?>