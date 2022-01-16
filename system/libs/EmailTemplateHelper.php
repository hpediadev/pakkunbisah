<?php
/**
 * Project: phpmylicense
 * Date: 18/11/2018
 * Time: 23:09
 */

namespace phpmylicense;


class EmailTemplateHelper
{

    private $TemplatePath;
    private $TemplateHtml;
    private $varSearch;
    private $varReplace;

    public function __construct($TemplatePath)
    {
        $this->TemplatePath = $TemplatePath;
    }

    public function loadTemplate($templateFile)
    {
        if(file_exists($this->TemplatePath.'/'.$templateFile))
        {
            $this->TemplateHtml = file_get_contents($this->TemplatePath.'/'.$templateFile.'.tpl');
            return true;
        }else{
            return false;
        }
    }
    public function search($variables)
    {
        if(is_array($variables))
        {
            $this->varSearch = $variables;
        }else{
            throw new \Exception('Parameter must be array');
        }
    }

    public function replace($variables)
    {
        if(is_array($variables))
        {
            $this->varReplace = $variables;
        }else{
            throw new \Exception('Parameter must be array');
        }
    }

    public function bindSearchParam($param)
    {
        $param = '{$'.$param.'}';
        array_push($this->varSearch, $param);
    }

    public function bindReplaceParam($param)
    {
        $param = '{$'.$param.'}';
        array_push($this->varSearch, $param);
    }

    public function render()
    {
        return str_replace($this->varSearch, $this->varReplace, $this->TemplateHtml);
    }



}