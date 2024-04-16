<?php

class CReport{

    /**
     * Report a comment 
     * @param int $idComment Refers to the id of the comment
     */
    public static function reportComment($idComment)
    {
        if(CUser::isLogged()){
            $pm = FPersistentManager::getInstance();
            USession::getInstance();
            $idUser = USession::getSessionElement('user');

            $reportedComment = $pm::retriveObj(EComment::getEntity(), $idComment);
            if($reportedComment !== null){
                //create a new Report Obj and persist it
                $report = new EReport('', 'linguaggio scurrile', $idUser);
                $report->setComment($reportedComment);
                $pm::uploadObj($report);
                header('Location: /Agora/Post/visit/'. $reportedComment->getIdPost());
            }else{
                header('Location: /Agora/User/home');
            }
            
        }else{
            header('Location: /Agora/User/login');
        }    
    }

    /**
    * this method is called when a user report a Post 
    * @param int $idPost Refers to the id of a post 
    */
    public static function reportPost($idPost){
        if(CUser::isLogged()){
            $pm = FPersistentManager::getInstance();
            USession::getInstance();
            $idUser = USession::getSessionElement('user');

            $reportedPost = $pm::retriveObj(EPost::getEntity(), $idPost);
            if($reportedPost !== null){
                //create a new Report Obj and persist it
                $report = new EReport(UHTTPMethods::post('description'), UHTTPMethods::post('type'), $idUser);
                $report->setPost($reportedPost[0]);
                $pm::uploadObj($report);
                header('Location: /Agora/Post/visit/'. $idPost);
            }else{
                header('Location: /Agora/User/home');
            }
        }else{
            header('Location: /Agora/User/login');
        }    
    }
}