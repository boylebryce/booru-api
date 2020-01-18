<?php

    require_once $_SERVER['DOCUMENT_ROOT'] . '/booru-api/include/config.php';

    $response = ['error' => ''];

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tag_ids']) && isset($_POST['img_id'])) {
        // Validate image ID before doing any work
        $img_id = $_POST['img_id'];
        $db = new PDO(DSN, DB_USER, DB_PW);

        try {
            $query = 'SELECT COUNT(*) FROM `images` WHERE `img_id` = :img_id';
            $statement = $db->prepare($query);
            $statement->bindValue(':img_id', $img_id);
            $statement->execute();

            if ($statement->fetchColumn() == 0) {
                $response['error'] = 'img_id not found in database';
                echo json_encode($response);
                exit();
            }

            foreach(explode(',', $_POST['tag_ids']) as $tag_id) {
                $query = 'DELETE FROM `imagetags` WHERE `img_id` = :img_id AND `tag_id` = :tag_id';
                $statement = $db->prepare($query);
                $statement->bindValue(':img_id', $img_id);
                $statement->bindValue(':tag_id', $tag_id);
                $statement->execute();
            }
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