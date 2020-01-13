<?php

    /*
        Takes an image ID via GET and returns the image path, ID,
        and tags (tag ID and label), if the image exists.
    */

    require_once $_SERVER['DOCUMENT_ROOT'] . '/booru-api/include/config.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/booru-api/include/whitelist.php';

    if (!isset($_GET['img_id'])) {
        $response = ['error' => 'Invalid request: no image id'];
        echo json_encode($response);
        exit();
    }

    $response = array(
        'img_id'    => '',
        'img_path'  => '',
        'tags'      => array() // tag ID => tag label
    );

    $db = new PDO(DSN, DB_USER, DB_PW);
    try {
        // Validate image ID
        $query = 'SELECT COUNT(*) FROM `images` WHERE `img_id` = :img_id';
        $statement = $db->prepare($query);
        $statement->bindValue(':img_id', $_GET['img_id']);
        $statement->execute();
        
        if (!$statement->fetchColumn()) {
            $response = ['error' => 'Invalid request: image ID ' . $_GET['img_id'] . ' does not exist in the database'];
            echo json_encode($response);
            exit();
        }

        $query = 'SELECT * FROM `images` WHERE `img_id` = :img_id';
        $statement = $db->prepare($query);
        $statement->bindValue(':img_id', $_GET['img_id']);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        $response['img_id'] = $result['img_id'];
        $response['img_path'] = $result['img_path'];

        // Get tag IDs associated with image ID
        $query = 'SELECT `tag_id` FROM `imagetags` WHERE `img_id` = :img_id';
        $statement = $db->prepare($query);
        $statement->bindValue(':img_id', $response['img_id']);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Get tag label for each tag ID
        foreach($result as $tag_id) {
            $query = 'SELECT `tag_label` FROM `tags` WHERE `tag_id` = :tag_id';
            $statement = $db->prepare($query);
            $statement->bindValue(':tag_id', $tag_id['tag_id']);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            $response['tags'][$tag_id['tag_id']] = $result['tag_label']; 
        }
    }
    catch (PDOException $e) {
        $response = ['error' => $e->getMessage()];
        echo json_encode($response);
        exit();
    }

    echo json_encode($response);

?>