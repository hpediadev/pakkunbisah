<?php
/**
* This  class is create or maintain the log file for your application, 
* where you  will create any log message for your application and store in logfile file under the log directory,
* you also create the date wise log file with custom name .
*
* @author		Giovanne Oliveira
* @authoremail  public@phpmylicense.ml
* @version      0.1
*/

/*define('DIR_PATH',dirname(__FILE__));
define('DIR_LOGS', DIR_PATH."/logs/");*/

class PML_Log {
	private $logfile;
	private $handler;


	
	public function __construct($path)
    {
        $filename = date("Y-m-d").'.log';
        $this->logfile = $path.'/'.$filename;
    }

    public function setHandler($handler)
    {
        $this->handler = $handler;
    }
	
	public function write($message, $type = 'notice') {
		/*echo "<br>".$file = DIR_LOGS . $this->filename;
		
		$handle = fopen($file, 'a+'); 
		
		fwrite($handle,  "\n".date('Y-m-d G:i:s') . ' - ' . $message . "\n");
			
		fclose($handle); */

		$logmessage = date("Y-m-d H:i:s").' - ';

		switch($type)
        {
            case 'debug':
                $logmessage .= 'DEBUG: ';
                break;
            case 'dberror':
                $logmessage .= 'DATABASE ERROR: ';
                break;
            case 'systemerror':
                $logmessage .= 'SYSTEM ERROR: ';
                break;
            case 'crypterror':
                $logmessage .= 'CRYPTOGRAPHIC ERROR: ';
                break;
            case 'critical':
                $logmessage .= 'CRITICAL ERROR: ';
                break;
            case 'notice':
                $logmessage .= 'NOTICE: ';
                break;
            case 'warning':
                $logmessage .= 'WARNING: ';
                break;
            case 'network':
                $logmessage .= 'NETWORK ERROR: ';
                break;
            default:
                $logmessage .= 'MESSAGE: ';
                break;
        }

        $logmessage .= '('.$this->handler.') '.$message;



        return file_put_contents($this->logfile, $logmessage.PHP_EOL, FILE_APPEND);
	}
}
