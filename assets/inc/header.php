<?php
/**
 * Project: CnkProtect
 * User: CnkPoncol.com
 */
if(defined('OFFLINE_MODE'))
{
    $messages = '{"total":1,"messages":[{"id":"13","header":"T2ZmbGluZSBNb2Rl","text":"WW91J3JlIGluIE9mZmxpbmUgTW9kZS4=","icon_class":"fa fa-diamond bg-primary","added":"00","redirurl":"#","status":"active"}]}';
}else{
    $messages = @file_get_contents(PHPMYLICENSE_API.'/public/getmessages?purchasecode='.$purchasecode);
}
$messages = json_decode($messages, true);
?>

<header class="header white-bg">
    <div class="sidebar-toggle-box">
        <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
    </div>
    <!--logo start-->
    <a href="index.php" class="logo">CNK<span>ProtectPHP</span></a>
    <!--logo end-->
    <div class="nav notify-row" id="top_menu">
        
        <!--  notification end -->

    </div>
    <div class="top-nav ">

        <?php include 'assets/inc/topbar.php';?>
    </div>
</header>
