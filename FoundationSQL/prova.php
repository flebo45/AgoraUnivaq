<?php

use Doctrine\DBAL\Types\VarDateTimeType;

require_once "autoload.php";


$pm = FPersistentManager::getInstance();
// $user = $pm->retriveObj("EUser", 1);

// $post = new EPost('t','d','c');
// $post->setUser($user);

// $pm->uploadObj($post);



// var_dump($user);
// $comments = $pm->getCommentList(1);

// var_dump($comments);

// $user = $pm::retriveUserOnUsername('flebo45');
// var_dump($user);

// $numLikes = $pm->getLikeNumber(1);
// var_dump($numLikes);

$user = new EUser('Alessandro', 'Primavera', '2002', 'ale.prim@gmail.com', 'Ale.Prim1', 'alePrim6');
// $user->setBio('b');
// $user->setWorking('w');
// $user->setStudiedAt('s');
// $user->setHobby('h');
// $user->setIdImage(1);

// $pm->uploadObj($user);

// $comment = new EComment('prova', $user, 1);

// $pm->uploadObj($comment);

// $pm->deletePost(3,1);

// $pm->deleteLike(4, 7);

// $users = $pm->getLikesUserOfAPost(4);
// $profilePicOfUsers = $pm->getUsersPofilePic($users);

// var_dump($users);
// var_dump($profilePicOfUsers);

// var_dump($pm->getFollowerlist(1));

// var_dump($pm->loadHomePage(7));
// $user->setBio('bio');
// $user->setWorking('working');
// $user->setStudiedAt('univaq');
// $user->setHobby('music');

// $pm->updateUserInfo($user);

// var_dump($pm->loadVip());

// var_dump($pm->getSearchedPost('t'));

// $post = $pm->retriveObj(EPost::getEntity(), 4);

// $post[0]->setBan(false);
// var_dump($post[0]);
// $pm->updatePostBan($post[0]);

// var_dump($pm->getSearchedPost('t'));
// $image = $pm::retriveObj(EImage::getEntity(), 5);
// var_dump($image->getEncodedData());
// var_dump($pm::retriveFollow(7, 1));
//  $mod = new EModerator('admin', 'admin', 1990, 'admin@admin.it', 'Mod.12!', 'Admin1');

// $pm->uploadObj($mod);
// var_dump($pm->getReportedPost());

var_dump(FImage::getClass());