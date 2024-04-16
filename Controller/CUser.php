<?php

class CUser{

    /**
     * check if the user is logged (using session)
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
        if(USession::isSetSessionElement('user')){
            $logged = true;
            self::isBanned();
        }
        return $logged;
    }

    /**
     * check if the user is banned
     * @return void
     */
    public static function isBanned()
    {
        $pm = FPersistentManager::getInstance();
        $userId = USession::getSessionElement('user');
        $user = $pm::retriveObj(EUser::getEntity(), $userId);
        if($user->isBanned()){
            $view = new VUser();
            USession::unsetSession();
            USession::destroySession();
            $view->loginBan();
        }
    }

    /**
     * verify if the choosen username and email already exist, create the User Obj and set a default profile image 
     * @return void
     */
    public static function registration()
    {
        $pm = FPersistentManager::getInstance();
        $view = new VUser();
        if($pm::verifyUserEmail(UHTTPMethods::post('email')) == false && $pm::verifyUserUsername(UHTTPMethods::post('username')) == false){
                $user = new EUser(UHTTPMethods::post('name'), UHTTPMethods::post('surname'),UHTTPMethods::post('age'), UHTTPMethods::post('email'),UHTTPMethods::post('password'),UHTTPMethods::post('username'));
                $pm::uploadObj($user);
                if($pm::retriveObj(EImage::getEntity(), 1) != null){
                    $user->setIdImage(1);
                    $pm::updateUserIdImage($user);
                }else{
                    $image = new EImage('default', 0, "image/png", "default");
                    $pm::uploadObj($image);
                    $user->setIdImage(1);
                    $pm::updateUserIdImage($user);
                }
                header('Location: /Agora/User/login');
        }else{
                $view->registrationError();
            }
    }

    /**
     * check the request, if the user have the session cookie(isLogged()) return the User in the home page, if not and request is in POST 
     * start the checkLogin() to start the login process
     * @return void
     */
    public static function login()
    {
        if(self::isLogged()){
            header('Location: /Agora/User/home');
        }else{
            $view = new VUser();
            $view->showLoginForm();
        }
    }

    /**
     * check if exist the Username inserted, and for this username check the password. If is everything correct the session is created and
     * the User is redirected in the homepage
     */
    public static function checkLogin()
    {
            $pm = FPersistentManager::getInstance();
            $view = new VUser();
            $username = $pm::verifyUserUsername(UHTTPMethods::post('username'));                                            
            if($username){
                $user = $pm::retriveUserOnUsername(UHTTPMethods::post('username'));
                if(password_verify(UHTTPMethods::post('password'), $user->getPassword())){
                    if($user->isBanned()){
                        $view->loginBan();

                    }elseif(USession::getSessionStatus() == PHP_SESSION_NONE){
                            USession::getInstance();
                            USession::setSessionElement('user', $user->getId());
                            header('Location: /Agora/User/home');
                    }
                }else{
                    $view->loginError();
                }
            }else{
                $view->loginError();
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
        header('Location: /Agora/User/login');
    }

    /**
     * load all the Posts in homepage (Posts of the Users that the logged User are following). Also are loaded Information about vip User and
     * about profile Images of all the involved User
     */
    public static function home()
    {
        if(CUser::isLogged()){
            $pm = FPersistentManager::getInstance();
            USession::getInstance();
            $view = new VUser();

            $userId = USession::getSessionElement('user');
            $userAndPropic = $pm::loadUsersAndImage($userId);

            //load all the posts of the users who you follow(post have user attribute) and the profile pic of the author of teh post
            $postInHome = $pm::loadHomePage($userId);
            
            //load the VIP Users, their profile Images and the foillower number
            $arrayVipUserPropicFollowNumb = $pm::loadVip();
        
            $view->home($userAndPropic, $postInHome,$arrayVipUserPropicFollowNumb);
        }else{
            header('Location: /Agora/User/login');
        }   
    }

    /**
     * load Posts belonged to the logged User and his Bio information
     */
    public static function personalProfile()
    {
        if(CUser::isLogged()){ 
            $pm = FPersistentManager::getInstance();
            USession::getInstance();
            $view = new VUser();

            $userId = USession::getSessionElement('user');
            $userAndPropic = $pm::loadUsersAndImage($userId);
                
            //load all the Posts belonged to a User that are not Banned
            $postProfileAndLikes = $pm::loadUserPage($userId);

            //load the number of followed and following users
            $followerNumb = $pm::getFollowerNumb($userId);
            $followedNumb = $pm::getFollowedNumb($userId);

            $view->uploadPersonalUserInfo($userAndPropic, $postProfileAndLikes, $followerNumb, $followedNumb);
        }else{
            header('Location: /Agora/User/login');
        }
    }

    /**
     * load post belonged to the visited User and his informations
     * @param String $username Refers to the username of a user
     */
    public static function profile($username)
    {
            if(CUser::isLogged()){
                $pm = FPersistentManager::getInstance();
                USession::getInstance();

                $personalUserId =  USession::getSessionElement('user');
                $personalUserAndPropic = $pm::loadUsersAndImage($personalUserId);
                if($personalUserAndPropic[0][0]->getUsername() != $username)
                {
                    if($pm::verifyUserUsername($username))
                    {
                        $user = $pm::retriveUserOnUsername($username);
                        $userAndPropic = $pm::loadUsersAndImage($user->getId());

                        $postUser = $pm::loadUserPage($user->getId());
                        $follow = $pm::retriveFollow($personalUserId, $user->getId());

                        $followerNumb = $pm::getFollowerNumb($user->getId());
                        $followedNumb = $pm::getFollowedNumb($user->getId());
                        $view = new VUser();
                        

                        $view->uploadUserInfo($userAndPropic, $personalUserAndPropic, $postUser,  $follow, $followerNumb, $followedNumb);
                    }else{
                        header('Location: /Agora/User/home');
                    }
                }else{
                    header('Location: /Agora/User/personalProfile');
                }
                
            }else{
                header('Location: /Agora/User/login');
            }
    }

    /**
     * load the settings page compiled with the user data
     */
    public static function settings(){
            if(CUser::isLogged()){
                $pm = FPersistentManager::getInstance();
                USession::getInstance();
                $view = new VUser();

                $userId = USession::getSessionElement('user');
                $userAndPropic = $pm::loadUsersAndImage($userId);    
                $view->settings($userAndPropic);
            }else{
                header('Location: /Agora/User/login');
            }
    }

    /**
     * Take the compiled form and use the data for update the user info (Biography, Working, StudeiedAt, Hobby)
     */
    public static function setUserInfo(){
        if(CUser::isLogged()){
            $pm = FPersistentManager::getInstance();
            USession::getInstance();

            $userId = USession::getSessionElement('user');
            $user = $pm::retriveObj(EUser::getEntity(), $userId);

            $user->setBio(UHTTPMethods::post('Bio'));
            $user->setWorking(UHTTPMethods::post('Working'));                                               
            $user->setStudiedAt(UHTTPMethods::post('StudiedAt'));
            $user->setHobby(UHTTPMethods::post('Hobby'));
            $pm::updateUserInfo($user);

            header('Location: /Agora/User/personalProfile');
        }else{
            header('Location: /Agora/User/login');
        }
    }

    /**
     * Take the compiled form, use teh data to cjheck if the username alredy exist and if not update the user Username
     */
    public static function setUsername(){
        if(CUser::isLogged()){
            $pm = FPersistentManager::getInstance();
            USession::getInstance();

            $userId = USession::getSessionElement('user');
            $user = $pm::retriveObj(EUser::getEntity(), $userId);

            if($user->getUsername() == UHTTPMethods::post('username')){
                header('Location: /Agora/User/personalProfile');
            }else{
                if($pm::verifyUserUsername(UHTTPMethods::post('username')) == false)
                {
                    $user->setUsername(UHTTPMethods::post('username'));
                    $pm::updateUserUsername($user);
                    header('Location: /Agora/User/personalProfile');
                }else{
                    $view = new VUser();
                    $userAndPropic = $pm::loadUsersAndImage($userId);
                    $view->usernameError($userAndPropic , true);
                }
            }
        }else{
            header('Location: /Agora/User/login');
        }
    }

    /**
     * Take the compiled form and update the user password
     */
    public static function setPassword(){
        if(CUser::isLogged()){
            $pm = FPersistentManager::getInstance();
            USession::getInstance();

            $userId = USession::getSessionElement('user');
            $user = $pm::retriveObj(EUser::getEntity(), $userId);$newPass = UHTTPMethods::post('password');
            $user->setPassword($newPass);
            $pm::updateUserPassword($user);

            header('Location: /Agora/User/personalProfile');
        }else{
            header('Location: /Agora/User/login');
        }
    }

    /**
     * Take the file, check if there is an upload error, if not update the user image and delete the old one 
     */
    public static function setProPic(){
        if(CUser::isLogged()){
            $pm = FPersistentManager::getInstance();
            USession::getInstance();

            $userId = USession::getSessionElement('user');
            $user = $pm::retriveObj(EUser::getEntity(), $userId);
            
            if(UHTTPMethods::files('imageFile','size') > 0){
                    $uploadedImage = UHTTPMethods::files('imageFile');
                        $checkUploadImage = $pm::uploadImage($uploadedImage);
                        if($checkUploadImage == 'UPLOAD_ERROR_OK' || $checkUploadImage == 'TYPE_ERROR' || $checkUploadImage == 'SIZE_ERROR'){
                            $view = new VUser();
                            $userAndPropic = $pm::loadUsersAndImage($userId);

                            $view->FileError($userAndPropic);
                        }
                        else{
                            $idImage = $pm::uploadObj($checkUploadImage);
                            if($user->getIdImage() != 1){
                                if($pm::deleteImage($user->getIdImage())){
                                    $user->setIdImage($idImage);
                                    $pm::updateUserIdImage($user);
                                }
                                header('Location: /Agora/User/personalProfile');
                            }else{
                                $user->setIdImage($idImage);
                                $pm::updateUserIdImage($user);
                            }
                            header('Location: /Agora/User/personalProfile');
                        }
                    }else{
                    header('Location: /Agora/User/settings');
                    }
        }else{
            header('Location: /Agora/User/login');
        }
    }

    /**
     * load all the post finded by a specifyc category
     * @param String $category Refers to a name of a category
     */
    public static function category($category)
    {
        if(CUser::isLogged()){
            $pm = FPersistentManager::getInstance();
            USession::getInstance();
            $view = new VUser();
        
            $userId = USession::getSessionElement('user');
            $userAndPropic = $pm::loadUsersAndImage($userId);

            //load the VIP Users, their profile Images and the foillower number
            $arrayVipUserPropicFollowNumb = $pm::loadVip();

            $postCategory = $pm::loadPostPerCategory($category);

            $view->category($userAndPropic, $postCategory, $arrayVipUserPropicFollowNumb);
        }else{
            header('Location: /Agora/User/login');
        }
    }

    /**
     * load a limit number of posts that are not belonged to the logged user, so this page is for discover new Users
     */
    public static function explore()
    {
        if(CUser::isLogged()){
            $pm = FPersistentManager::getInstance();
            USession::getInstance();
            $view = new VUser();
                
            $userId = USession::getSessionElement('user');
            $userAndPropic = $pm::loadUsersAndImage($userId);

            ///load the VIP Users, their profile Images and the foillower number
            $arrayVipUserPropicFollowNumb = $pm::loadVip();

            $postExplore = $pm::loadPostInExplore($userId);

                
            $view->explore($userAndPropic, $postExplore, $arrayVipUserPropicFollowNumb);
        }else{
            header('Location: /Agora/User/login');
        }
    }

    /**
     * return a page with a list of Users who are followed by the User logged 
     * @param int $idUser Refers to the id of a user
     */
    public static function followers($idUser)
    {
        if(CUser::isLogged()){
            $pm = FPersistentManager::getInstance();
            $usersListAndPropic = $pm::getFollowedList($idUser);
                
            $view = new VManagePost();
            $view->showUsersList($usersListAndPropic, 'followers');

        }else{
            header('Location: /Agora/User/login');
        }        
    }

    /**
     * return a page with a list of Users who are following the User logged 
     * @param int $idUser Refers to the id of a user
     */
    public static function followed($idUser)
    {
        if(CUser::isLogged()){
            $pm = FPersistentManager::getInstance();
            $usersListAndPropic = $pm::getFollowerList($idUser);
                
            $view = new VManagePost();
            $view->showUsersList($usersListAndPropic, 'followed');
        }else{
            header('Location: /Agora/User/login');
        }
    }

    /**
     * method to follow a user, the check is in the profile() method
     * @param int $followerId Refers to the id of a user
     */
    public static function follow($followedId)
    {
        if(CUser::isLogged()){
            $pm = FPersistentManager::getInstance();
            USession::getInstance();

            $userId = USession::getSessionElement('user');

            //new Follow Object
            $follow = new EUserFollow($userId, $followedId);
            $pm::uploadObj($follow);
            $visitedUser = $pm::retriveObj(EUser::getEntity(), $followedId);
            header('Location: /Agora/User/profile/' . $visitedUser->getUsername());
        }else{
            header('Location: /Agora/User/login');
        }        
    }

    /**
     * method to unfollow a user, the check is in the profile() method
     * @param int $followedId Refers to the id of a user
     */
    public static function unfollow($followedId)
    {
        if(CUser::isLogged()){
            $pm = FPersistentManager::getInstance();
            USession::getInstance();

            $userId = USession::getSessionElement('user');

            $pm::deleteFollow($userId, $followedId);
            $visitedUser = $pm::retriveObj(EUser::getEntity(), $followedId);
            header('Location: /Agora/User/profile/' . $visitedUser->getUsername());
        }else{
            header('Location: /Agora/User/login');
        }    
    }
}