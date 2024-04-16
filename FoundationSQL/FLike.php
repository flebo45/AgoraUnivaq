<?php

class FLike extends FEntityManagerSQL{
    private static $class = "FLike";

    private static $table = "likes";

    private static $value = "(NULL,:idUser,:idPost)";

    private static $key = "idLike";

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

    public static function createLikeObj($queryResult){
        if(count($queryResult) == 1){
            $like = new ELike($queryResult[0]['idUser'], $queryResult[0]['idPost']);
            $like->setId($queryResult[0]['idLike']);
            return $like;
        }elseif(count($queryResult) > 1){
            $likes = array();
            for($i = 0; $i < count($queryResult); $i++){
                $l = new ELike($queryResult[$i]['idUser'], $queryResult[$i]['idPost']);
                $l->setId($queryResult[$i]['idLike']);
                $likes[] = $l;
            return $likes;
            }
        }else{
            return array();
        }  
    }

    public static function bind($stmt, $like){
        $stmt->bindValue(":idUser", $like->getIdUser(), PDO::PARAM_INT);
        $stmt->bindValue(":idPost", $like->getIdPost(), PDO::PARAM_INT);
    }

    public static function getObj($id){
        $fem = FEntityManagerSQL::getInstance();
        $result = $fem->retriveObj(self::getTable(), self::getKey(), $id);
        if(count($result) > 0){
            $like = self::createLikeObj($result);
            return $like;
        }else{
            return null;
        }
    }

    public static function saveObj($obj){
        $fem = FEntityManagerSQL::getInstance();
        $saveLike = $fem->saveObject(self::getClass(), $obj);
        if($saveLike !== null){
            return true;
        }else{
            return false;
        }
    }

    public static function getLikeNumber($idPost)
    {
        $fem = FEntityManagerSQL::getInstance();
        $result = $fem::retriveObj(self::getTable(), FPost::getKey(), $idPost);
    
        return count($result);
    }

    public static function getLikeOnUser($idUser, $idPost){
        $fem = FEntityManagerSQL::getInstance();
        $queryResult = $fem::getObjOnAttributes(self::getTable(), Fuser::getKey(), $idUser, FPost::getKey(), $idPost);
        $like = self::createLikeObj($queryResult);

        return $like;
        
    }

    public static function deleteLikeInDb($idLike, $idUser){
        $fem = FEntityManagerSQL::getInstance();

        try{
            $fem->getDb()->beginTransaction();
            $queryResult = $fem->retriveObj(self::getTable(), self::getKey(), $idLike);
            if($fem::existInDb($queryResult) && $fem::checkCreator($queryResult, $idUser)){
                $fem::deleteObjInDb(self::getTable(), self::getKey(), $idLike);
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
}