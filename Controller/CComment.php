<?php

class CComment{

    /**
     * create a comment taking info from the compiled form and associate it to the post
     * @param int $idPost Refers to id of the post
     */
    public static function createComment($idPost){
        if(CUser::isLogged()){
            $pm = FPersistentManager::getInstance();
            USession::getInstance();

            $userId = USession::getSessionElement('user');
            $user = $pm::retriveObj(EUser::getEntity(), $userId);

            $comment = new EComment(UHTTPMethods::post('body'), $user, $idPost);              //TODO
            $pm::uploadObj($comment);
            header('Location: /Agora/Post/visit/'. $idPost);
        }else{
            header('Location: /Agora/User/login');
        }
    }
}