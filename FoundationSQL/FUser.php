<?php

class FUser extends FEntityManagerSQL{

    private static $class = "FUser";

    private static $table = "user";

    private static $value = "(:ban,:vip,:biography,:working,:studiedAt,:hobby,:idImage,:warnings,:idUser)";

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

    /**
     * Proxy obj
     */
    public static function crateUserObj($queryResult){
        $fem = FEntityManagerSQL::getInstance();
        if(count($queryResult) == 1){
            $attributes = $fem->retriveObj(self::getTable(), "idUser", $queryResult[0]['idUser']);

            $user = new EUser($queryResult[0]['name'], $queryResult[0]['surname'], $queryResult[0]['year'], $queryResult[0]['email'], $queryResult[0]['password'], $queryResult[0]['username']);
            $user->setId($queryResult[0]['idUser']);
            $user->setHashedPassword($queryResult[0]['password']);
            $user->setBan($attributes[0]['ban']);
            $user->setVip($attributes[0]['vip']);
            $user->setBio($attributes[0]['biography']);
            $user->setWorking($attributes[0]['working']);
            $user->setStudiedAt($attributes[0]['studiedAt']);
            $user->setHobby($attributes[0]['hobby']);
            $user->setIdImage($attributes[0]['idImage']);
            $user->setWarning($attributes[0]['warnings']);
            return $user;
        }elseif(count($queryResult) > 1){
            $users = array();
            for($i = 0; $i < count($queryResult); $i++){
                $attributes = $fem->retriveObj(self::getTable(), "idUser", $queryResult[$i]['idUser']);

                $user = new EUser($queryResult[$i]['name'], $queryResult[$i]['surname'], $queryResult[$i]['year'], $queryResult[0]['email'], $queryResult[0]['password'], $queryResult[0]['username']);
                $user->setId($queryResult[$i]['idUser']);
                $user->setHashedPassword($queryResult[$i]['password']);
                $user->setBan($attributes[0]['ban']);
                $user->setVip($attributes[0]['vip']);
                $user->setBio($attributes[0]['biography']);
                $user->setWorking($attributes[0]['working']);
                $user->setStudiedAt($attributes[0]['studiedAt']);
                $user->setHobby($attributes[0]['hobby']);
                $user->setIdImage($attributes[0]['idImage']);
                $user->setWarning($attributes[0]['warnings']);
                $users[] = $user;
            }
            return $users;
        }else{
            return array();
        }
    }

    public static function bind($stmt, $user, $id){
        $stmt->bindValue(":ban", $user->isBanned(), PDO::PARAM_BOOL);
        $stmt->bindValue(":vip", $user->isVip(), PDO::PARAM_BOOL);
        $stmt->bindValue(":biography", $user->getBio(), PDO::PARAM_STR);
        $stmt->bindValue(":working",$user->getWorking(), PDO::PARAM_STR);
        $stmt->bindValue(":studiedAt", $user->getStudiedAt(), PDO::PARAM_STR);
        $stmt->bindValue(":hobby", $user->getHobby(), PDO::PARAM_STR);
        $stmt->bindValue(":idImage", $user->getIdImage(), PDO::PARAM_INT);
        $stmt->bindValue(":warnings", $user->getWarnings(), PDO::PARAM_INT);
        $stmt->bindValue(":idUser", $id, PDO::PARAM_INT);
    }

    public static function getObj($id){
        $fem = FEntityManagerSQL::getInstance();
        $result = $fem->retriveObj(FPerson::getTable(), self::getKey(), $id);
        //var_dump($result);
        if(count($result) > 0){
            $user = self::crateUserObj($result);
            return $user;
        }else{
            return null;
        }

    }

    //if field null salva, sennò deve updetare la table
    //fieldArray è un array che deve contere array aventi nome del field e valore 
    public static function saveObj($obj, $fieldArray = null){
        $fem = FEntityManagerSQL::getInstance(); 

        if($fieldArray === null){
            $savePerson = $fem->saveObject(FPerson::getClass(), $obj);
                if($savePerson !== null){
                    $saveUser = $fem->saveObjectFromId(self::getClass(), $obj, $savePerson);
                    return $saveUser;
                }else{
                    return false;
                }
        }else{
            try{
                $fem::getDb()->beginTransaction();
                //var_dump($fieldArray);
                foreach($fieldArray as $fv){
                    if($fv[0] != "username" && $fv[0] != "password"){
                        $fem::updateObj(FUser::getTable(), $fv[0], $fv[1], self::getKey(), $obj->getId());
                    }else{
                        $fem::updateObj(FPerson::getTable(), $fv[0], $fv[1], self::getKey(), $obj->getId());
                    }
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

    public static function getUserByUsername($username)
    {
        $fem = FEntityManagerSQL::getInstance();

        $result = $fem::retriveObj(FPerson::getTable(), 'username', $username);

        if(count($result) > 0){
            $user = self::crateUserObj($result);
            return $user;
        }else{
            return null;
        }
    }

    public static function getSearched($keyword){
        //chiedere la row di user
        //creare gli utenti
        //ritornare la lista di utenti
        $fem = FEntityManagerSQL::getInstance();

        $queryResult = $fem->getSearchedItem(FPerson::getTable(), 'username', $keyword);
        $users = array();
        if(count($queryResult) == 1){
            $user = self::crateUserObj($queryResult);
            $users[] = $user;
        }elseif(count($queryResult) > 1){
            $users = self::crateUserObj($queryResult);
        }
        return $users;
    }

    /**
     * return a list of all vip (they are max 3)
     */
    public static function loadVipUsers(){
        $fem = FEntityManagerSQL::getInstance();
        $queryResult = $fem->retriveObj(FUser::getTable(), 'vip', '1');
        $users = array();
        if(count($queryResult) == 1){
            $user = self::getObj($queryResult[0][self::getKey()]);
            $users[] = $user;
        }elseif(count($queryResult) > 1){
            for($i = 0; $i < count($queryResult); $i++){
                $user = self::getObj($queryResult[$i][self::getKey()]);
                $users[] = $user;
            }
        }
        return $users;
    }



}