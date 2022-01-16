<?php
/**
 * Project: CnkProtectPHP
 * User: CnkPoncol.com
 */

$envatoenabled = false;
/*if(file_exists(SYSTEMPATH.'/modules/envato/module.json'))
{
    $j = json_decode(file_get_contents(SYSTEMPATH.'/modules/envato/module.json'));
    if($j->enabled = true)
    {
        $envatoenabled = true;
    }
}*/

$envatoenabled = $ModuleHelper->isInstalled('envato');
?>
<aside>
    <div id="sidebar"  class="nav-collapse ">
        <!-- sidebar menu start-->
        <ul class="sidebar-menu" id="nav-accordion">
            <li>
                <a <?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'index'){echo('class="active" ');}?>href="./">
                    <i class="fa fa-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="sub-menu">
                <a <?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'licenses' or basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'newlicense' or basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'checklicensefile'){echo('class="active" ');}?>href="javascript:;" >
                    <i class="fa fa-key"></i>
                    <span>Licenses</span>
                </a>
                <ul class="sub">
                    <li <?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'licenses'){echo('class="active"');}?>><a  href="licenses.php">Licenses Manager</a></li>
                    <li <?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'newlicense'){echo('class="active"');}?>><a  href="newlicense.php">Create New License</a></li>
                    <li <?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'checklicensefile'){echo('class="active"');}?>><a  href="checklicensefile.php">License Certificate Checker</a></li>
                </ul>
            </li>

            <li class="sub-menu">
                <a <?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'jsobfuscator' or basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'generateclass' or basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'obfuscator'){echo('class="active" ');}?>href="javascript:;" >
                    <i class="fa fa-shield"></i>
                    <span>Security Tools</span>
                </a>
                <ul class="sub">
                    <li <?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'generateclass'){echo('class="active"');}?>><a  href="generateclass.php">Generate Protection Class</a></li>
                    <li <?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'obfuscator'){echo('class="active"');}?>><a  href="obfuscator.php">PHP Obfuscator</a></li>
                </ul>
            </li>

            <li class="sub-menu">
                <a <?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'products' or basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'newproduct'){echo('class="active" ');}?>href="javascript:;" >
                    <i class="fa fa-tags"></i>
                    <span>Products</span>
                </a>
                <ul class="sub">
                    <li <?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'products'){echo('class="active"');}?>><a  href="products.php">Manage Products</a></li>
                    <li <?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'newproduct'){echo('class="active"');}?>><a  href="newproduct.php">New Product</a></li>
                </ul>
            </li>

            
            
            

            <li class="sub-menu">
                <a <?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'settings' or basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'api-settings' or basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'paymentsettings' or basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'upload' or basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'mailsettings'){echo('class="active" ');}?>href="javascript:;" >
                    <i class="fa fa-cogs"></i>
                    <span>Settings</span>
                </a>
                <ul class="sub">
                    <li <?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'settings'){echo('class="active"');}?>><a  href="settings.php">System BackUp</a></li>
                   
                    <?php if($envatoenabled)
                        { ?>
                            <li <?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'envatosettings'){echo('class="active"');}?>><a  href="envatosettings.php">Envato Settings</a></li>
                       <?php } ?>
                   
                </ul>
            </li>
<li>
                <a <?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == 'about'){echo('class="active" ');}?>href="about.php">
                    <i class="fa fa-question-circle"></i>
                    <span>About</span>
                </a>
            </li>

        </ul>
        <!-- sidebar menu end-->
    </div>
</aside>