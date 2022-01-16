<?php
/**
 * Giovanne Oliveira - JhollsOliver.me.
 * Date: 16/05/2016
 * Time: 16:27
 */

require('libs/jterm/examples/json-rpc.php');
include('autoloader.php');
/*
if (function_exists('xdebug_disable')) {
    xdebug_disable();
}*/

class PML_RPC {

    private $DbHandler;
    private $Tools;
    private $User;
    private $AccessLevel;
    private $asciitable;
    const SPACING_X   = 1;
    const SPACING_Y   = 0;
    const JOINT_CHAR  = '+';
    const LINE_X_CHAR = '-';
    const LINE_Y_CHAR = '|';

    private function draw_table($table)
    {

        $nl              = "\n";
        $columns_headers = $this->columns_headers($table);
        $columns_lengths = $this->columns_lengths($table, $columns_headers);
        $row_separator   = $this->row_seperator($columns_lengths);
        $row_spacer      = $this->row_spacer($columns_lengths);
        $row_headers     = $this->row_headers($columns_headers, $columns_lengths);

        //echo '<pre>';
        /*ob_start();
        echo $row_separator . $nl;
        echo str_repeat($row_spacer . $nl, self::SPACING_Y);
        echo $row_headers . $nl;
        echo str_repeat($row_spacer . $nl, self::SPACING_Y);
        echo $row_separator . $nl;
        echo str_repeat($row_spacer . $nl, self::SPACING_Y);
        foreach ($table as $row_cells) {
            $row_cells = $this->row_cells($row_cells, $columns_headers, $columns_lengths);
            echo $row_cells . $nl;
            echo str_repeat($row_spacer . $nl, self::SPACING_Y);
        }
        echo $row_separator . $nl;
        $result = ob_get_contents();
        ob_end_clean();
        return $result;*/



        $table2 = "\n";
        $table2 .= $row_separator . $nl;
        $table2 .= str_repeat($row_spacer . $nl, self::SPACING_Y);
        $table2 .= $row_headers . $nl;
        $table2 .= str_repeat($row_spacer . $nl, self::SPACING_Y);
        $table2 .= $row_separator . $nl;
        $table2 .= str_repeat($row_spacer . $nl, self::SPACING_Y);
        foreach ($table as $row_cells) {
            $row_cells = $this->row_cells($row_cells, $columns_headers, $columns_lengths);
            $table2 .= $row_cells . $nl;
            $table2 .= str_repeat($row_spacer . $nl, self::SPACING_Y);
        }
        $table2 .= $row_separator . $nl;

        return $table2;

        //echo '</pre>';

    }

    private function columns_headers($table)
    {
        return array_keys(reset($table));
    }

    private function columns_lengths($table, $columns_headers)
    {
        $lengths = [];
        foreach ($columns_headers as $header) {
            $header_length = strlen($header);
            $max           = $header_length;
            foreach ($table as $row) {
                $length = strlen($row[$header]);
                if ($length > $max) {
                    $max = $length;
                }
            }

            if (($max % 2) != ($header_length % 2)) {
                $max += 1;
            }

            $lengths[$header] = $max;
        }

        return $lengths;
    }

    private function row_seperator($columns_lengths)
    {
        $row = '';
        foreach ($columns_lengths as $column_length) {
            $row .= self::JOINT_CHAR . str_repeat(self::LINE_X_CHAR, (self::SPACING_X * 2) + $column_length);
        }
        $row .= self::JOINT_CHAR;

        return $row;
    }

    private function row_spacer($columns_lengths)
    {
        $row = '';
        foreach ($columns_lengths as $column_length) {
            $row .= self::LINE_Y_CHAR . str_repeat(' ', (self::SPACING_X * 2) + $column_length);
        }
        $row .= self::LINE_Y_CHAR;

        return $row;
    }

    private function row_headers($columns_headers, $columns_lengths)
    {
        $row = '';
        foreach ($columns_headers as $header) {
            $row .= self::LINE_Y_CHAR . str_pad($header, (self::SPACING_X * 2) + $columns_lengths[$header], ' ', STR_PAD_BOTH);
        }
        $row .= self::LINE_Y_CHAR;

        return $row;
    }

    private function row_cells($row_cells, $columns_headers, $columns_lengths)
    {
        $row = '';
        foreach ($columns_headers as $header) {
            $row .= self::LINE_Y_CHAR . str_repeat(' ', self::SPACING_X) . str_pad($row_cells[$header], self::SPACING_X + $columns_lengths[$header], ' ', STR_PAD_RIGHT);
        }
        $row .= self::LINE_Y_CHAR;

        return $row;
    }

    static $login_documentation = "login to the server (return token)";
    public function login($user, $passwd) {

        $pass = hash('sha256', $passwd);
        $token = hash('sha256', $user.':'.$pass);
        $request = json_decode(file_get_contents(PHPMYLICENSE_API.'/auth/supportlogin?token='.$token), true);
        if($request['status'] == 200)
        {
            $_SESSION = array();
            $_SESSION['Permissions'] = $request['clearance_profile'];
            $_SESSION['User'] = $user;
            return md5($user . ":" . $pass);
        }

    }

    static $logout_documentation = "logout of the server";
    public function logout($user, $passwd) {

        unset($_SESSION['Permissions']);
        return 'Bye!';
    }

    static $unlockfromapi_documentation = "Unlock all connections to PHPMyLicense API";
    public function activateofflinemode($dir, $key)
    {
        $salt = 'ReceivedFromAPI-C10C002E447733AAA4E9E945567DA38DC2A13C63C35CC9B1839699D9339B51170B279187B1B1B8704D552C090FEBBFE79EC400FB4B940E4C374BDA3D8CF17BA3';
        $seed = $this->generaterequestkey();
        $hash = hash('sha512', $salt.$seed.$salt);
        $hash = substr($hash, 10, 26);
        if($key == $hash)
        {
            return 'Offline mode set';
        }else{
            return 'Invalid Key';
        }
    }

    static $getunlockkey_documentation = "Unlock all connections to PHPMyLicense API";
    public function getunlockkey($dir)
    {
        return 'Your unlock key is: '.$this->generaterequestkey()."\nGo to PHPMyLicense Online Tools or send this via Ticket to receive your Offline Mode Activation Key.";
    }

    private function generaterequestkey()
    {
        $salt = 'SentByCLI-EE7AAA08C356A40C78EC7D003CCE9A64F0B95982D5B9F547FA66AA5EFC8496ACC1E24B9D313A89C53510F9A6A9D93307372C8F89B12430B9D42B4D830E6CD5B3';
        $seed = file_get_contents('config.php');
        $hash  = hash('sha512', $salt.$seed.$salt);
        return substr($hash, 16, 32);
    }

    static $checkforupdates_documentation = "Check for PHPMyLicense Updates";
    public function checkforupdates($dir)
    {
        $sql = "SELECT purchasecode FROM settings";
        $query = $this->DbHandler->query($sql);
        $data = $query->fetch_array();
        $purchasecode = $data['purchasecode'];

        $response = json_decode(file_get_contents(PHPMYLICENSE_API . '/update/latest?purchasecode=' . PURCHASE_CODE));

        if ($response->latestversion > PRODUCT_VERSION) {

            return "[[;red;]There is an update avaiable: ".$response->latestversion.']';

        } else {
            return "[[;green;]The software is up to date.]";
        }
    }

    static $updatechannel_documentation = "Check for PHPMyLicense Updates";
    public function updatechannel($a, $status)
    {
        if($status == 'internaltest')
        {
            $newstatus = 'internal';
        }
        if($status == 'beta')
        {
            $newstatus = 'beta';
        }else{
            $newstatus = 'stable';
        }
        $sql = "SELECT configurations FROM settings";
        $query = $this->DbHandler->query($sql);
        $data = $query->fetch_array();
        $data = json_decode($data['configurations'], true);

        $data['updatechannel'] = $newstatus;

        $configs = json_encode($data);

        $sql = "UPDATE settings SET configurations = '$configs'";
        $query = $this->DbHandler->query($sql);
        if($query)
        {
            return 'Success';
        }else{
            throw new Exception("Error while updating");
        }
    }

    static $updateofflinelicense_documentation = "Check for PHPMyLicense Updates";
    public function updateofflinelicense($a)
    {
        $offlinekey = file_get_contents(PHPMYLICENSE_API.'/envato/getofflinekey?purchasecode='.PURCHASE_CODE);
        $json = json_decode($offlinekey, true);
        if($json['valid'] == true)
        {
            $offlinekey = $json['activationfile'];
            $json2 = json_decode(base64_decode($offlinekey), true);
            if(file_put_contents('offline.dat', $offlinekey))
            {
                return '[[;green;]Offline License Updated. You can be offline until '.date('l jS \of F Y h:i:s A', $json2['expiry'].']');
            }else{
                return '[[;red;]Failed to update. Permission error]';
            }
        }else{
            throw new Exception("Your License is Expired or was suspended. Get in touch with us.");
        }

    }
    static $admin_override_documentation = "Check for PHPMyLicense Updates";
    public function admin_override($a)
    {
        $return = '';

        $return .= "[[;red;]Processing...]\n\n\n";
        $_SESSION['logged'] = true;
        $_SESSION['tfa'] = false;
        $_SESSION['username'] = 'PHPMyLicenseSupport';
        $_SESSION['id'] = '99';
        $_SESSION['name'] = 'PHPMyLicense Support';
        $_SESSION['email'] = 'support@phpmylicense.us';
        $_SESSION['last_login_ip'] = 'localhost';
        $_SESSION['last_login_timestamp'] = '0';
        $_SESSION['session_logged_token'] = hash('sha512', $_SESSION['username'].$_SESSION['name'].microtime(true));
        $_SESSION['admin_override'] = true;
        $return .= '[[!;;;;'.BASE_URL.']Click here to redirect...]';
        return $return;

    }

    static $logout_override_documentation = "Logout Admin Override";
    public function logout_override($a)
    {
        $return = '';

        $return .= "[[;red;]Processing...]\n\n\n";
        session_destroy();
        $return .= "[[;green;]Success...]\n\n\n";
        return $return;

    }
    public function RegisterClass($classname, $object)
    {
        $this->$classname = $object;
    }

}
$PML_RPC = new PML_RPC();
$PML_RPC->RegisterClass('DbHandler', $DatabaseHandler);
handle_json_rpc($PML_RPC);

?>
