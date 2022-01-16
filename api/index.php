<?php
/**
 * Unreal Studio
 * Project: UnrealLicensing
 * User: jhollsoliver
 * Date: 06/06/15
 * Time: 10:31
 */

include_once '../system/autoloader.php';
$sql            = "SELECT configurations FROM settings";
$query          = $DatabaseHandler->query($sql);
$data           = $query->fetch_array();
$configurations = json_decode($data['configurations'], true);

if (isset($_REQUEST['licensekey'])) {
    $licensekey = $Gauntlet->filter($_REQUEST['licensekey']);
    $sql        = "SELECT * FROM licenses WHERE licensekey = '$licensekey'";
    $query      = $DatabaseHandler->query($sql);
    if ($query) {
        if ($query->num_rows < 1) {
            $json['status']  = 301;
            $json['message'] = 'Invalid License Key';
            if ($configurations['signresponse'] == 'true') {
                $json = $SignatureHandler->FormatJsonSignature($json);
            }
            if ($configurations['return_encrypted'] == 'true') {
                $key       = $configurations['encryption_key'];
                //$encrypted = $Tools->encryptRJ256($key, json_encode($json));
                $encrypted = $RNC->encrypt(json_encode($json), $key);
                die($encrypted);
            } else {
                die(json_encode($json));
            }
        }
        $data = $query->fetch_array();
        if ($data['host'] <> '*') {
            $json['status']  = 301;
            $json['message'] = 'This license key is already activated for a domain.';
            if ($configurations['return_encrypted'] == 'true') {
                $key       = $configurations['encryption_key'];
                $encrypted = $RNC->encrypt(json_encode($json), $key);
                die($encrypted);
            } else {
                die(json_encode($json));
            }
        }
        if ($data['status'] <> 'inactive') {
            $json['status']  = 301;
            $json['message'] = 'This license key is already activated';
            if ($configurations['return_encrypted'] == 'true') {
                $key       = $configurations['encryption_key'];
                $encrypted = $RNC->encrypt(json_encode($json), $key);
                die($encrypted);
            } else {
                die(json_encode($json));
            }
        }
        
        //$domain = strtolower($_SERVER['REMOTE_ADDR']);
        $domain = $Tools->get_client_ip_server();
        $sql    = "UPDATE licenses SET host = '$domain', status = 'active' WHERE licensekey = '$licensekey'";
        $query  = $DatabaseHandler->query($sql);
        if ($query) {
            $json['status']  = 200;
            $json['message'] = 'License activated Successfully! Query again for full status';
            if ($configurations['return_encrypted'] == 'true') {
                $key       = $configurations['encryption_key'];
                $encrypted = $RNC->encrypt(json_encode($json), $key);
                die($encrypted);
            } else {
                die(json_encode($json));
            }
        } else {
            $json['status']  = 500;
            $json['message'] = 'Database Error. Try again.';
            if ($configurations['return_encrypted'] == 'true') {
                $key       = $configurations['encryption_key'];
                $encrypted = $RNC->encrypt(json_encode($json), $key);
                die($encrypted);
            } else {
                die(json_encode($json));
            }
        }
        
    } else {
        $json['status']  = 301;
        $json['message'] = 'License not Found!';
        if ($configurations['return_encrypted'] == 'true') {
            $key       = $configurations['encryption_key'];
            $encrypted = $RNC->encrypt(json_encode($json), $key);
            die($encrypted);
        } else {
            die(json_encode($json));
        }
        
    }
}

if (isset($_REQUEST['domain']) || isset($_REQUEST['license'])) {
    if (isset($_REQUEST['domain'])) {
        $domain = $Gauntlet->filter($_REQUEST['domain']);
        
        if (strpos($domain, "www.") === 0) {
            $URL1 = $domain;
            $URL2 = preg_replace("/www./", "", $domain, 1);
        } else {
            $URL1 = $domain;
            $URL2 = "www." . $domain;
        }
    }
    
    if (isset($_REQUEST['license'])) {
        $license = $Gauntlet->filter($_REQUEST['license']);
    }
    
    if (isset($_REQUEST['product'])) {
        $product = $Gauntlet->filter($_REQUEST['product']);
    } else {
        $product = 0;
    }
    
    $now = time();
    
    $sql = "SELECT trialtime, sandbox FROM products WHERE id = '$product'";
    @$query = $DatabaseHandler->query($sql);
    $productdata = $query->fetch_array();
    
    if ($productdata['sandbox'] == true) {
        $json['status']  = 200;
        $json['message'] = 'License is valid by Sandbox.';
        /*if($configurations['returndata'] == 'true')
        {
        $json['licensedata']['domain'] = $licensedata['host'];
        $json['licensedata']['customer_email'] = $licensedata['customer_email'];
        $json['licensedata']['licensekey'] = $licensedata['licensekey'];
        $json['licensedata']['expirydate'] = $licensedata['expirydate'];
        $json['licensedata']['issued-by'] = $licensedata['issued-by'];
        $json['licensedata']['comments'] = $licensedata['comments'];
        }*/
        if ($configurations['return_encrypted'] == 'true') {
            $key       = $configurations['encryption_key'];
            $encrypted = $RNC->encrypt(json_encode($json), $key);
            die($encrypted);
        } else {
            die(json_encode($json));
        }
    }
    
    if (isset($domain)) {
        $sql = "SELECT * FROM licenses WHERE productid = '$product' AND host IN ('$URL1', '$URL2')";
    } else if (isset($license)) {
        $sql = "SELECT * FROM licenses WHERE productid = '$product' AND licensekey = '$license'";
    }
    @$query = $DatabaseHandler->query($sql);
    if ($query) {
        if ($query->num_rows > 0) {
            $licensedata = $query->fetch_array();
            if ($licensedata['status'] == 'active') {
                if ($licensedata['expirydate'] > $now) {
                    $json['status']  = 200;
                    $json['message'] = 'License is valid.';
                    if (isset($licensedata['parameters']) && $licensedata['parameters'] <> '') {
                        $parameters = $licensedata['parameters'];
                        parse_str($parameters, $parameters);
                        $json['parameters'] = $parameters;
                    }
                    if ($configurations['returndata'] == 'true') {
                        $json['licensedata']['domain']         = $licensedata['host'];
                        $json['licensedata']['customer_email'] = $licensedata['customer_email'];
                        $json['licensedata']['licensekey']     = $licensedata['licensekey'];
                        $json['licensedata']['expirydate']     = $licensedata['expirydate'];
                        $json['licensedata']['issued-by']      = $licensedata['issued-by'];
                        $json['licensedata']['comments']       = $licensedata['comments'];
                    }
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                    
                    
                } else {
                    $json['status']  = 301;
                    $json['message'] = 'Your license is expired';
                    if ($configurations['returndata'] == 'true') {
                        $json['licensedata']['domain']         = $licensedata['host'];
                        $json['licensedata']['customer_email'] = $licensedata['customer_email'];
                        $json['licensedata']['licensekey']     = $licensedata['licensekey'];
                        $json['licensedata']['expirydate']     = $licensedata['expirydate'];
                        $json['licensedata']['issued-by']      = $licensedata['issued-by'];
                        $json['licensedata']['comments']       = $licensedata['comments'];
                    }
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                }
            } else {
                switch ($licensedata['status']) {
                    case 'inactive':
                        $json['status']  = 301;
                        $json['message'] = 'Your license is inactive. Maybe, you need to activate it.';
                        if ($configurations['returndata'] == 'true') {
                            $json['licensedata']['domain']         = $licensedata['host'];
                            $json['licensedata']['customer_email'] = $licensedata['customer_email'];
                            $json['licensedata']['licensekey']     = $licensedata['licensekey'];
                            $json['licensedata']['expirydate']     = $licensedata['expirydate'];
                            $json['licensedata']['issued-by']      = $licensedata['issued-by'];
                            $json['licensedata']['comments']       = $licensedata['comments'];
                        }
                        if ($configurations['signresponse'] == 'true') {
                            $json = $SignatureHandler->FormatJsonSignature($json);
                        }
                        if ($configurations['return_encrypted'] == 'true') {
                            $key       = $configurations['encryption_key'];
                            $encrypted = $RNC->encrypt(json_encode($json), $key);
                            die($encrypted);
                        } else {
                            die(json_encode($json));
                        }
                        break;
                    case 'processing':
                        $json['status']  = 301;
                        $json['message'] = 'Your license is in processing status. Wait some time and try again.';
                        if ($configurations['returndata'] == 'true') {
                            $json['licensedata']['domain']         = $licensedata['host'];
                            $json['licensedata']['customer_email'] = $licensedata['customer_email'];
                            $json['licensedata']['licensekey']     = $licensedata['licensekey'];
                            $json['licensedata']['expirydate']     = $licensedata['expirydate'];
                            $json['licensedata']['issued-by']      = $licensedata['issued-by'];
                            $json['licensedata']['comments']       = $licensedata['comments'];
                        }
                        if ($configurations['signresponse'] == 'true') {
                            $json = $SignatureHandler->FormatJsonSignature($json);
                        }
                        if ($configurations['return_encrypted'] == 'true') {
                            $key       = $configurations['encryption_key'];
                            $key       = $configurations['encryption_key'];
                            $encrypted = $RNC->encrypt(json_encode($json), $key);
                            die($encrypted);
                        } else {
                            die(json_encode($json));
                        }
                        break;
                    case 'suspended':
                        $json['status']  = 301;
                        $json['message'] = 'Your license is suspended. Contact the Administrator.';
                        if ($configurations['returndata'] == 'true') {
                            $json['licensedata']['domain']         = $licensedata['host'];
                            $json['licensedata']['customer_email'] = $licensedata['customer_email'];
                            $json['licensedata']['licensekey']     = $licensedata['licensekey'];
                            $json['licensedata']['expirydate']     = $licensedata['expirydate'];
                            $json['licensedata']['issued-by']      = $licensedata['issued-by'];
                            $json['licensedata']['comments']       = $licensedata['comments'];
                        }
                        if ($configurations['signresponse'] == 'true') {
                            $json = $SignatureHandler->FormatJsonSignature($json);
                        }
                        if ($configurations['return_encrypted'] == 'true') {
                            $key       = $configurations['encryption_key'];
                            $key       = $configurations['encryption_key'];
                            $encrypted = $RNC->encrypt(json_encode($json), $key);
                            die($encrypted);
                        } else {
                            die(json_encode($json));
                        }
                        break;
                    default:
                        $json['status']  = 301;
                        $json['message'] = 'Your license is inactive. Maybe, you need to activate it.';
                        if ($configurations['returndata'] == 'true') {
                            $json['licensedata']['domain']         = $licensedata['host'];
                            $json['licensedata']['customer_email'] = $licensedata['customer_email'];
                            $json['licensedata']['licensekey']     = $licensedata['licensekey'];
                            $json['licensedata']['expirydate']     = $licensedata['expirydate'];
                            $json['licensedata']['issued-by']      = $licensedata['issued-by'];
                            $json['licensedata']['comments']       = $licensedata['comments'];
                        }
                        if ($configurations['signresponse'] == 'true') {
                            $json = $SignatureHandler->FormatJsonSignature($json);
                        }
                        if ($configurations['return_encrypted'] == 'true') {
                            $key       = $configurations['encryption_key'];
                            $encrypted = $RNC->encrypt(json_encode($json), $key);
                            die($encrypted);
                        } else {
                            die(json_encode($json));
                        }
                        break;
                }
            }
            
            
        } else {
            if ($productdata['trialtime'] > 0 && isset($domain)) {
                //$licensekey = $Tools->create_guid();
                $licensekey = $Tools->GenSerialByMask($configurations['serialmask']);
                $trialtime  = $productdata['trialtime'];
                $expirydate = strtotime("+$trialtime days");
                $sql        = "INSERT INTO `licenses`(`host`, `licensekey`, `customer_email`, `expirydate`, `productid`, `status`, `issued-by`, `comments`) VALUES ('$URL1','$licensekey','automatic@issuer.php','$expirydate','$product','active','1','Issued by Trial Time Automatic Setup')";
                @$query = $DatabaseHandler->query($sql);
                if ($query) {
                    $json['status']  = 200;
                    $json['message'] = 'License in Trial.';
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                } else {
                    $json['status']  = 500;
                    $json['message'] = 'Error while setup your trial.';
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                }
            } else {
                $json['status']  = 301;
                $json['message'] = 'License not Found';  // HERE
                if ($configurations['signresponse'] == 'true') {
                    $json = $SignatureHandler->FormatJsonSignature($json);
                }
                if ($configurations['return_encrypted'] == 'true') {
                    $key       = $configurations['encryption_key'];
                    $encrypted = $RNC->encrypt(json_encode($json), $key);
                    die($encrypted);
                } else {
                    die(json_encode($json));
                }
            }
        }
    }
    
}

if(isset($_REQUEST['autoconfig']) && isset($_REQUEST['module'])){
    $module = $_REQUEST['module'];
    if(isset($_REQUEST['apikey'])){
        $apikey     = $Gauntlet->filter($_REQUEST['apikey']);
    }else{
        $apikey = '00';
    }


    $sql   = "SELECT * FROM apikeys WHERE apikey = '$apikey' AND status = 'active'";
    $queryApi = $DatabaseHandler->query($sql);
    if(!$queryApi){
        $Tools->getAjaxReturn('500', 'Oops!', 'Database Error', 'danger');
    }
    if ($queryApi->num_rows < 1) {
        $Tools->getAjaxReturn('401', 'Oops!', 'Invalid API Key', 'danger');
    }
    $apikey_data = $queryApi->fetch_array();
    if($apikey_data['license_read_permission'] == 0){
        $Tools->getAjaxReturn('401', 'Oops!', 'Unauthorized! API Key does not have enough permission to access this resource.', 'danger');
    }

    if($module == 'pml3_tools'){
        $json['status']  = 200;
        $json['message'] = "Success!";
        $json['version'] = PRODUCT_VERSION;
        $json['pubkey'] = base64_encode(file_get_contents('../system/libs/signature/phpmylicense.pub'));
        $json['settings']['signresponse'] = $configurations['signresponse'];
        $json['settings']['return_encrypted'] = $configurations['return_encrypted'];
        die(json_encode($json));
    }else{
        $json['status']  = 401;
        $json['message'] = "Not authorized!";
        die(json_encode($json));
    }
}

if (isset($_REQUEST['apikey']) && isset($_REQUEST['handler']) && isset($_REQUEST['parameters'])) {
    $apikey     = $Gauntlet->filter($_REQUEST['apikey']);
    $handler    = $Gauntlet->filter($_REQUEST['handler']);
    $parameters = $_REQUEST['parameters'];
    $parameters = json_decode($parameters, true);
    
    $sql   = "SELECT * FROM apikeys WHERE apikey = '$apikey' AND status = 'active'";
    $query = $DatabaseHandler->query($sql);
    if ($query) {
        if ($query->num_rows < 1) {
            $json['status']  = 301;
            $json['message'] = 'Invalid API Key';
            if ($configurations['signresponse'] == 'true') {
                $json = $SignatureHandler->FormatJsonSignature($json);
            }
            if ($configurations['return_encrypted'] == 'true') {
                $key       = $configurations['encryption_key'];
                $encrypted = $RNC->encrypt(json_encode($json), $key);
                die($encrypted);
            } else {
                die(json_encode($json));
            }
        }
        $apikey_data = $query->fetch_array();
        
        switch ($handler) {
            
            case 'changestatus':
                if ($apikey_data['license_update_permission'] <> true) {
                    $json['status']  = 401;
                    $json['message'] = 'Permission denied.';
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                }
                if (!isset($parameters['domain'])) {
                    $json['status']  = 500;
                    $json['message'] = 'Missing the Domain parameter. Try again.';
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                }
                if (!isset($parameters['status'])) {
                    $json['status']  = 500;
                    $json['message'] = 'Missing the status parameter. Try again.';
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                }
                
                $host  = $parameters['domain'];
                $sql   = "SELECT status FROM licenses WHERE host = '$host'";
                $query = $DatabaseHandler->query($sql);
                if ($query->num_rows < 1) {
                    $json['status']  = 500;
                    $json['message'] = 'No such domain.';
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                }
                $data = $query->fetch_array();
                
                switch ($parameters['status']) {
                    case 'active':
                    case 'inactive':
                    case 'suspended':
                    case 'processing':
                        $status = $parameters['status'];
                        break;
                    default:
                        $json['status']  = 500;
                        $json['message'] = 'Unknown Status.';
                        if ($configurations['signresponse'] == 'true') {
                            $json = $SignatureHandler->FormatJsonSignature($json);
                        }
                        if ($configurations['return_encrypted'] == 'true') {
                            $key       = $configurations['encryption_key'];
                            $encrypted = $RNC->encrypt(json_encode($json), $key);
                            die($encrypted);
                        } else {
                            die(json_encode($json));
                        }
                        break;
                        
                }
                
                $sql   = "UPDATE licenses SET status = '$status' WHERE host = '$host'";
                $query = $DatabaseHandler->query($sql);
                if ($query) {
                    $json['status']  = 200;
                    $json['message'] = 'Success!';
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                } else {
                    $json['status']  = 500;
                    $json['message'] = 'Error while executing the action.';
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                }
                
                
                break;
            
            case 'changeexpirydate':
                
                if (!isset($parameters['domain'])) {
                    $json['status']  = 500;
                    $json['message'] = 'Missing the Domain parameter. Try again.';
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                }
                if (!isset($parameters['timestamp'])) {
                    $json['status']  = 500;
                    $json['message'] = 'Missing the Timestamp parameter. Try again.';
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                }
                $host  = $parameters['domain'];
                $sql   = "SELECT expirydate FROM licenses WHERE host = '$host'";
                $query = $DatabaseHandler->query($sql);
                if ($query->num_rows < 1) {
                    $json['status']  = 500;
                    $json['message'] = 'No such domain.';
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                }
                $data      = $query->fetch_array();
                $timestamp = $parameters['timestamp'];
                if (!$Tools->is_timestamp($timestamp)) {
                    $json['status']  = 500;
                    $json['message'] = 'Timestamp field is not an unix timestamp.';
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                }
                $sql   = "UPDATE licenses SET expirydate = '$timestamp' WHERE host = '$host'";
                $query = $DatabaseHandler->query($sql);
                if ($query) {
                    $json['status']  = 200;
                    $json['message'] = 'Success!';
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                } else {
                    $json['status']  = 500;
                    $json['message'] = 'Error while executing the action.';
                    if ($configurations['signresponse'] == 'true') {
                        $json = $SignatureHandler->FormatJsonSignature($json);
                    }
                    if ($configurations['return_encrypted'] == 'true') {
                        $key       = $configurations['encryption_key'];
                        $encrypted = $RNC->encrypt(json_encode($json), $key);
                        die($encrypted);
                    } else {
                        die(json_encode($json));
                    }
                }
                
                break;
            
            default:
                $json['status']  = 401;
                $json['message'] = 'Unknown Handler.';
                if ($configurations['signresponse'] == 'true') {
                    $json = $SignatureHandler->FormatJsonSignature($json);
                }
                if ($configurations['return_encrypted'] == 'true') {
                    $key       = $configurations['encryption_key'];
                    $encrypted = $RNC->encrypt(json_encode($json), $key);
                    die($encrypted);
                } else {
                    die(json_encode($json));
                }
                break;
        }
        
    } else {
        $json['status']  = 500;
        $json['message'] = 'Database Error';
        if ($configurations['signresponse'] == 'true') {
            $json = $SignatureHandler->FormatJsonSignature($json);
        }
        if ($configurations['return_encrypted'] == 'true') {
            $key       = $configurations['encryption_key'];
            $encrypted = $RNC->encrypt(json_encode($json), $key);
            die($encrypted);
        } else {
            die(json_encode($json));
        }
    }
}
