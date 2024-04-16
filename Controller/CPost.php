<?php

class CPost{


/**
 * show the page for the creation of a post
 */

public static function postForm(){
    if(CUser::isLogged()){
        $pm = FPersistentManager::getInstance();
        USession::getInstance();

        $userId = USession::getSessionElement('user');
        $userAndPropic = $pm::loadUsersAndImage($userId);

        $view = new VManagePost();
        $view->showCreationForm($userAndPropic);
    }else{
        header('Location: /Agora/User/login');
    }
}

/**
 * create a post taking info from the form and check if the uploaded images are ok
 */
public static function createPost(){
    if(CUser::isLogged()){
        $pm = FPersistentManager::getInstance();
        USession::getInstance();
        $view = new VManagePost();

        $userId = USession::getSessionElement('user');
        $user = $pm::retriveObj(EUser::getEntity(), $userId);

        //create new Post Obj and upload it in the db 
        $post = new EPost(UHTTPMethods::post('title'), UHTTPMethods::post('description'), UHTTPMethods::post('category')); 
        $post->setUser($user);
        $lastId = $pm::uploadObj($post);
        $post->setId($lastId);
        
        //file check for the images uploaded
        $check = UHTTPMethods::files('imageFile','size',0);                                       
        //var_dump($check);
        if($check > 0){
            $uploadedImages = UHTTPMethods::files('imageFile');
            $check = $pm::manageImages($uploadedImages, $post, $userId);
            if(!$check){
                $view->uploadFileError($check);
            }else{
                header('Location: /Agora/User/personalProfile');
            }
        }else{
            header('Location: /Agora/User/personalProfile');
        }
    }else{
        header('Location: /Agora/User/login');
        }
    }


/**
 * show the page of a single post, with it's information and info about the creator
 * @param int $idPost Refers to the id of a post 
 */

public static function visit($idPost){
    $pm = FPersistentManager::getInstance();
    $post = $pm::loadPostInVisited($idPost);
    if(!is_array($post)){
        $view = new VManagePost();
        $visitedUserAndPic = $pm::loadUsersAndImage($post->getUser()->getId());

        $commentsAndUserPic = $pm::loadCommentsAndUsersPic($idPost);

        //array with: like number, follower number, followed number
        $numericInfo = $pm::loadFollLikeNumb($post);

        if(!CUser::isLogged()){
            $userAndPropic = null;
            $like = false;
            $follow = false;
        }else{
            USession::getInstance();
            $userId = USession::getSessionElement('user');

            $userAndPropic = $pm::loadUsersAndImage($userId);
            $follow = $pm::retriveFollow($userId, $post->getUser()->getId());
            $like = $pm::retriveLike($userId, $idPost);
        }

        $view->showPost($userAndPropic, $visitedUserAndPic, $post, $commentsAndUserPic, $numericInfo, $like,  $follow);

    }else{
        header('Location: /Agora/User/home');
    }
}

/**
 * show the list of the Users who liked the Post
 * @param int $idPost Refers to the id of a post 
 */
public static function like($idPost)
{
    if(CUser::isLogged()){
        $pm = FPersistentManager::getInstance();
        $usersAndPropic = $pm::getLikesPage($idPost);

        $view = new VManagePost();
        $view->showUsersList($usersAndPropic, 'like');
    }else{
        header('Location: /Agora/User/login');
    }
}

/**
 * this method is called when a User want to delete a Post 
 * @param int $idPost Refers to the id of a post 
 */
public static function delete($idPost)
{
    if(CUser::isLogged()){
        $pm = FPersistentManager::getInstance();
        USession::getInstance();
        $idUser = USession::getSessionElement('user');

        $post = $pm::getPostAndUser($idPost);
    

        //check if the Post exist
        if(count($post) > 0  && $idUser == $post[0]->getUser()->getId()){
            $pm::deletePost($idPost, $idUser);
            header('Location: /Agora/User/personalProfile');
        }else{
            header('Location: /Agora/User/personalProfile');
        }        
    }else{
        header('Location: /Agora/User/login');
    }
}

/**
 * this method is called when the User want to like the Post that is visualizing 
 * @param int $idPost Refers to the id of a post 
 */
public static function settingLike($idPost)
{
    if(CUser::isLogged()){
        $pm = FPersistentManager::getInstance();
        USession::getInstance();
        $idUser = USession::getSessionElement('user');

        $post = $pm::retriveObj(EPost::getEntity(), $idPost);
        if(count($post) > 0){
            //create new Like Obj and persist it
            $like = new ELike($idUser, $idPost);
            $pm::uploadObj($like);
        }
        header('Location: /Agora/Post/visit/'.$idPost);
    }else{
        header('Location: /Agora/User/login');
    }
}

/**
 * this method is called when the User want to delete teh like of the Post that is visualizing 
 * @param int $idPost Refers to the id of a post 
 */
public static function deleteLike($idPost)
{
    if(CUser::isLogged()){
        $pm = FPersistentManager::getInstance();
        USession::getInstance();
        $idUser = USession::getSessionElement('user');
            
        $like = $pm::retriveLike($idUser, $idPost);

        //check if the like exist and the User who is deleting the like is the same User
        if(!is_array($like)){
            $pm::deleteLike($like->getId(), $idUser);
        }
        header('Location: /Agora/Post/visit/'.$idPost);
    }else{
        header('Location: /Agora/User/login');
    }
}

}