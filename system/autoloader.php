<?php
/**
  * Project: CnkProtectPHP
 * User: CnkPoncol.com


 */

/**
 * Configuration Includes
 */

require_once dirname(__FILE__).'/config.php';
require_once dirname(__FILE__).'/cryptoconfig.php';

if (!isset($BaseURL)) {
    header("Location: install/");
}

/**
 * Library Includes
 */
require_once dirname(__FILE__).'/libs/RequestAutomator.php';
require_once dirname(__FILE__).'/libs/Gauntlet.php';
require_once dirname(__FILE__).'/libs/Tools.php';
require_once dirname(__FILE__).'/libs/FilterClass.php';
require_once dirname(__FILE__).'/libs/totp.class.php';
require_once dirname(__FILE__).'/libs/sqAES.php';
require_once dirname(__FILE__).'/libs/SignatureHandler.php';
require_once dirname(__FILE__).'/libs/PML_Obfuscator.core.php';
require_once dirname(__FILE__).'/libs/AESHelper.php';
require_once dirname(__FILE__).'/libs/IssueLicenseCertificateHandler.php';
require_once dirname(__FILE__).'/libs/LicenseCertificateHandler.php';
require_once dirname(__FILE__).'/libs/log.php';
require_once dirname(__FILE__).'/libs/GAuth.php';
require_once dirname(__FILE__).'/libs/ModuleHelper.php';
require_once dirname(__FILE__).'/libs/vendor/autoload.php';

/**
 * Library Object Initialization
 */

$LogHandler = new PML_Log(dirname(__FILE__) . '/logs');
$AES        = new AESHelper();
$RSA        = new \phpseclib\Crypt\RSA();
$LicenseCertChecker = new LicenseCertificateHandler($RSA, $AES);
$LicenseCertChecker->LoadPublicKey($RSAKeyPair['public']);
$LicenseCertificateIssuer = new IssueLicenseCertificateHandler($RSA, $AES);
$LicenseCertificateIssuer->LoadPrivateKey($RSAKeyPair['private']);
$Gauntlet = new Gauntlet();
$RNC = new \RNCryptor\RNCryptor\Encryptor;
$Tools    = new Tools();
$TOTP     = new TOTP();
$DatabaseHandler = new mysqli($Database['host'], $Database['user'], $Database['pass'], $Database['data']);
if ($DatabaseHandler->connect_errno > 0) {
    die($DatabaseHandler->error);
}
$TOTP->setSecretKey($Database['pass']);
$TOTP->setDigitsNumber(16);
$TOTP->setExpirationTime(480);
$Tools->RegisterClass('DbHandler', $DatabaseHandler);
$Tools->RegisterClass('TOTP', $TOTP);
$Tools->RegisterClass('RNCryptor', $RNC);

/**
 * Constants Declaration
 */
define('BASE_URL', $BaseURL);
define('ASSETS_URL', $BaseURL . '/assets');
unset($BaseURL);
define('PRODUCT_NAME', 'CNKProtectPHP');
$version = json_decode(file_get_contents(dirname(__FILE__).'/buildinfo.json'));
define('PRODUCT_VERSION', $version->version);
define('PHPMYLICENSE_API', '');
define('PHPMYLICENSE_UPDATESERVICE', '');
$query    = $DatabaseHandler->query("SELECT purchasecode, configurations FROM settings");
$settings = $query->fetch_array();
$data     = json_decode($settings['configurations']);
define('PRODUCT_UPDATECHANNEL', $data->updatechannel);
$purchasecode = $settings['purchasecode'];
$Tools->setVariable('AppVersion', PRODUCT_VERSION);
$Tools->setVariable('UpdateChannel', PRODUCT_UPDATECHANNEL);
$RequestAutomator = new RequestAutomator();
$ModuleHelper = new ModuleHelper();
$ModuleHelper->setModuleDirectory(dirname(__FILE__).'/modules');

/**
 * Security Initialization
 */

$SignatureHandler = new SignatureHandler();
$SignatureHandler->LoadExternalClass('Rsa', $RSA);

/**
 * Session Declaration
 */

session_start();

/**
 * Connection Check and Offline Mode Definition
 */

if(!$Tools->checkConnection())
{
    define('OFFLINE_MODE', true);
}
