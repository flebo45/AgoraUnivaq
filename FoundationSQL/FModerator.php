<?php

class FModerator extends FEntityManagerSQL{
    private static $class = "FModerator";

    private static $table = "moderator";

    private static $value = "(:idUser)";

    private static $key = "idUser";

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

    public static function createModeratorObj($queryResult){
        if(count($queryResult) > 0){
            $mod = new EModerator($queryResult[0]['name'], $queryResult[0]['surname'], $queryResult[0]['year'], $queryResult[0]['email'],$queryResult[0]['password'], $queryResult[0]['username']);
            $mod->setId($queryResult[0]['idUser']);
            $mod->setHashedPassword($queryResult[0]['password']);
            return $mod;
        }else{
            return array();
        }
        
        
    }

    public static function bind($stmt, $id){
        $stmt->bindValue(":idUser", $id, PDO::PARAM_INT);
        //var_dump($id);
    }

    public static function getObj($id){
        $fem = FEntityManagerSQL::getInstance();
        $result = $fem->retriveObj(FPerson::getTable(), FModerator::getKey(), $id);
        //var_dump($result);
        if(count($result) > 0){
            $mod = self::createModeratorObj($result);
            return $mod;
        }else{
            return null;
        }

    }

    public static function saveObj($obj){
        $fem = FEntityManagerSQL::getInstance();

        $savePerson = $fem->saveObject(FPerson::getClass(), $obj);
        //var_dump($savePerson);
        if($savePerson !== null){
            $saveMod = $fem->saveObjectFromId(self::getClass(), $savePerson);
            return $saveMod;
        }else{
            return false;
        }
    }

    public static function getModByUsername($username){
        $fem = FEntityManagerSQL::getInstance();

        $result = $fem::retriveObj(FPerson::getTable(), 'username', $username);
        //var_dump($result);

        if($result !== null && count($result) > 0){
            $user = self::createModeratorObj($result);
            return $user;
        }else{
            return null;
        }
    }
}