<?php

class CModerator{

    /**
     * check if the Moderator is logged (using session)
     * @return boolean
     */
    public static function isLogged()
    {
        $logged = false;

        if(UCookie::isSet('PHPSESSID')){
            if(session_status() == PHP_SESSION_NONE){
                USession::getInstance();
            }
        }
        if(USession::isSetSessionElement('mod')){
            $logged = true;
        }
        return $logged;
    }

    
    /**
     * check the request, if the Mod have the session cookie(isLogged()) return the Mod in the home page, if not and request is in POST 
     * start the checkLogin() to start the login process
     * @return void
     */
    public static function login()
    {
        if(UServer::getRequestMethod() == "GET"){
            if(self::isLogged()){
                header('Location: /Agora/Moderator/reportList');
            }else{
                $view = new VModerator();
                $view->showLoginForm();
            }
           }
           elseif(UServer::getRequestMethod() == "POST"){
            self::checkLogin();
           }
    }

    
    /**
     * check if exist teh Username inserted, and for this username check the password. If is everything correct the session is created and
     * the Mod is redirected in the homepage
     */
    public static function checkLogin()
    {
        if(UServer::getRequestMethod() != 'GET'){
            $pm = FPersistentManager::getInstance();
            $view = new VModerator();
            $username = $pm::verifyUserUsername(UHTTPMethods::post('username'));                //TODO
            if($username){
                $user = $pm::retriveModOnUsername(UHTTPMethods::post('username'));
                if(password_verify(UHTTPMethods::post('password'), $user->getPassword())){
                    if(USession::getSessionStatus() == PHP_SESSION_NONE){
                        USession::getInstance();
                        USession::setSessionElement('mod', $user->getId());
                        header('Location: /Agora/Moderator/reportList');
                        }
                    }else{
                        $view->loginError();
                    }
                }else{
                    $view->loginError();
                }
            }
        }
    

    
    /**
     * this method can logout the User, unsetting all the session element and destroing the session. Return the user to the Login Page
     * @return void
     */
    public static function logout(){
        USession::getInstance();
        USession::unsetSession();
        USession::destroySession();
        header('Location: /Agora/Moderator/login');
    }

    /**
     * Show the all reported post and comment 
     */
    public static function reportList()
    {
        if(UServer::getRequestMethod() == 'GET')
        {
            if(CModerator::isLogged())
            {
                $pm = FPersistentManager::getInstance();

                USession::getInstance();
                $modId = USession::getSessionElement('mod');

                $mod = $pm::retriveObj(EModerator::getEntity(), $modId);

                $reportedPost = $pm::getReportedPost();
                $reportedComment = $pm::getReportedComment();

                $view = new VModerator();

                $view->showReportList($mod->getUsername(), $reportedPost,  $reportedComment);
            }else{
                header('Location: /Agora/Moderator/login');
            }
        }else{
            header('Location: /Agora/Moderator/reportList');
        }
    }

    /**
     * with this method the Moderator can ban an User or Post or Comment. The ban delete all the related reports to the banned object
     */
    public static function ban($param, $id)
    {
        if(UServer::getRequestMethod() == 'POST')
        {
            if(CModerator::isLogged())
            {
                $pm = FPersistentManager::getInstance();

                if($param == 'user')
                {
                    $user = $pm::retriveObj(EUser::getEntity(), $id);
                    if($user !== null)
                    {
                        $user->setBan(true);
                        $pm::uploadObj($user);
                        //self::sendBanEmail($user, 'ban');
                    }
                }
                elseif($param == 'post')
                {
                    $post = $pm::retriveObj(EPost::getEntity(), $id);
                    if($post !== null)
                    {
                        $post->setBan(true);
                        $pm::uploadObj($post);
                        //self::sendBanEmail($post, 'post');
                        $pm::deleteRelatedReports($param, $id);

                        //setWarnings update the warnings adding 1
                        $post->getUser()->setWarnings();
                        $pm::uploadObj($post->getUser());

                        //if the warnings are 3 the user is banned
                        if($post->getUser()->getWarnings() == 3)
                        {
                            $post->getUser()->setBan(true);
                            $pm::uploadObj($post->getUser());
                            //self::sendBanEmail($post->getUser(), 'ban');
                        }
                    }
                }
                elseif($param == 'comment')
                {
                    $comment = $pm::retriveObj(EComment::getEntity(), $id);
                    if($comment !== null)
                    {
                        $comment->setBan(true);
                        $pm::uploadObj($comment);
                        //self::sendBanEmail($comment, 'comment');
                        $pm::deleteRelatedReports($param, $id);
                    }
                }
                header('Location: /Agora/Moderator/reportList');
            }else{
                header('Location: /Agora/Moderator/login');
            }
        }else{
            header('Location: /Agora/Moderator/reportList');
        }
    }

    /**
     * this function is for sending email: 
     * if $reason is "ban" the $obj will be an EUser obj; 
     * if $reason is "comment" the $obj will be an EComment obj;
     * if $reason is "post" the $obj will be EPost obj.
     */
    private static function sendBanEmail($obj, $reason)
    {
        $headers = "From: noreply@agora.com";
        if($reason == "ban")
        {
            $to = $obj->getEmail();
            $subject = "Banned from our platform Agorà";
            $message = "You (".$obj->getUsername().") are Banned permanently from our platform Agora, cause you are violating our guide lines such as : Violence, Gambling, Offensive Content, Suspicious Acctivities, Pornography.";
        }elseif($reason == "comment")
        {
            $to = $obj->getUser()->getEmail();
            $subject = "Your Comment has been removed";
            $message = "Your Comment (Comment Id: ".$obj->getId().", Comment Body: ".$obj->getBody()."Comment User: ".$obj->getUser()->getUsername().") is violating our guide lines. Your comment has been removed.";
        }elseif($reason == "post")
        {
            $to = $obj->getUser()->getEmail();
            $subject = "Your Post has been removed";
            $message = "Your Post (Post Id: ".$obj->getId().", Post Title: ".$obj->getTitle().", Post Description: ".$obj->getDescription().", Post User: ".$obj->getUser()->getUsername().") is violating our guide lines. Your Post has been removed.";
        }
        mail($to, $subject, $message, $headers);
    }

    /**
     * With this method the Moderato ignore the Report, so it will be delated
     */
    public static function ignore($id)
    {
        if(UServer::getRequestMethod() == 'POST')
        {
            if(CModerator::isLogged())
            {
                $pm = FPersistentManager::getInstance();

                $report = $pm::retriveObj(EReport::getEntity(), $id);
                if($report !== null)
                {
                    $pm::deleteReport($report);
                }
                header('Location: /Agora/Moderator/reportList');
            }else{
                header('Location: /Agora/Moderator/login');
            }
        }else{
            header('Location: /Agora/Moderator/reportList');
        }
    }

    /**
     * this method show the user page in the Moderator view 
     */
    public static function visitUser($id)
    {
        if(UServer::getRequestMethod() == 'GET')
        {
            if(CModerator::isLogged())
            {
                $pm = FPersistentManager::getInstance();

                USession::getInstance();
                $modId = USession::getSessionElement('mod');
                $mod = $pm::retriveObj(EModerator::getEntity(), $modId);
                $modUsername = $mod->getUsername();

                $user = $pm::retriveObj(EUser::getEntity(), $id);
                if($user !== null)
                {
                    $userPic = $pm::retriveObj(EImage::getEntity(), $user->getIdImage());

                    $userPosts = $pm::loadUserPage($id);

                    $followerNumb = $pm::getFollowerNumb($id);
                    $followedNumb = $pm::getFollowedNumb($id);

                    $view = new VModerator();
                    $view->visitUser($user, $userPic, $userPosts, $followerNumb, $followedNumb, $modUsername);
                }else{
                    header('Location: /Agora/Moderator/reportList');
                }  
            }else{
                header('Location: /Agora/Moderator/login');
            }
        }else{
            header('Location: /Agora/Moderator/reportList');
        }
    }

    /**
     * this method show the Post in the Moderator view
     */
    public static function visitPost($id)
    {
        if(UServer::getRequestMethod() == 'GET')
        {
            if(CModerator::isLogged())
            {
                $pm = FPersistentManager::getInstance();

                USession::getInstance();
                $modId = USession::getSessionElement('mod');
                $mod = $pm::retriveObj(EModerator::getEntity(), $modId);
                $modUsername = $mod->getUsername();

                $post = $pm::retriveObj(EPost::getEntity(), $id);
                if($post !== null)
                {
                    $userPic = $pm::retriveObj(EImage::getEntity(), $post->getUser()->getIdImage());

                    $view = new VModerator();
                    $view->visitPost($post, $userPic, $modUsername);
                }else{
                    header('Location: /Agora/Moderator/reportList');
                }
            }else{
                header('Location: /Agora/Moderator/login');
            }
        }else{
            header('Location: /Agora/Moderator/reportList');
        }
    }

}