<?php
/**
 * PHPMyLicense Development Platform.
 * User: giova
 * Date: 21/11/2018
 * Time: 19:37
 * Project: phpmylicense
 */

class ModuleHelper
{

    private $moduledir;

    public function __construct(){

    }

    public function setModuleDirectory($moduledir){

        if(is_dir($moduledir)){
            $this->moduledir = $moduledir;
        }else{
            throw new Exception('Invalid Module Directory');
        }
    }

    public function isInstalled($modulename)
    {
        if(file_exists($this->moduledir.'/'.$modulename.'/module.json'))
        {
            $json = json_decode(file_get_contents($this->moduledir.'/'.$modulename.'/module.json'));
            if($json->enabled == true)
            {
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function getInstalledModules()
    {
        $modules = array_diff(scandir($this->moduledir), array('..', '.', 'index.html'));
        return $modules;
    }

}