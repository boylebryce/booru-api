<?php

    /*
        Takes an image ID and a string of space-separated tags
        to add to that image, and returns the image ID and path.
        Substrings enclosed in double quotes will be parsed as
        a single tag, allowing for tags with spaces.
    */

    require_once $_SERVER['DOCUMENT_ROOT'] . '/booru-api/include/config.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/booru-api/include/whitelist.php';

    $response = [
        'img_id'    => '',
        'img_path'  => '',
        'error'     => ''
    ];

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tags']) && isset($_POST['img_id'])) {
        // Validate image ID before doing any work
        $img_id = $_POST['img_id'];
        $db = new PDO(DSN, DB_USER, DB_PW);

        try {
            $query = 'SELECT COUNT(*) FROM `images` WHERE `img_id` = :img_id';
            $statement = $db->prepare($query);
            $statement->bindValue(':img_id', $img_id);
            $statement->execute();

            if ($statement->fetchColumn() == 0) {
                $response['error'] = 'Invalid request: img_id not found in database';
                echo json_encode($response);
                exit();
            }
        }
        catch (PDOException $e) {
            $response['error'] = $e->getMessage();
            echo json_encode($response);
            exit();
        }

        // Image ID is valid, can parse and validate tags
        $tokens = explode(' ', $_POST['tags']);
        $tag_labels = array();
        $quote_tag = '';

        foreach($tokens as $token) {
            if ($token !== '') {
                // Token is the start of a quote-enclosed tag 
                if ($quote_tag === '' && $token[0] === '"') {
                    $quote_tag .= $token;
                }
                // Token is an individual tag
                else if ($quote_tag === '') {
                    $tag_labels[] = $token;
                }
                // Token is part of a quote-enclosed tag
                else {
                    $quote_tag .= ' ' . $token;

                    // Token is the end of the quote-enclosed tag
                    if (substr($token, -1) === '"') {
                        $quote_tag = trim($quote_tag, '"');
                        $tag_labels[] = $quote_tag;
                        $quote_tag = '';
                    }
                }
            }
        }

        if ($quote_tag !== '') {
            $response['error'] = 'Tagging error: no closing quote for ' . $quote_tag;
            echo json_encode($response);
            exit();
        }

        $tag_id = '';

        // Tags are parsed and valid, get tag IDs
        foreach($tag_labels as $tag_label) {
            try {
                $query = 'SELECT `tag_id` FROM `tags` WHERE `tag_label` = :tag_label';
                $statement = $db->prepare($query);
                $statement->bindValue(':tag_label', $tag_label);
                $statement->execute();
                $tag_id = $statement->fetch(PDO::FETCH_ASSOC)['tag_id'];

                if (!$tag_id) {
                    $query = 'INSERT INTO `tags` (`tag_id`, `tag_label`, `tag_imgcount`) VALUES (NULL, :tag_label, 0)';
                    $statement = $db->prepare($query);
                    $statement->bindValue(':tag_label', $tag_label);
                    $statement->execute();
                    $tag_id = $db->lastInsertId();
                }

                // Check if image already has the tag
                $query = 'SELECT COUNT(*) FROM `imagetags` WHERE `img_id` = :img_id AND `tag_id` = :tag_id';
                $statement = $db->prepare($query);
                $statement->bindValue(':img_id', $img_id);
                $statement->bindValue(':tag_id', $tag_id);
                $statement->execute();

                // Image doesn't have this tag, add it
                if ($statement->fetchColumn() == 0) {
                    $query = 'INSERT INTO `imagetags` (`imagetags_id`, `img_id`, `tag_id`) VALUES (NULL, :img_id, :tag_id)';
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

    }
    else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tags'])) {
        $response['error'] = 'Invalid request: img_id is not set in the POST body';
    }
    else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['img_id'])) {
        $response['error'] = 'Invalid request: tags is not set in the POST body';
    }
    else {
        $response['error'] = 'Invalid request: Request method is not POST';
    }

    echo json_encode($response);

?>