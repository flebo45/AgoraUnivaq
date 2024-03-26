<?php

class CSearch{

    /**
     * load all the Posts and the Users who have in their Title/Username the $kewyword pass in the $_POST[]
     */
    public static function search()
    {
        if(UServer::getRequestMethod() == 'POST')
        {
            if(CUser::isLogged())
            {
                $pm = FPersistentManager::getInstance();

                //list of Posts 'title LIKE keyword'
                $searchedPosts = $pm::getSerachedPosts(UHTTPMethods::post('keyword'));             //TODO
                //list of Users 'username LIKE keyword'
                $searchedUsers = $pm::getSearchedUsers(UHTTPMethods::post('keyword'));

                //load the Users profile Image 
                $postUserPic = array();
                $userPic = array();

                if(count($searchedPosts) > 0)
                {
                    foreach($searchedPosts as $s)
                    {
                        $postUserPic[$s->getId()] = $pm::retriveObj(EImage::getEntity(), $s->getUser()->getIdImage());
                    }
                }
                if(count($searchedUsers) > 0)
                {
                    foreach($searchedUsers as $u)
                    {
                        $userPic[$u->getId()] = $pm::retriveObj(EImage::getEntity(), $u->getIdImage());
                    }
                }

                $view = new VSearch();
                $view->showSearch(UHTTPMethods::post('keyword'), $searchedPosts, $postUserPic, $searchedUsers, $userPic);        //TODO
            }else{
                header('Location: /Agora/User/login');
            }
        }else{
            header('Location: /Agora/User/home');
        }
    }
}