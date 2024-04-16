<?php

class FUserFollow extends FEntityManagerSQL{

    private static $class = "FUserFollow";
    
    private static $table = "userfollow";

    private static $value = "(NULL,:idFollower,:idFollowed)";

    private static $key = "id";

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

    public static function createUserFollowObj($queryResult){
        if(count($queryResult) == 1){
            $userFollow = new EUserFollow($queryResult[0]['idFollower'], $queryResult[0]['idFollowed']);
            $userFollow->setId($queryResult[0]['id']);
            return $userFollow;
        }elseif(count($queryResult) > 1){
            $userFollows = array();
            for($i = 0; $i < count($queryResult); $i++){
                $userFollow = new EUserFollow($queryResult[$i]['idFollower'], $queryResult[$i]['idFollowed']);
                $userFollow->setId($queryResult[$i]['id']);
                $userFollows[] = $userFollow;
            }
            return $userFollows;
        }else{
            return array();
        }
    }

    public static function bind($stmt, $userFollow){
        $stmt->bindValue(":idFollower", $userFollow->getFollower(), PDO::PARAM_INT);
        $stmt->bindValue(":idFollowed", $userFollow->getFollowed(), PDO::PARAM_INT);
    }

    public static function getObj($id){
        $fem = FEntityManagerSQL::getInstance();
        $result = $fem->retriveObj(self::getTable(), self::getKey(), $id);
        //var_dump($result);
        if(count($result) > 0){
            $userFollow = self::createUserFollowObj($result);
            return $userFollow;
        }else{
            return null;
        }
    }

    public static function saveObj($obj){
        $fem = FEntityManagerSQL::getInstance();

        $saveUserFollow = $fem->saveObject(self::getClass(), $obj);
        if($saveUserFollow !== null){
            return true;
        }else{
            return false;
        }
    }

    public static function retriveUserFollow($idUser, $idFollowed){
        $fem = FEntityManagerSQL::getInstance();

        $queryResult = $fem::getObjOnAttributes(FUserFollow::getTable(), 'idFollower', $idUser, 'idFollowed', $idFollowed);
        if(count($queryResult) > 0){
            return $queryResult[0][FUserFollow::getKey()];
        }else{
            return false;
        }
    }

    public static function deleteUserFollowInDb($idUser, $idFollowed){
        $fem = FEntityManagerSQL::getInstance();
        
        try{
            $fem->getDb()->beginTransaction();
            $idUserFollow = self::retriveUserFollow($idUser, $idFollowed);
            if($idUserFollow){
                $fem::deleteObjInDb(self::getTable(), self::getKey(), $idUserFollow);
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

    public static function getFollowedNumb($idUser){
        $fem = FEntityManagerSQL::getInstance();

        $result = $fem::retriveObj(self::getTable(), 'idFollower', $idUser);

        return count($result);
    }

    public static function getFollowerNumb($idUser){
        $fem = FEntityManagerSQL::getInstance();

        $result = $fem::retriveObj(self::getTable(), 'idFollowed', $idUser);

        return count($result);
    }

    /**
     * return a list of the users who have the highest number of followers
     */
    public static function getTopUserFollower(){
        $fem = FEntityManagerSQL::getInstance();
        try{
            $query = "SELECT uf.idFollowed, COUNT(uf.idFollowed) as followCount FROM ". self::getTable() . " uf GROUP BY uf.idFOllowed ORDER BY followCount DESC LIMIT ". MAX_VIP . ";";
            $stmt = $fem->getDb()->prepare($query);
            $stmt->execute();
            $rowNum = $stmt->rowCount();
            if($rowNum > 0){
                $result = array();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while ($row = $stmt->fetch()){
                    $result[] = $row;
                }
                return $result;
            }else{
                return array();
            }
            
        }catch(PDOException $e){
            echo "ERROR" . $e->getMessage();
            return array();
        }
    }

    
}