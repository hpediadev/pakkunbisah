<?php
/**
 * Unreal Studio
 * Project: UnrealLicensing
 * User: jhollsoliver
 * Date: 03/06/15
 * Time: 21:30
 */

class Tools {

    private $DbHandler;
    private $TOTP;
    private $RNCryptor;
    private $AppVersion;
    private $UpdateChannel;

    /*public function __construct($DbHandler)
    {
        $this->$DbHandler = $DbHandler;
    }*/

    public function RegisterClass($classname, $object)
    {
        $this->$classname = $object;
    }

    public function setVariable($var, $value)
    {
        $this->$var = $value;
    }

    public function CheckIfLogged($session)
    {
        if(isset($session['logged']))
        {
            if($session['logged'] == true)
            {
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function GetUserById($id)
    {
        $sql = "SELECT name FROM users WHERE id = '$id'";
        $query = $this->DbHandler->query($sql);
        if($query)
        {
            if($query->num_rows > 0)
            {
                $data = $query->fetch_array();
                return $data['name'];
            }else{
                return 'User not found';
            }
        }else{
            return false;
        }
    }

    public function create_guid() {
        static $guid = '';
        $namespace = rand(11111, 99999);
        $uid = uniqid("", true);
        $data = $namespace;
        $data .= $_SERVER['REQUEST_TIME'];
        $data .= $_SERVER['HTTP_USER_AGENT'];
        //$data .= $_SERVER['LOCAL_ADDR'];
        //$data .= $_SERVER['LOCAL_PORT'];
        $data .= $_SERVER['REMOTE_ADDR'];
        $data .= $_SERVER['REMOTE_PORT'];
        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
        $guid = '' .
            substr($hash,  0,  8) .
            '-' .
            substr($hash,  8,  4) .
            '-' .
            substr($hash, 12,  4) .
            '-' .
            substr($hash, 16,  4) .
            '-' .
            substr($hash, 20, 12) .
            '';
        return $guid;
    }

    public function GetLatestVersion($purchaseid, $version = NULL)
    {
        if($version == NULL)
        {
            $version = $this->AppVersion;
        }
        //$url = PHPMYLICENSE_UPDATESERVICE.'/getlatest/'.$this->UpdateChannel.'/'.$purchaseid.'/'.$version;
        //$json = @file_get_contents($url);
        //$json = '{"latestversion":"3.1.5"}';
        //$data = json_decode($json, true);
        if(isset($data['error']))
        {
            return  $data['error'];
        }else{
            //return $data['latest'];
        }

    }

    public function is_timestamp($timestamp) {
        if(strtotime(date('d-m-Y H:i:s',$timestamp)) === (int)$timestamp) {
            return $timestamp;
        } else return false;
    }

    public function GenSerialByMask($template)
    {
        $k = strlen($template);
        $sernum = '';
        for ($i=0; $i<$k; $i++)
        {
            switch($template[$i])
            {
                case 'X': $sernum .= chr(rand(65,90)); break;
                case 'x': $sernum .= chr(rand(97,122)); break;
                case '9': $sernum .= rand(0,9); break;
                case '-': $sernum .= '-';  break;
                case '#':
                    $r = rand(1,2);
                    if($r == 1){
                    $sernum .= chr(rand(65,90));
                    }else if($r == 2)
                    {
                        $sernum .= rand(0,9);

                    } else{$sernum .= chr(rand(97,122));} break;

                default: $sernum.=$template[$i];break;
            }
        }
        return $sernum;
    }

    public function downloadFile ($url, $path) {

        $newfname = $path;
        $file = fopen ($url, "rb");
        if ($file) {
            $newf = fopen ($newfname, "wb");

            if ($newf)
                while(!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
                }
        }

        if ($file) {
            fclose($file);
        }

        if ($newf) {
            fclose($newf);
        }
    }

    /*public function decryptRJ256($key,$string_to_decrypt)
    {
        // DEPRECATED
        $iv = md5($key);
        $string_to_decrypt = base64_decode($string_to_decrypt);
        $rtn = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $string_to_decrypt, MCRYPT_MODE_CBC, $iv);
        $rtn = rtrim($rtn, "\0\4");
        return($rtn);
    }

    public function encryptRJ256($key,$string_to_encrypt)
    {
        // DEPRECATED
        $iv = md5($key);
        $rtn = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $string_to_encrypt, MCRYPT_MODE_CBC, $iv);
        $rtn = base64_encode($rtn);
        return($rtn);
    }*/

    public function RNEncrypt($string, $password)
    {

    }

    public function keygen($length=16)
    {
        $key = '';
        list($usec, $sec) = explode(' ', microtime());
        mt_srand((float) $sec + ((float) $usec * 100000));

        //$inputs = array_merge(range('z','a'),range(0,9),range('A','Z'));

        for($i=0; $i<$length; $i++)
        {
            //$key .= $inputs{mt_rand(0,61)};
        }
        //return $key;
    }

    public function ValidateSession($session)
    {
        if(isset($session['session_auth_token']) && $this->TOTP->validateCode($session['session_auth_token']))
        {
            return true;
        }else{
            return false;
        }
    }

    public function UpdateSessionToken()
    {
        $token = $this->TOTP->generateCode();
        $_SESSION['session_auth_token'] = $token;
    }
    public function BackupDB($mysqli, $params)
    {
        $mtables = array(); $contents = "-- Database: `".$params['db_to_backup']."` --\n";

        $results = $mysqli->query("SHOW TABLES");

        while($row = $results->fetch_array()){
            if (!in_array($row[0], $params['db_exclude_tables'])){
                $mtables[] = $row[0];
            }
        }

        foreach($mtables as $table){
            //$contents .= "-- Table `".$table."` --\n";

            $results = $mysqli->query("SHOW CREATE TABLE ".$table);
            while($row = $results->fetch_array()){
                $contents .= $row[1].";\n\n";
            }

            $results = $mysqli->query("SELECT * FROM ".$table);
            $row_count = $results->num_rows;
            $fields = $results->fetch_fields();
            $fields_count = count($fields);

            $insert_head = "INSERT INTO `".$table."` (";
            for($i=0; $i < $fields_count; $i++){
                $insert_head  .= "`".$fields[$i]->name."`";
                if($i < $fields_count-1){
                    $insert_head  .= ', ';
                }
            }
            $insert_head .=  ")";
            $insert_head .= " VALUES\n";

            if($row_count>0){
                $r = 0;
                while($row = $results->fetch_array()){
                    if(($r % 400)  == 0){
                        $contents .= $insert_head;
                    }
                    $contents .= "(";
                    for($i=0; $i < $fields_count; $i++){
                        $row_content =  str_replace("\n","\\n",$mysqli->real_escape_string($row[$i]));
                        $row_content = str_replace('\'\'', 'NULL', $row_content);

                        switch($fields[$i]->type){
                            case 8: case 3:
                            $contents .=  $row_content;
                            break;
                            default:
                                $contents .= "'". $row_content ."'";
                        }
                        if($i < $fields_count-1){
                            $contents  .= ', ';
                        }
                    }
                    if(($r+1) == $row_count || ($r % 400) == 399){
                        $contents .= ");\n\n";
                    }else{
                        $contents .= "),\n";
                    }
                    $r++;
                }
            }
        }

        /*if (!is_dir ( $params['db_backup_path'] )) {
            mkdir ( $params['db_backup_path'], 0777, true );
        }

        $backup_file_name = "sql-backup-".date( "d-m-Y--h-i-s").".sql";

        $fp = fopen($backup_file_name ,'w+');
        if (($result = fwrite($fp, $contents))) {
            echo "Backup file created '--$backup_file_name' ($result)";
        }
        fclose($fp);*/

        return $contents;
    }

    public function generatePassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789#*@$';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }
    
    public function GetAvatarURL($username = 'John Doe')
    {
        if(file_exists('assets/avatars/'.md5($username).'.png'))
        {
            return ASSETS_URL.'/avatars/'.md5($username).'.png';
        }else{
            $url = 'https://ui-avatars.com/api/?name='.$username.'&background=FFC0BB&color=C02C20&size=120';
            $img = file_get_contents($url);
            file_put_contents('assets/avatars/'.md5($username).'.png', $img);
            return ASSETS_URL.'/avatars/'.md5($username).'.png';
        }
    }
    
    public function get_client_ip_server() {
        $ipaddress = '';
        if ($_SERVER['HTTP_CLIENT_IP'])
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if($_SERVER['HTTP_X_FORWARDED_FOR'])
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if($_SERVER['HTTP_X_FORWARDED'])
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if($_SERVER['HTTP_FORWARDED_FOR'])
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if($_SERVER['HTTP_FORWARDED'])
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if($_SERVER['REMOTE_ADDR'])
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
     
        return $ipaddress;
    }
    
    public function getHeaders()
    {
        $headers = array();
        foreach ($_SERVER as $k => $v)
        {
            if (substr($k, 0, 5) == "HTTP_")
            {
                $k = str_replace('_', ' ', substr($k, 5));
                $k = str_replace(' ', '-', ucwords(strtolower($k)));
                $headers[$k] = $v;
            }
        }
        return $headers;
    }

    public function getAjaxReturn($status, $header, $message, $type, $extra = NULL)
    {
        $json['status'] = $status;
        $json['message']['header'] = $header;
        $json['message']['text'] = $message;
        $json['message']['type'] = $type;
        if($extra != NULL)
        {
            if(is_array($extra))
            {
                $json = array_merge($json, $extra);
            }
        }
        header('Content-Type: application/json');
        die(json_encode($json));
    }

    public function validateSecurityToken($token)
    {
        if (!$this->TOTP->validateCode($token)) {
            $this->getAjaxReturn(401, 'Oops!', 'Security Token Expired. Please refresh the page.', 'error');
        }
    }

    public function checkConnection($sCheckHost = 'www.google.com')
    {
        return (bool) @fsockopen($sCheckHost, 80, $iErrno, $sErrStr, 5);
    }

    public function contains($needle, $haystack)
    {
        if(is_array($haystack))
        {
            //$string = 'my domain name is website3.com';
            foreach ($haystack as $arrayfield) {
                //if (strstr($string, $url)) { // mine version
                if (strpos($needle, $arrayfield) !== FALSE) { // Yoshi version
                    //echo "Match found";
                    return true;
                }
            }
            //echo "Not found!";
            return false;
        }else{
            return strpos($haystack, $needle) !== false;
        }

    }


}