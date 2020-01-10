<?php

    /*
        Takes an image file via POST, adds it to the booru
        database, and returns the image ID and path as JSON
    */

    require_once $_SERVER['DOCUMENT_ROOT'] . '/booru-api/paths.php';
    require_once CONFIG_DIR . '/config.php';
    require_once CONFIG_DIR . '/whitelist.php';

    $response = array(
        'img_id'    => '',
        'img_path'  => '',
        'error'     => '');

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['userfile'])) {
        $valid_extensions = array('png', 'jpg', 'jpeg');
        $upload_extension = explode('/', $_FILES['userfile']['type'])[1];
        if (!in_array($upload_extension, $valid_extensions)) {
            $response['error'] = 'Invalid extension "' . $upload_extension . '"';
            echo json_encode($response);
            exit();
        }

        $file_name = random_filename(16, UPLOAD_DIR, $upload_extension);
        $file_path = UPLOAD_DIR . $file_name;

        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $file_path)) {
            try {
                $db = new PDO(DSN, DB_USER, DB_PW);
                $query = 'INSERT INTO `images` (`img_id`, `img_path`, `img_tagcount`) VALUES (NULL, :img_path, 0);';
                $statement = $db->prepare($query);
                $statement->bindValue('img_path', $file_name);
                $statement->execute();
                $img_id = $db->lastInsertId();
                $statement->closeCursor();

                $response['img_id'] = $img_id;
                $response['img_path'] = $file_name;
            }
            catch (PDOExceptoin $e) {
                $response['error'] = $e->getMessage();
            }
        }
        else {
            $response['error'] = 'Error moving file from temporary to upload directory';
        }
    }
    else {
        $response['error'] = 'Invalid request';
    }

    echo json_encode($response);

    function random_filename($length, $directory = '', $extension = '') {
        $dir = $directory;
    
        do {
            $key = '';
            $keys = array_merge(range(0, 9), range('a', 'z'));
    
            for ($i = 0; $i < $length; $i++) {
                $key .= $keys[array_rand($keys)];
            }
        } while (file_exists($dir . '/' . $key . (!empty($extension) ? '.' . $extension : '')));
    
        return $key . (!empty($extension) ? '.' . $extension : '');
    }


?>