<?php

class FPost extends FEntityManagerSQL{
    private static $class = "FPost";

    private static $table = "post";

    private static $value = "(NULL,:title,:description,:category,:creation_time,:removed,:idUser)";

    private static $key = "idPost";

    public static function getTable(){
        return self::$table;
    }

    public static function getValue(){
        return self::$value;
    }

    public static function getClass(){
        return self::$class;
    }

    public static function getKey(){
        return self::$key;
    }
    
    /**
     * Proxy obj
     */
    public static function createPostObj($queryResult){
        if(count($queryResult) > 0){
            $posts = array();
            for($i = 0; $i < count($queryResult); $i++){
                $p = new EPost($queryResult[$i]['title'],$queryResult[$i]['description'],$queryResult[$i]['category']);
                $p->setId($queryResult[$i]['idPost']);
                $dateTime =  DateTime::createFromFormat('Y-m-d H:i:s', $queryResult[$i]['creation_time']);
                $p->setCreationTime($dateTime);
                $p->setBan($queryResult[$i]['removed']);
                $posts[] = $p;
            }
            return $posts;
        }else{
            return array();
        }
    }

    public static function bind($stmt, $post){
        $stmt->bindValue(":title", $post->getTitle(), PDO::PARAM_STR);
        $stmt->bindValue(":description", $post->getDescription(), PDO::PARAM_STR);
        $stmt->bindValue(":category", $post->getCategory(), PDO::PARAM_STR);
        $stmt->bindValue("creation_time", $post->getTimeStr(), PDO::PARAM_STR);
        $stmt->bindValue(":removed", $post->isBanned(), PDO::PARAM_BOOL);
        $stmt->bindValue(":idUser", $post->getUser()->getId(), PDO::PARAM_INT);
    }

    public static function getObj($id){
        $fem = FEntityManagerSQL::getInstance();
        $result = $fem->retriveObj(self::getTable(), self::getKey(), $id);
        //var_dump($result);
        if(count($result) > 0){
            $post = self::createPostObj($result);
            return $post;
        }else{
            return null;
        }
    }

    public static function saveObj($obj , $fieldArray = null){
        $fem = FEntityManagerSQL::getInstance();

        if($fieldArray === null){
            $savePost = $fem->saveObject(self::getClass(), $obj);
            if($savePost !== null){
                return $savePost;
            }else{
                return false;
            }
        }else{
            try{
                $fem::getDb()->beginTransaction();
                //var_dump($fieldArray);
                foreach($fieldArray as $fv){
                    $fem::updateObj(FPost::getTable(), $fv[0], $fv[1], self::getKey(), $obj->getId());
                }
                $fem->getDb()->commit();
                return true;
            }catch(PDOException $e){
                echo "ERROR " . $e->getMessage();
                $fem->getDb()->rollBack();
                return false;
            }finally{
                $fem->closeConnection();
            }  
        }
        
    }

    /**
     * un post ha immagini, commenti, likes; verificare che chi sta eliminando è il creatore del post
     */
    public static function deletePostInDb($idPost, $idUser){
        $fem = FEntityManagerSQL::getInstance();
        
        try{
            $fem->getDb()->beginTransaction();
            $queryResult = $fem->retriveObj(self::getTable(), self::getKey(), $idPost);

            if($fem::existInDb($queryResult) && $fem::checkCreator($queryResult, $idUser)){
                //mi servono solo gli id della query
                $likesList = $fem->retriveObj(FLike::getTable(), self::getKey(), $idPost);
                for($i = 0; $i < count($likesList); $i++){
                    $fem::deleteObjInDb(FLike::getTable(), FLike::getKey(), $likesList[$i][FLike::getKey()]);
                }

                $commentsList = $fem->retriveObj(FComment::getTable(), self::getKey(), $idPost);
                for($i = 0; $i < count($commentsList); $i++){
                    $reportCommList = $fem->retriveObj(FReport::getTable(), FComment::getKey(), $commentsList[$i][FComment::getKey()]);
                    for($j = 0; $j < count($reportCommList); $j++){
                        $fem::deleteObjInDb(FReport::getTable(), FReport::getKey(), $reportCommList[$j][FReport::getKey()]);
                    }
                    $fem::deleteObjInDb(FComment::getTable(), FComment::getKey(), $commentsList[$i][FComment::getKey()]);
                }

                $imagesList = $fem->retriveObj(FImage::getTable(), self::getKey(), $idPost);
                for($i = 0; $i < count($imagesList); $i++){
                    $fem::deleteObjInDb(FImage::getTable(), FImage::getKey(), $imagesList[$i][FImage::getKey()]);
                }

                $reportList = $fem->retriveObj(FReport::getTable(), self::getKey(), $idPost);
                for($i = 0; $i < count($reportList); $i++){
                    $fem::deleteObjInDb(FReport::getTable(), FReport::getKey(), $reportList[$i][FReport::getKey()]);
                }

                $fem->deleteObjInDb(self::getTable(), self::getKey(), $idPost);

                $fem->getDb()->commit();
                return true;
            }else{
                $fem->getDb()->commit();
                return false;
            }
        }catch(PDOException $e){
            echo "ERROR " . $e->getMessage();
            $fem->getDb()->rollBack();
            return false;
        }finally{
            $fem->closeConnection();
        }
    }

    public static function getSearched($field, $keyword){
        //chiedere la row di post
        //creare i post 
        //settare gli utenti
        //ritornare la lista di post
        $fem = FEntityManagerSQL::getInstance();

        $queryResult = $fem->getSearchedItem(self::getTable(), $field, $keyword);
        foreach($queryResult as $key =>$row){
            if($row['removed'] == true){
                unset($queryResult[$key]);
            }
        }
        if($field == 'title'){
            $posts = self::getPostWithUser($queryResult);
        }else{
            $posts = self::getPostComplete($queryResult);
        }
        
        return $posts;
    }

    public static function postListNotBanned($idUser){
        //ritorna una lista di post non bannati di un utente
        $fem = FEntityManagerSQL::getInstance();
        $queryResult = $fem->objectListNotRemoved(self::getTable(), FPerson::getKey(), $idUser);
        $posts = self::getPostComplete($queryResult);
        return $posts;
    }

    public static function getPostWithUser($queryResult){
        $posts = array();
        if(count($queryResult) > 0){
            $posts =  self::createPostObj($queryResult);
            for($i = 0; $i < count($queryResult); $i++){
                $idUser = $queryResult[$i][FUser::getKey()];
                $user = FUser::getObj($idUser);
                $posts[$i]->setUser($user);
            }
        }
        return $posts;
    }

    public static function getPostComplete($queryResult){
        $posts = array();
        if(count($queryResult) > 0){
            $posts =  self::createPostObj($queryResult);
            for($i = 0; $i < count($queryResult); $i++){
                $idUser = $queryResult[$i][FUser::getKey()];
                $user = FUser::getObj($idUser);
                $posts[$i]->setUser($user);
                //var_dump($posts[$i]);

                $images = FImage::getObjOnPostId($posts[$i]->getId());
                //var_dump($images);
                if($images !== null){
                    foreach($images as $im){
                        $posts[$i]->addImage($im);
                    }
                }
            }
        }
        return $posts;
    }

    public static function postInExplore($idUser){
        $fem = FEntityManagerSQL::getInstance();
        try{
            $query = "SELECT p.* FROM " . FPost::getTable() . " p WHERE p." . FUser::getKey() . " <> :idUser AND p.removed = false ORDER BY p.creation_time DESC LIMIT :limit";
            $stmt = $fem::getDb()->prepare($query);
            $stmt->bindValue(':idUser', $idUser);
            $stmt->bindValue(':limit', MAX_POST_EXPLORE, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) == 0) {   
                return array();
            }else{
                $posts = self::getPostComplete($result);
                return $posts;
            }
        }catch(Exception $e){
            echo "ERROR " . $e->getMessage();
            return null;
        }
    }

    public static function postInVisited($idPost){
        $fem = FEntityManagerSQL::getInstance();
        $queryResult = $fem::retriveObj(self::getTable(), self::getKey(), $idPost);

        $postArr = self::getPostComplete($queryResult);
        return $postArr[0]; 
    }

    public static function comparePostsByCreationTime($post1, $post2) {
        $time1 = $post1->getTime();
        $time2 = $post2->getTime();

        if ($time1 == $time2) {
            return 0;
        }

        return ($time1 > $time2) ? -1 : 1;
    }

}
