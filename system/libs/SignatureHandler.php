<?php

/**
 * Created by PhpStorm.
 * User: Giovanne
 * Date: 23-Oct-15
 * Time: 1:57 PM
 */
class SignatureHandler
{
    private $privatekey = "";

    private $publickey = "";

    private $Rsa;

    public function __construct()
    {
        if(file_exists(__DIR__.'/signature/phpmylicense.pub'))
        {
            $this->publickey = file_get_contents(__DIR__.'/signature/phpmylicense.pub');
        }else{
            throw new ErrorException('Unable to load the public key');
        }
        if(file_exists(__DIR__.'/signature/phpmylicense.pri'))
        {
            $this->privatekey = file_get_contents(__DIR__.'/signature/phpmylicense.pri');
        }else{
            throw new ErrorException('Unable to load the private key');
        }
        //return true;
    }

    public function Sign($str)
    {
        $this->Rsa->loadKey($this->privatekey);
        $this->Rsa->setSignatureMode($this->Rsa::SIGNATURE_PKCS1);
        $signature = $this->Rsa->sign($str);
        return base64_encode($signature);
    }

    public function CheckSignature($str, $sign, $pub = "")
    {
        if($pub == "") {
            $pub = $this->publickey;
        }

        $this->Rsa->loadKey($pub);
                
        $this->Rsa->setSignatureMode($this->Rsa::SIGNATURE_PKCS1);
        $sign = base64_decode($sign);
        $valid = $this->Rsa->verify($str, $sign);
        if($valid)
        {
            return true;
        }else{
            return false;
        }
    }

    public function FormatJsonSignature($json, $signature = "")
    {
        if($signature == "")
        {
            $j = json_encode($json);
            $signature = $this->Sign($j);
        }
        $json['signature'] = $signature;
        return $json;
    }

    public function ValidateJsonSignature($json)
    {
        if(!is_array($json))
        {
            $json = json_decode($json, true);
        }
        $signature = $json['signature'];

        unset($json['signature']);
        $valid = $this->CheckSignature(json_encode($json), $signature);
        if($valid)
        {
            return true;
        }else{
            return false;
        }

    }

    public function LoadExternalClass($classname, $object)
    {
        $this->$classname = $object;
    }
    public function LoadCustomKey($key, $type = 'public')
    {
        if($type == 'public')
        {
            $this->publickey = $key;
        }else{
            $this->privatekey = $key;
        }
    }

    public function lCK($k)
    {
        $this->publickey = $k;
    }
    
    public function prepareRSAPreProcessor()
    {
        $this->publickey = '-----BEGIN PUBLIC KEY-----
MIIBITANBgkqhkiG9w0BAQEFAAOCAQ4AMIIBCQKCAQB8ylVuMUKmNiiB3VmWf5kS
6Vgk5L0BOuw3f4yrB3biyw7EcvFW+QDUH6brtKcbWhBaouy/P5qTgG/++s+inz4k
Qb44RvEXyYxS/LWrjQscB2NFPqoPCG/iv/hOzV4oPi8MQj5dnFvofzsIZbiWieZJ
TsFRlhTKuPp2plZd7gzLMTmM9WUOQlhkT04ZFMSR8et7h+yAW3QluprcMoP9xaaS
9E//lxpVpZV0yL4eBvvzVWe15b5SngvamMHxJrjWlqJGOdiMkznM5J12iOgc3Pu4
sjgntl6PbHr5GcaAJrtrq7LC6p4jquf6TsofiXQ4D72adhPJlyssNkAdTPPIrceL
AgMBAAE=
-----END PUBLIC KEY-----';
    }

}