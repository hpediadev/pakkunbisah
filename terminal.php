<?php
/**
 * Giovanne Oliveira - JhollsOliver.me.
 * Date: 16/05/2016
 * Time: 16:25
 */

if(!isset($_REQUEST['support'])){
    die('<b>Error! </b>Unauthorized Access.<br><br>Error ID: 9d9f2ff<br>Operation ID: ' . substr(md5(rand(0, 999)), 0, 11));
}
include_once 'system/autoloader.php';
$accesslevel = 0;
if(isset($_SESSION['Permissions']))
{
    switch($_SESSION['Permissions'])
    {
        case 'root':
            $accesslevel = addslashes("Root Access - Full User Permission");
            break;
        case 'internal':
            $accesslevel = "Developer Access - PHPMySecurity Employee - Special Permission";
            break;
        case 'user':
            $accesslevel = "Restrict User Access";
            break;
        default:
            $accesslevel = 'Unknown';
            break;
    }
}

?>

<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <title>PHPMyLicense Admin Terminal</title>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="system/libs/jterm/js/jquery.mousewheel-min.js"></script>
    <script src="system/libs/jterm/js/jquery.terminal.min.js"></script>
    <link href="system/libs/jterm/css/jquery.terminal.min.css" rel="stylesheet"/>
    <script src="system/libs/jterm/js/xml_formatting.js"></script>
    <script src="https://unpkg.com/js-polyfills/keyboard.js"></script>

    <script>
        jQuery(document).ready(function($) {
            <?php
            if(!isset($_SESSION['Permissions']))
            {
                echo "localStorage.clear();";
            }

            ?>
            $('body').terminal("system/rpc.php", {
                login: true,
                prompt: 'pml> ',
                greetings: "===========================================================================================\nSYSPML - PHPMyLicense Tweak Terminal\nYou are now Authenticated.\nUser: <?php if(isset($_SESSION['User'])){echo $_SESSION['User'];}?>\nPermission Profile:<?php echo $accesslevel;?>\nSystem Version: 1.8.2679\n===========================================================================================\n\n\n",
                onBlur: function() {
                    // the height of the body is only 2 lines initialy
                    return false;
                }

            });
        });
    </script>
</head>
<body>
</body>
</html>

