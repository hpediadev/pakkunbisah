<?php
/**
  * Project: CnkProtect
 * User: CnkPoncol.com
 */
include_once('../system/autoloader.php');
$sql = "SELECT * FROM settings";
$query = $DatabaseHandler->query($sql);
$data = $query->fetch_array();
$configurations = json_decode($data['configurations'], true);
$purchasecode = $data['purchasecode'];
$LogHandler->setHandler('AJAX');

$_REQUEST = FilterClass::filterXSS($_REQUEST);

if(isset($_REQUEST['handler'])) {
    $handler = $_REQUEST['handler'];
    @$token = $_REQUEST['token'];

    if ($handler == 'login') {
        $user = $Gauntlet->filter($_REQUEST['user']);
        $pass = hash('sha256', $_REQUEST['pass']);
        $sql = "SELECT id, name, status, email, last_login_ip, last_login_timestamp, permissions, 2fa, 2fa_secret FROM `users` WHERE `username` = '$user' AND `password` = '$pass'";
        $query = $DatabaseHandler->query($sql);
        if ($query) {
            if ($query->num_rows > 0) {
                $data = $query->fetch_array();
                if ($data['status'] == 'active') {
                    if($data['2fa'] == 1) {
                        $_SESSION['logged'] = false;
                        $_SESSION['tfa'] = true;
                        $json['twofactor'] = true;
                        $json['seed'] = $data['2fa_secret'];
                    }else{
                        $_SESSION['logged'] = true;
                        $json['twofactor'] = false;
                        $_SESSION['tfa'] = false;
                    }
                    $_SESSION['username'] = $user;
                    $_SESSION['id'] = $data['id'];
                    $_SESSION['name'] = $data['name'];
                    $_SESSION['email'] = $data['email'];
                    $_SESSION['last_login_ip'] = $data['last_login_ip'];
                    $_SESSION['last_login_timestamp'] = $data['last_login_timestamp'];
                    $_SESSION['session_logged_token'] = hash('sha512', $_SESSION['username'].$_SESSION['name'].microtime(true));
                    //$_SESSION['session_auth_token'] = $TOTP->generateCode();
                    $sql = "UPDATE `users` SET last_login_ip`='" . $_SERVER['REMOTE_ADDR'] . "',`last_login_timestamp`='" . time() . "',`WHERE `id` = 'ID'";
                    @$query = $DatabaseHandler->query($sql);
                    $Tools->getAjaxReturn(200, 'Success', 'Login Successfull! Redirecting you...', 'success', $json);
                } else {
                    $Tools->getAjaxReturn(401, 'Oops!', 'This user is not active', 'error');
                }

            } else {
                $Tools->getAjaxReturn(401, 'Oops!', 'Invalid Username or Password', 'error');
            }
        } else {
            $LogHandler->write('Database Error in Ajax Login Method. Error:'.$DatabaseHandler->error, 'dberror');
            $Tools->getAjaxReturn(500, 'Oops!', 'Database Error', 'error');
        }

    } else if ($handler == 'newlicense') {
        $Tools->validateSecurityToken($token);

        $domain = $Gauntlet->filter($_REQUEST['domain']);
        $customer_email = $Gauntlet->filter($_REQUEST['customer_email']);
        $expirydate = strtotime($_REQUEST['expirydate']);
        $productid = $Gauntlet->filter($_REQUEST['productid']);
        $status = $Gauntlet->filter($_REQUEST['status']);
        $comments = $Gauntlet->filter($_REQUEST['comments']);
        $parameters = $Gauntlet->filter($_REQUEST['parameters']);
        if ($domain == '' || $customer_email == '' || $expirydate == '' || $status == '') {
            $Tools->getAjaxReturn(500, 'Oops!', 'You need to input all critical values.', 'error');
        }
        if($domain == "*")
        {
            $status = 'inactive';
        }
        $LicenseKey = $Gauntlet->filter($_REQUEST['serialkey']);
        $userid = $_SESSION['id'];

        $sql = "INSERT INTO `licenses` (`host`, `licensekey`, `customer_email`, `expirydate`, `productid`, `status`, `issued-by`, `comments`, `parameters`) VALUES ('$domain', '$LicenseKey', '$customer_email', '$expirydate', '$productid', '$status', '$userid', '$comments', '$parameters');";
        //die(var_dump($sql));
        $query = $DatabaseHandler->query($sql);
        if ($query) {
            $Tools->getAjaxReturn(200, 'Yay!', 'License issued successfully!', 'success');
        } else {
            $LogHandler->write('Database Error in NewLicense Method - '.$query->error, 'dberror');
            $Tools->getAjaxReturn(500, 'Oops!', 'Database Error.', 'error');
        }

    } else if ($handler == 'savelicense') {
        $Tools->validateSecurityToken($token);
        $id = $Gauntlet->filter($_REQUEST['id']);
        $domain = $Gauntlet->filter($_REQUEST['domain']);
        $customer_email = $Gauntlet->filter($_REQUEST['customer_email']);
        $expirydate = strtotime($_REQUEST['expirydate']);
        $productid = $Gauntlet->filter($_REQUEST['productid']);
        $status = $Gauntlet->filter($_REQUEST['status']);
        $comments = $Gauntlet->filter($_REQUEST['comments']);
        //$LicenseKey = $Tools->create_guid();
        $parameters = $Gauntlet->filter($_REQUEST['parameters']);
        $userid = $_SESSION['id'];
        if ($domain == '' || $customer_email == '' || $expirydate == '' || $status == '') {
            $Tools->getAjaxReturn(500, 'Oops!', 'You need to input all critical values.', 'error');
        }
        $sql = "UPDATE `licenses` SET `host`= '$domain',`customer_email`='$customer_email',`expirydate`='$expirydate',`productid`='$productid',`status`='$status',`comments`= '$comments', `parameters` = '$parameters' WHERE `id` = '$id'";
        //die(var_dump($sql));
        $query = $DatabaseHandler->query($sql);
        if ($query) {
            $Tools->getAjaxReturn(200, 'Yay!', 'License updated successfully!', 'success');
        } else {
            $LogHandler->write('Database Error in SaveLicense Method - '.$query->error, 'dberror');
            $Tools->getAjaxReturn(500, 'Oops!', 'Database Error.', 'error');
        }

    } else if ($handler == 'obfuscate') {

        $Tools->validateSecurityToken($token);


        $script = $_REQUEST['script'];
        $obfuscator = $_REQUEST['obfuscator'];
        if($obfuscator == 1)
        {
            $encoder = new PML_Obfuscator();
            $encoder->SetCode(base64_decode($script));
            $Tools->getAjaxReturn(200, 'Success!', 'Script Obfuscated Successfully!', 'success', array('script' => base64_encode($encoder->getEncodedCode())));
            /*
            $json['status'] = 200;
            $json['message']['header'] = 'Success!';
            $json['message']['text'] = 'Script obfuscated successfully!';
            $json['message']['type'] = 'success';
            $json['script'] = base64_encode($encoder->GetEncodedCode());
            die(json_encode($json));*/

        }elseif($obfuscator == 2)
        {
            if(defined('OFFLINE_MODE'))
            {
                $Tools->getAjaxReturn(500, 'Oops!', 'You are in Offline Mode', 'error');
            }
            $fields = array(
                'purchasecode' => PURCHASE_CODE,
                'script' => urlencode($script),
            );
            $fields_string = '';
            foreach ($fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }
            rtrim($fields_string, '&');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, PHPMYLICENSE_API . '/phpobfuscator/newencoder');
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $result = json_decode(curl_exec($ch), true);
            curl_close($ch);
            if ($result['status'] == 200) {
                $json['status'] = 200;
                $json['message']['header'] = 'Success!';
                $json['message']['text'] = 'Script obfuscated successfully!';
                $json['message']['type'] = 'success';
                $json['script'] = $result['script'];
                die(json_encode($json));
            } else if ($result['status'] == 301) {
                $Tools->getAjaxReturn(500, 'Oops!', 'You have a Invalid or Unauthorized Purchase Code. Our automated Anti-Nulling system is tracking you now to futher analysis.', 'error');
            } else {
                $LogHandler->write('Remote API Error in Obfuscator', 'systemerror');
                $json['status'] = 500;
                $json['message']['header'] = 'Oops!';
                $json['message']['text'] = 'PHPMyLicense API Error. Contact the support.';
                $json['message']['type'] = 'error';
                $json['error'] = print_r($result, true);
                //die(var_dump($result));
                die(json_encode($json));

            }

        }else{
            $Tools->getAjaxReturn(500, 'Oops!', 'No valid encoder selected', 'error');
        }

    } else if ($handler == 'newproduct') {
        $Tools->validateSecurityToken($token);
        $fullname = $Gauntlet->filter($_REQUEST['fullname']);
        $shortname = $Gauntlet->filter($_REQUEST['shortname']);
        $sandbox = $Gauntlet->filter($_REQUEST['sandbox']);
        $price = $Gauntlet->filter($_REQUEST['price']);
        $currency = $Gauntlet->filter($_REQUEST['currency']);
        $renewalcycle = $Gauntlet->filter($_REQUEST['renewalcycle']);
        $trialtime = $Gauntlet->filter($_REQUEST['trialtime']);
        $added = time();
        if ($fullname == '' || $shortname == '' || $sandbox == '' || $trialtime == '') {
            $Tools->getAjaxReturn(500, 'Oops!', 'You need to input all critical values.', 'error');
        }

        $sql = "INSERT INTO `products` (`fullname`, `shortname`, `added`, `sandbox`, `price`, `currency`, `renewalcycle`, `trialtime`) VALUES ('$fullname', '$shortname', '$added', '$sandbox', '$price', '$currency', '$renewalcycle', '$trialtime');";
        //die(var_dump($sql));
        $query = $DatabaseHandler->query($sql);
        if ($query) {
            $Tools->getAjaxReturn(200, 'Yay!', 'Product inserted successfully!', 'success');
        } else {
            $LogHandler->write('Database Error in NewProduct Method - '.$query->error, 'dberror');
            $Tools->getAjaxReturn(500, 'Oops!', 'Database Error.', 'error');
        }
    } else if ($handler == 'genclass') {
        $Tools->validateSecurityToken($token);
        $productid = $Gauntlet->filter($_REQUEST['productid']);
        if(!isset($_REQUEST['type'])){
            $Tools->getAjaxReturn(404, 'Oops!', 'Missing type parameter.', 'error');
        }
        $type = $Gauntlet->filter($_REQUEST['type']);
        $expired_html = file_get_contents('../api/tpl/license_expired.tpl');

        if($type == 1){
            $class_content = "
            \$result = json_decode(file_get_contents('".BASE_URL."/api/validate/host/'.\$_SERVER['SERVER_NAME'].'/".$productid."'), true);

            if(\$result['status'] != 200) {
            \$html = \"" . $expired_html . "\";
            \$search = '<%returnmessage%>';
            \$replace = \$result['message'];
            \$html = str_replace(\$search, \$replace, \$html);


            die( \$html );

            }
            ?>";
        }elseif($type == 2){
            $class_content = "
            /* 
               This variable should be set in any other part of your application. 
               It is here just for test purposes. Keep that in mind.
            */
            define('LICENSE_KEY', 'ABC-123');
            
            
            \$result = json_decode(file_get_contents('".BASE_URL."/api/validate/licensekey/'.LICENSE_KEY.'/".$productid."'), true);

            if(\$result['status'] != 200) {
            \$html = \"" . $expired_html . "\";
            \$search = '<%returnmessage%>';
            \$replace = \$result['message'];
            \$html = str_replace(\$search, \$replace, \$html);


            die( \$html );

            }
            ?>";
        }else{
            $Tools->getAjaxReturn(500, 'Oops!', 'Type not allowed.', 'error');
        }


        $comment = "<?php /**
            * CNKProtectPHP - Copyright 2022 - v1.1.0
            *
            *
            * LICENSE: www.cnkponcol.com
            * @package    CNKProtectPHP
            * @author     Cnkponcol <support 089667771377>
            * @copyright  2021 - 2022 CnkProtectPHP
            * @license    http://www.cnkponcol.com/  CNKProtectPHP
            * @version    v1.1.0
            * @link       https://cnkponcol.com */
            ";
        if (ob_get_level() == 0) ob_start();

        $code = base64_encode($comment . $class_content);
        $json['status'] = 200;
        $json['message']['header'] = 'Success!';
        $json['message']['text'] = 'Class Generated Successfully!';
        $json['message']['type'] = 'success';
        $json['class'] = $code;

        die(json_encode($json));
    } else if ($handler == 'editproduct') {
        $Tools->validateSecurityToken($token);
        $fullname = $Gauntlet->filter($_REQUEST['fullname']);
        $shortname = $Gauntlet->filter($_REQUEST['shortname']);
        $sandbox = $Gauntlet->filter($_REQUEST['sandbox']);
        $price = $Gauntlet->filter($_REQUEST['price']);
        $currency = $Gauntlet->filter($_REQUEST['currency']);
        $renewalcycle = $Gauntlet->filter($_REQUEST['renewalcycle']);
        $trialtime = $Gauntlet->filter($_REQUEST['trialtime']);
        $productid = $Gauntlet->filter($_REQUEST['id']);

        $sql = "UPDATE `products` SET `fullname`= '$fullname',`shortname`= '$shortname',`sandbox`= '$sandbox',`price`= '$price', `currency`= '$currency', `renewalcycle`= '$renewalcycle', `trialtime`= '$trialtime' WHERE `id` = '$productid'";
        $query = $DatabaseHandler->query($sql);
        if ($query) {
            $Tools->getAjaxReturn(200, 'Yay!', 'Product updated successfully!', 'success');
        } else {
            $LogHandler->write('Database Error in EditProduct Method - '.$query->error, 'dberror');
            $Tools->getAjaxReturn(500, 'Oops!', 'Database Error.', 'error');
        }

    } else if ($handler == 'changepwd') {
        $Tools->validateSecurityToken($token);
        $actual = hash('sha256', $_REQUEST['oldpass']);
        $newpwd = hash('sha256', $_REQUEST['newpass']);
        $newpwd2 = hash('sha256', $_REQUEST['newpass2']);

        if ($newpwd <> $newpwd2) {
            $Tools->getAjaxReturn(500, 'Oops!', 'The password and the confirmation don\'t match.', 'error');
        }
        $username = $_SESSION['username'];

        $sql = "SELECT password FROM users WHERE username = '$username'";
        $query = $DatabaseHandler->query($sql);
        if (!$query) {
            $LogHandler->write('Database Error while retrieving password hash on changepwd method - '.$query->error, 'dberror');
            $Tools->getAjaxReturn(500, 'Oops!', 'Database Error.', 'error');
        }
        $data = $query->fetch_array();
        if ($data['password'] <> $actual) {
            $Tools->getAjaxReturn(500, 'Oops!', 'Your actual password don\'t match.', 'error');
        }

        $sql = "UPDATE users SET password = '$newpwd' WHERE username = '$username'";
        $query = $DatabaseHandler->query($sql);
        if ($query) {
            session_destroy();
            $Tools->getAjaxReturn(200, 'Success!', 'Password successfully updated', 'error');
        } else {
            $LogHandler->write('Database Error while saving new password hash in changepwd Method - '.$query->error, 'dberror');
            $Tools->getAjaxReturn(500, 'Oops!', 'Database Error.', 'error');
        }
    } else if ($handler == 'updatesettings') {
        $Tools->validateSecurityToken($token);


        $serialmask = $Gauntlet->filter($_REQUEST['serialmask']);
        $returnvalue = $Gauntlet->filter($_REQUEST['returndata']);
        $encryption_key = $Gauntlet->filter($_REQUEST['encryption_key']);
        $returnencrypted = $Gauntlet->filter($_REQUEST['returnencrypted']);
        //$updatechannel = $Gauntlet->filter($_REQUEST['updatechannel']);
        $updatechannel = $_REQUEST['updatechannel'];
        switch($updatechannel)
        {
            case 'stable':
            case 'beta':
            case 'alpha':
            case 'devonly':
                break;
            default:
                $updatechannel = 'stable';
        }
        $signresponse = $Gauntlet->filter($_REQUEST['signresponse']);
        $signaturetype = $Gauntlet->filter($_REQUEST['signaturetype']);


        if($updatechannel == 'beta')
        {
            $apiquery = PHPMYLICENSE_API.'/update/checkbetastatus?purchasecode='.$purchasecode;
            $apiquery = file_get_contents($apiquery);
            $apiresponse = json_decode($apiquery);

            if($apiresponse->beta <> true)
            {
                $Tools->getAjaxReturn(401, 'Oops!', 'You are not a beta tester, so, you can\'t use Beta Update Channel.', 'error');
            }
        }
        if($updatechannel == 'internal')
        {
            $Tools->getAjaxReturn(401, 'Oops!', 'Missing developer.flag file. <br><br> Your IP don\'t seems to be registered as Employee IP or PHPMySecurity Internal IPs.', 'error');
        }
        $sql = "SELECT configurations FROM settings";
        $query = $DatabaseHandler->query($sql);
        $data = $query->fetch_array();
        $data = json_decode($data['configurations'], true);
        $data['serialmask'] = $serialmask;
        $data['returndata'] = $returnvalue;
        $data['encryption_key'] = $encryption_key;
        $data['return_encrypted'] = $returnencrypted;
        $data['signresponse'] = $signresponse;
        $data['signaturetype'] = $signaturetype;
        $data['updatechannel'] = $updatechannel;
        $data = json_encode($data);

        $sql = "UPDATE settings SET configurations = '$data'";
        $query = $DatabaseHandler->query($sql);
        if ($query) {
            $Tools->getAjaxReturn(200, 'Oops!', 'Settings successfully updated.', 'success');
        } else {
            $LogHandler->write('Database Error while updating settings in updatesettings Method - '.$query->error, 'dberror');
            $Tools->getAjaxReturn(500, 'Oops!', 'Database Error.', 'error');
        }


    } else if ($handler == 'checkforupdates') {
        $Tools->validateSecurityToken($token);

        if(defined('OFFLINE_MODE'))
        {
            $Tools->getAjaxReturn(500, 'Oops!', 'You are in Offline Mode. Unable to check for updates.', 'error');
        }

        //$channel = $Gauntlet->filter($_REQUEST['channel']);

        $sql = "SELECT purchasecode FROM settings";
        $query = $DatabaseHandler->query($sql);
        $data = $query->fetch_array();
        $purchasecode = $data['purchasecode'];

        //$response = json_decode(file_get_contents(PHPMYLICENSE_UPDATESERVICE . '/getlatest/'.$purchasecode));
        $response = $Tools->GetLatestVersion($purchasecode);
        if ($response > PRODUCT_VERSION) {

            $Tools->getAjaxReturn(200, 'Success!', 'Latest fetched.', 'success', array('newversion' => $response));
        } else {
            $Tools->getAjaxReturn(404, 'Updater!', 'You\'re using the latest version.', 'success');
        }

    } else if($handler == 'installupdate') {

        $update = $Gauntlet->filter($_REQUEST['update']);
        $filename = md5(microtime());

        ini_set('max_execution_time', 200);

        $updatefile = '../system/temp/'.$filename.'.zip';
        //$return = json_decode(file_get_contents(PHPMYLICENSE_UPDATESERVICE.'/?purchasecode='.$purchasecode.'&channel='.PRODUCT_UPDATECHANNEL.'&version='.$update.'&act=getpackage'));
        $return = json_decode(file_get_contents(PHPMYLICENSE_UPDATESERVICE.'/request/'.$purchasecode.'/'.$update)); // Version 2 endpoint
        if($return->status <> 200)
        {
            $Tools->getAjaxReturn($return->status, 'Updater', $return->message, 'warning');
        }

        $Tools->downloadFile($return->url, $updatefile);

        //include_once('../system/libs/PhpZip/vendor/autoload.php');
        /*$zip = new PhpZip\ZipFile();

        $directoryIterator = new \RecursiveDirectoryIterator('../');
        $zip->addFilesFromIterator($directoryIterator);
        $zip->withNewPassword('TemporaryBackupPassword');
        $safetybackupfile = sha1(microtime()).'.zip';
        $zip->saveAsFile($safetybackupfile);
        $zip->close();
        unset($zip);*/

        $t = explode('/', $return->url);
        $guid = end($t);

        $t = file_get_contents(PHPMYLICENSE_UPDATESERVICE.'/getkey/'.$guid);
        $t = json_decode($t);

        if($t->status != 200)
        {
            $LogHandler->write('Error in Update Service', 'systeemerror');
            $Tools->getAjaxReturn($t->status, 'Updater', 'There\'s an error on the Update Service. Please try again', 'warning');
        }

        $zip = new PhpZip\ZipFile();
        $zip->openFile($updatefile);
        //$key = $AES->decrypt($t->key);
        $zip->withReadPassword($t->key);
        $extraction = $zip->extractTo('../');
        if(!$extraction)
        {
            var_dump($extraction);
            die();
        }
        if(file_exists('postupdate.php'))
        {
            include('postupdate.php');
            unlink('postupdate.php');
        }

        // TIME TO CLEAN ALL UP

        $zip->close();
        unlink($updatefile);


        $Tools->getAjaxReturn(200, 'Success!', 'Your PHPMyLicense Installation was updated to the latest version.', 'success');





        /*$filestream = file_get_contents($file);
        $original = mb_substr($filestream, 0, -32);
        file_put_contents($file, $original);
        $md5 = substr($filestream, -32, 32);
        $checksum = md5_file($file);
        if($checksum == $md5)
        {
            $json['status'] = 200;
            $json['installurl'] = BASE_URL.'/installzip.php?f='.$RevAlgo->EncryptAndEncode($filename);
            die(json_encode($json));
        }else{
            //@unlink($file);
            $json['status'] = 500;
            $json['message']['header'] = 'Oops!';
            $json['message']['text'] = 'Error while checking the package signature. Please, try again.';
            $json['message']['type'] = 'error';
            die(json_encode($json));
        }*/


    } else if($handler == 'encodejs')
    {
        /*if(!$TOTP->validateCode($token))
        {
            $json['status'] = 401;
            $json['message']['header'] = 'Oops!';
            $json['message']['text'] = 'Invalid Token. Refresh the page and try again.';
            $json['message']['type'] = 'error';
            die(json_encode($json));
        }
        $jscode = $_REQUEST['jscode'];

        $apiquery = file_get_contents(PHPMYLICENSE_API.'/jsobfuscator/encode?code='.$jscode.'&purchasecode='.$purchasecode);

        $apiresponse = json_decode($apiquery, true);
        if($apiresponse['status'] == 200)
        {
            $json['status'] = 200;
            $json['jscode'] = $apiresponse['code'];
            die(json_encode($json));
        }else{
            $json['status'] = 500;
            $json['message']['header'] = 'Oops!';
            $json['message']['text'] = $apiresponse['error'];
            $json['message']['type'] = 'error';
            die(json_encode($json));

        }*/
        $LogHandler->write('Encode JS Function Deprecated', 'notice');
        $Tools->getAjaxReturn(200, 'Oops!', 'This function will be avaiable again in PML v4.', 'warning');



    }else if($handler == 'resetpwd')
    {
        if(!isset($_REQUEST['email']))
        {
            $Tools->getAjaxReturn(404, 'Oops!', 'Missing email Parameter.', 'error');
        }
        if($configurations['mail']['active'] <> true)
        {
            $Tools->getAjaxReturn(301, 'Oops!', 'Resource disabled by Admin.', 'error');
        }

        $email = $_REQUEST['email'];

        $sql = "SELECT name, email FROM users WHERE email = '$email'";
        $query = $DatabaseHandler->query($sql);
        if(!$query)
        {
            $LogHandler->write('Database Error while fetching name and email in resetpwd Method - '.$query->error, 'dberror');
            $Tools->getAjaxReturn(500, 'Oops!', 'System Error', 'error');
        }
        if($query->num_rows < 1)
        {
            $Tools->getAjaxReturn(500, 'Oops!', 'The requested user was not found.', 'error');
        }

        $userdata = $query->fetch_array();

        $newpwd = $Tools->generatePassword(16);
        $newpwd_hash = hash('sha256', $newpwd);
        $sql = "UPDATE users SET password = '$newpwd_hash' WHERE email = '$email'";
        $query = $DatabaseHandler->query($sql);
        if(!$query)
        {
            $LogHandler->write('Database Error while saving new password hash in resetpwd Method - '.$query->error, 'dberror');
            $Tools->getAjaxReturn(500, 'Oops!', 'System Error', 'error');
        }

        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $body = file_get_contents('../system/libs/resetpwdmail.tpl');
        $search = array('{$name}', '{$newpwd}');
        $replace = array($userdata['name'], $newpwd);
        $body = str_replace($search, $replace, $body);
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
        // 1 = errors and messages
        // 2 = messages only
        $mail->SMTPAuth   = true;                  // enable SMTP authentication

        switch($configurations['mail']['smtp_security'])
        {
            case 'none':
                break;
            case 'ssl':
                $mail->SMTPSecure = 'ssl';
                break;
            case 'tls':
                $mail->SMTPSecure = 'tls';
                break;
            default:
                break;

        }
        $mail->Timeout  =   20;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($configurations['mail']['smtp_sender']);
        $mail->Host       = $configurations['mail']['smtp_host']; // sets the SMTP server
        $mail->Port       = $configurations['mail']['smtp_port'];                    // set the SMTP port for the GMAIL server
        $mail->Username   = $configurations['mail']['smtp_user']; // SMTP account username
        $mail->Password   = $configurations['mail']['smtp_pass'];        // SMTP account password
        $mail->FromName = "PHPMyLicense Mailer";
        $mail->AddAddress($email);
        $mail->IsHTML(true);
        $mail->Subject  = "Password Recovery";
        $mail->Body = $body;
        $sended = $mail->Send();

        $mail->ClearAllRecipients();
        $mail->ClearAttachments();

        if($sended)
        {
            $Tools->getAjaxReturn(200, 'Yay!', 'Check your email! Your new password will be there.', 'success');
        }else{
            $LogHandler->write('SMTP Error - '.$mail->ErrorInfo, 'systemerror');
            $Tools->getAjaxReturn(500, 'Oops!', 'System Error! '.$mail->ErrorInfo, 'error');
        }
    }

    else if($handler == 'sysbkp')
    {
        /*if(!$TOTP->validateCode($token))
        {
            $json['status'] = 401;
            $json['message']['header'] = 'Oops!';
            $json['message']['text'] = 'Invalid Token. Refresh the page and try again.';
            $json['message']['type'] = 'error';
            die(json_encode($json));
        }*/

        if(!$Tools->CheckIfLogged($_SESSION))
        {
            $LogHandler->write('Unauthorized attempt to download system backup. Remote Address:'.$SERVER['REMOTE_ADDR'], 'critical');
            $Tools->getAjaxReturn(301, 'Oops!', 'You\'re not authorized to perform this operation.', 'error');
        }

        $para = array(
            'db_to_backup' => $Database['data'], //database name
            'db_exclude_tables' => array('ignore') //tables to exclude
        );

        $dump = $Tools->BackupDB($DatabaseHandler, $para);
        $time = time();
        file_put_contents('../system/temp/dbdump.sql', $dump);

        include_once('../system/libs/PhpZip/vendor/autoload.php');
        $zip = new PhpZip\ZipFile();
        $zip->addFile("../system/temp/dbdump.sql", "/dbdump.sql");
        $zip->addEmptyDir('system');
        $zip->addFile('../system/config.php', '/system/config.php');
        $zip->addFile('../system/cryptoconfig.php', '/system/cryptoconfig.php');
        $zip->addFromString('/version.txt', PRODUCT_VERSION);
        if(isset($_REQUEST['pwd']))
        {
            $pw = $_REQUEST['pwd'];
            $zip->withNewPassword($pw);
        }
        $filename = 'backup_'.$time.'.bkp';
        $zip->saveAsFile('../system/temp/'.$filename);
        $zip->close();
        unlink('../system/temp/dbdump.sql');
        $Tools->getAjaxReturn(200, 'Yay!', 'Backup Complete.', 'success', array('url' => BASE_URL.'/system/temp/'.$filename));




    }else if($handler == 'updatemailsettings') {


        $Tools->validateSecurityToken($token);
        $smtp_enabled = $Gauntlet->filter($_REQUEST['smtp_enabled']);
        $smtp_user = $Gauntlet->filter($_REQUEST['smtp_user']);
        $smtp_pass = $Gauntlet->filter($_REQUEST['smtp_pass']);
        $smtp_host = $Gauntlet->filter($_REQUEST['smtp_host']);
        $smtp_port = $Gauntlet->filter($_REQUEST['smtp_port']);
        $smtp_sender = $Gauntlet->filter($_REQUEST['smtp_sender']);
        $smtp_security = $Gauntlet->filter($_REQUEST['smtp_security']);

        $sql = "SELECT configurations FROM settings";
        $query = $DatabaseHandler->query($sql);
        $data = $query->fetch_array();
        $data = json_decode($data['configurations'], true);
        $data['mail']['active'] = $smtp_enabled;
        $data['mail']['smtp_host'] = $smtp_host;
        $data['mail']['smtp_user'] = $smtp_user;
        $data['mail']['smtp_pass'] = $smtp_pass;
        $data['mail']['smtp_port'] = $smtp_port;
        $data['mail']['smtp_sender'] = $smtp_sender;
        $data['mail']['smtp_security'] = $smtp_security;
        $data = json_encode($data);

        $sql = "UPDATE settings SET configurations = '$data'";
        $query = $DatabaseHandler->query($sql);
        if ($query) {
            $Tools->getAjaxReturn(200, 'Oops!', 'Settings successfully updated.', 'success');
        } else {
            $LogHandler->write('Database Error in updatemailsettings Method - '.$query->error, 'dberror');
            $Tools->getAjaxReturn(500, 'Oops!', 'Database Error.', 'error');
        }


    }else if($handler == 'updatepaymentsettings') {

        $Tools->validateSecurityToken($token);
        $payments_enabled = $Gauntlet->filter($_REQUEST['payments_enabled']);
        $clientid = $Gauntlet->filter($_REQUEST['clientid']);
        $clientsecret = $Gauntlet->filter($_REQUEST['clientsecret']);
        $apimode = $Gauntlet->filter($_REQUEST['apimode']);

        $sql = "SELECT configurations FROM settings";
        $query = $DatabaseHandler->query($sql);
        $data = $query->fetch_array();
        $data = json_decode($data['configurations'], true);
        // Workaroung Boostrap Switch return string not boolean
        if($payments_enabled == 'false'){
            $data['payments']['active'] = false;
        }else{
            $data['payments']['active'] = true;
        }
        $data['payments']['clientid'] = $clientid;
        $data['payments']['clientsecret'] = $clientsecret;
        $data['payments']['apimode'] = $apimode;
        $data = json_encode($data);

        $sql = "UPDATE settings SET configurations = '$data'";
        $query = $DatabaseHandler->query($sql);
        if ($query) {
            $Tools->getAjaxReturn(200, 'Oops!', 'Settings successfully updated.', 'success');
        } else {
            $LogHandler->write('Database Error in updatepaymentsettings Method - '.$query->error, 'dberror');
            $Tools->getAjaxReturn(500, 'Oops!', 'Database Error.', 'error');
        }

    }else if($handler == '2fa-authenticate') {

        $token = $_REQUEST['token'];
        $seed = $_REQUEST['seed'];

        $seed = $RevAlgo->DecryptAndDecode($seed); //TODO: REMOVE REVALGO
        $token = str_replace(' ', '', $token);

        $g = new GAuth();

        $v = $g->verifyCode($seed, $token, 0);
        if($v){
            $_SESSION['logged'] = true;
            $Tools->getAjaxReturn(200, 'Success!', 'Redirecting you...', 'success');
        }else {
            $Tools->getAjaxReturn(401, 'Oops!', 'Invalid Two-Factor Authentication Token.', 'error');
        }
    }else if($handler == 'getInstallationDebugData') {

        if($configurations['debug'] == false){
            $Tools->getAjaxReturn(401, 'Oops!', 'Debug option disabled.', 'error');
        }

        echo "==============================================<br>";
        echo "PHPMYLICENSE DEBUG MODE<br>";
        echo "DUMPING DATA...<br>";
        echo "<br><br><br>";
    }else if($handler == 'getRSAKey'){

        if(!isset($_SESSION['logged']) || $_SESSION['logged'] <> true){
            $Tools->getAjaxReturn(401, 'Oops!', 'You need to be logged in to get this resource', 'error');
        }
        $Tools->validateSecurityToken($token);
        if(!isset($_REQUEST['scope'])){
            $Tools->getAjaxReturn(500, 'Oops!', 'Missing Key Scope', 'error');
        }
        switch($_REQUEST['scope']){
            case 'public':
            default:
                $key = file_get_contents('../system/libs/signature/phpmylicense.pub');
                break;
            case 'private':
                $key = file_get_contents('../system/libs/signature/phpmylicense.pri');
                break;
        }

        $Tools->getAjaxReturn(200, 'Success!', 'Resource avaiable', 'success', array('resource' => $key));

    }else if($handler == 'genRSAKeypair'){

        if(!isset($_SESSION['logged']) || $_SESSION['logged'] <> true){
            $Tools->getAjaxReturn(401, 'Oops!', 'You need to be logged in to get this resource', 'error');
        }
        $Tools->validateSecurityToken($token);
        define('CRYPT_RSA_EXPONENT', 65537);
        define('CRYPT_RSA_SMALLEST_PRIME', 64);
        $RSA->setPrivateKeyFormat(CRYPT_RSA_PRIVATE_FORMAT_PKCS1);
        $RSA->setPublicKeyFormat(CRYPT_RSA_PUBLIC_FORMAT_PKCS1);
        try{
            $keypair = $RSA->createKey(4096);
        }catch(Exception $e){
            $Tools->getAjaxReturn(500, 'Oops!', 'Cryptographic Error: '.$e->getMessage(), 'error');
        }
        if(is_writeable('../system/libs/signature/phpmylicense.pri') && is_writeable('../system/libs/signature/phpmylicense.pub')){
            @file_put_contents('../system/libs/signature/phpmylicense.pri', $keypair['privatekey']);
            @file_put_contents('../system/libs/signature/phpmylicense.pub', $keypair['publickey']);
            $Tools->getAjaxReturn(200, 'Success!', 'Your new Keypair is ready to use! No futher action is needed.', 'success');
        }else{
            $Tools->getAjaxReturn(500, 'Oops!', 'Filesystem Error: Permission Denied', 'error');
        }


    } else{
        $Tools->getAjaxReturn(404, 'Oops!', 'Unknown Resource', 'error');
    }
}