<?php

    /*
        Takes a string of space-separated tags and returns
        an array of image ID/path pairs encoded in JSON.
    */

    // paths to resources relative to web root
    require_once 'paths.php';

    // various settings, such as database information
    require_once CONFIG_DIR . '/config.php';

    // external server whitelist for CORS
    require_once CONFIG_DIR . '/whitelist.php';

    if (isset($_POST)) {
        $json = json_decode(file_get_contents('php://input'));

        try {
            $db = new PDO(DSN, DB_USER, DB_PW);

            $tokens = explode(' ', $json->searchString);
            $search_tags = array();
            $quote_tag = '';

            foreach ($tokens as $token) {
                if ($token !== '') {
                    if ($quote_tag === '') {
                        if ($token[0] === '"') {
                            $quote_tag .= $token;
                        }
                        else {
                            $search_tags[] = $token;
                        }
                    }
                    else {
                        $quote_tag .= ' ' . $token;

                        if (substr($token, -1) === '"') {
                            $quote_tag = trim($quote_tag, '"');
                            $search_tags[] = $quote_tag;
                            $quote_tag = '';
                        }
                    }
                }
            }

            $tags = array(); // tag_id => tag_label
            $images = array(); // img_id => img_path
            $image_ids = array();

            // get tag IDs 
            foreach ($search_tags as $tag_label) {
                $query = 'SELECT `tag_id` FROM `tags` WHERE `tag_label` = :tag_label';
                $statement = $db->prepare($query);
                $statement->bindValue(':tag_label', $tag_label);
                $statement->execute();
                $tags[$statement->fetch(PDO::FETCH_ASSOC)['tag_id']] = $tag_label;
            }

            // get image IDs matching the first tag ID
            $first_tag_id = array_key_first($tags);
            $query = 'SELECT `img_id` FROM `imagetags` WHERE `tag_id` = :tag_id';
            $statement = $db->prepare($query);
            $statement->bindValue(':tag_id', $first_tag_id);
            $statement->execute();
            $image_ids = $statement->fetchAll(PDO::FETCH_ASSOC);

            // reduce image results to match all tags
            foreach ($tags as $tag_id => $tag_label) {
                $eligible = array();
                $query = 'SELECT `img_id` FROM `imagetags` WHERE `tag_id` = :tag_id';
                $statement = $db->prepare($query);
                $statement->bindValue(':tag_id', $tag_id);
                $statement->execute();
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);

                foreach ($result as $img) {
                    if (array_search($img, $image_ids) !== false) {
                        $eligible[] = $img;
                    }
                }
                $image_ids = $eligible;
            }

            // get image paths
            foreach ($image_ids as $img_id) {
                $query = 'SELECT `img_path` FROM `images` WHERE `img_id` = :img_id';
                $statement = $db->prepare($query);
                $statement->bindValue(':img_id', $img_id['img_id']);
                $statement->execute();
                $result = $statement->fetch(PDO::FETCH_ASSOC);

                $images[] = array('img_id' => $img_id['img_id'], 'img_path' => $result['img_path']);
            }
            $statement->closeCursor();
        }
        catch (PDOException $e) {
            echo $e->getMessage();
        }

        echo json_encode($images);
    }
?>