<?php

    require_once $_SERVER['DOCUMENT_ROOT'] . '/booru-api/include/config.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/booru-api/include/whitelist.php';

    $response = ['error' => ''];

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['img_id'])) {
        // Validate image ID before doing any work
        $img_id = $_POST['img_id'];
        $db = new PDO(DSN, DB_USER, DB_PW);

        try {
            $query = 'SELECT * FROM `images` WHERE `img_id` = :img_id';
            $statement = $db->prepare($query);
            $statement->bindValue(':img_id', $img_id);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                $response['error'] = 'img_id not found in database';
                echo json_encode($response);
                exit();
            }

            $query = 'DELETE FROM `imagetags` WHERE  `img_id` = :img_id';
            $statement = $db->prepare($query);
            $statement->bindValue(':img_id', $img_id);
            $statement->execute();

            $query = 'DELETE FROM `images` WHERE `img_id` = :img_id';
            $statement = $db->prepare($query);
            $statement->bindValue(':img_id', $img_id);
            $statement->execute();

            unlink(UPLOAD_DIR . $result['img_path']);
        }
        catch (PDOException $e) {
            $response['error'] = $e->getMessage();
            echo json_encode($response);
            exit();
        }
    }
    else {
        $response['error'] = INVALID_REQUEST;
    }

    echo json_encode($response);

?>