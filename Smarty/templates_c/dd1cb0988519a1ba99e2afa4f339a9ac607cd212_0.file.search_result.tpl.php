<?php
/* Smarty version 3.1.33, created on 2023-09-05 17:03:22
  from 'C:\xampp\htdocs\Agora\Smarty\templates\search_result.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_64f7433a228726_71545476',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'dd1cb0988519a1ba99e2afa4f339a9ac607cd212' => 
    array (
      0 => 'C:\\xampp\\htdocs\\Agora\\Smarty\\templates\\search_result.tpl',
      1 => 1693925652,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_64f7433a228726_71545476 (Smarty_Internal_Template $_smarty_tpl) {
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-compatible" content ="IE=edge">
  <meta name="viewport" content="width-device-width, initial-scale-1.0">
  <title>Agorà</title>
  <!-- icon scout cdn -->
  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.6/css/unicons.css">
  <link rel="icon" href="Img/A.png">

  <!-- stylesheet -->
  <link rel="stylesheet" href="/Agora/Smarty/css/style.css">
  <?php echo '<script'; ?>
>
        function ready(){
            if (!navigator.cookieEnabled) {
                alert('Attenzione! Attivare i cookie per proseguire correttamente la navigazione');
            }
        }
        document.addEventListener("DOMContentLoaded", ready);
    <?php echo '</script'; ?>
>
</head>
<body>


<nav>
  <div class="container">
    <a href="/Agora/User/home" class="log" style="text-decoration: none; color: inherit; font-size: 1.5rem; font-weight : bold" >Agorà</a>
    <div class="search-bar">
      <i class ="uil uil-search"></i>
      <label>
        <input type ="search" placeholder="search for post or users">
      </label>
    </div>
    <form  action="/Agora/User/logout" method="post">
      <div>
        <button class="btn btn-primary" type="submit">Log out</button>
      </div>
    </form>
    <div class="profile-photo">
      <img src="Img/A.png" alt="">
    </div>
  </div>
</nav>


<!------------------------------------------------------
                  RESULT FOR SEARCH
----------------------------------------------------->
<main>

  <div class="result">
    <h3 class="text-muted">Result for : <?php echo $_smarty_tpl->tpl_vars['keyword']->value;?>
</h3>
  </div>
  <?php if (count($_smarty_tpl->tpl_vars['searchedPost']->value) === 0) {?>
    <div class="result" style="margin-top: 2%">There are no post with this title, try something else</div>
    <?php } else { ?>
  <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['searchedPost']->value, 'post');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['post']->value) {
?>
  <div class="result" style="margin-top: 2%">
    <div class="left">
      <h3>Post</h3>
        <div class="profile">
          <?php if ($_smarty_tpl->tpl_vars['postUserPic']->value[$_smarty_tpl->tpl_vars['post']->value->getId()]->getSize() > 0) {?>
            <div class="profile-photo">
              <img src="data:<?php echo $_smarty_tpl->tpl_vars['postUserPic']->value[$_smarty_tpl->tpl_vars['post']->value->getId()]->getType();?>
;base64,<?php echo $_smarty_tpl->tpl_vars['postUserPic']->value[$_smarty_tpl->tpl_vars['post']->value->getId()]->getEncodedData();?>
" alt="Img">
            </div>
          <?php } else { ?>
            <div class="profile-photo">
              <img src="/Agora/Smarty/immagini/1.png" alt="">
            </div>
          <?php }?>
          <div class ="handle">
            <h4> <?php echo $_smarty_tpl->tpl_vars['post']->value->getUser()->getUsername();?>
 </h4>
            <p class="text-muted">
              @<?php echo $_smarty_tpl->tpl_vars['post']->value->getuser()->getName();?>

            </p>
          </div>
        </div>
      <div>
      <h3>Title</h3>
        <a href="/Agora/Post/visit/<?php echo $_smarty_tpl->tpl_vars['post']->value->getId();?>
" class="search" style="text-decoration: none; color: inherit; font-size: 1rem; font-weight : bold"> <?php echo $_smarty_tpl->tpl_vars['post']->value->getTitle();?>
</a>
          </div>
        <div>
      </div>
    </div>
  <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
  <?php }?>
    <?php if (count($_smarty_tpl->tpl_vars['searchedUser']->value) === 0) {?>
    <div class="result" style="margin-top: 2%">There are no user with this username, try something else</div>
    <?php } else {
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['searchedUser']->value, 'user');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['user']->value) {
?>
    <div class="right">
      <div class="list-profile">
        <div>
        <h3>User</h3>
          <?php if ($_smarty_tpl->tpl_vars['userPic']->value[$_smarty_tpl->tpl_vars['user']->value->getId()]->getSize() > 0) {?>
            <div class="profile-photo">
              <img src="data:<?php echo $_smarty_tpl->tpl_vars['userPic']->value[$_smarty_tpl->tpl_vars['user']->value->getId()]->getType();?>
;base64,<?php echo $_smarty_tpl->tpl_vars['userPic']->value[$_smarty_tpl->tpl_vars['user']->value->getId()]->getEncodedData();?>
" alt="Img">
            </div>
          <?php } else { ?>
            <div class="profile-photo">
              <img src="/Agora/Smarty/immagini/1.png" alt="">
            </div>
          <?php }?>
          <div class ="handle">
            <a href="/Agora/User/profile/<?php echo $_smarty_tpl->tpl_vars['user']->value->getUsername();?>
" style="text-decoration: none; color: inherit; font-size: 1rem; font-weight : bold"> <?php echo $_smarty_tpl->tpl_vars['user']->value->getUsername();?>
 </a>
            <p class="text-muted">
              @<?php echo $_smarty_tpl->tpl_vars['user']->value->getName();?>

            </p>
          </div>
        </div>
      </div>
  <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
    </div>

        <?php }?>
  </div>
</main><?php }
}
