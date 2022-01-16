<?php
/**
  * Project: CnkProtectPHP
* User: CnkPoncol.com
 */


include_once 'system/autoloader.php';
$LogHandler->setHandler('restore.php');
$Logged = $Tools->CheckIfLogged($_SESSION);
if(!$Logged)
{
    header("Location: login.php?go=".base64_encode($_SERVER["REQUEST_URI"])."");
}
if(isset($_POST['hdnBackupPassword']))
{
    $uploaddir = 'system/temp/';
    $filename = explode(".", $_FILES["zipfile"]["name"]);
    $ext = end($filename);
    //if($_FILES['zipfile']['type'] == 'application/zip')
    if($ext == 'bkp')
    {
        $filename = md5(microtime());
        $uploadedfile = $uploaddir.$filename.'.zip';
        if(move_uploaded_file($_FILES['zipfile']['tmp_name'], $uploadedfile))
        {
            include_once('system/libs/PhpZip/vendor/autoload.php');
            $zip = new PhpZip\ZipFile();
            try{

                $zip->openFile($uploadedfile);
                $zip->withReadPassword($_POST['hdnBackupPassword']);
                if(isset($zip['/version.txt']))
                {
                    $version = $zip['/version.txt'];
                    if($version == PRODUCT_VERSION)
                    {
                        if(!isset($_POST['ignore_config_file']))
                        {
                            if(!file_put_contents('system/config.php', $zip['/system/config.php']))
                            {
                                $error .= 'Error while restoring config file<br>';
                                $LogHandler->write('Error while restoring config file', 'critical');
                            }
                            if(!file_put_contents('system/criptoconfig.php', $zip['/system/criptoconfig.php']))
                            {
                                $error .= 'Error while restoring cryptographic config file<br>';
                                $LogHandler->write('Error while restoring cryptographic config file', 'critical');
                            }
                        }

                        $dbdump = $zip['/dbdump.sql'];
                            $tables = array('apikeys', 'licenses', 'products', 'renew_transactions', 'settings', 'users');
                            foreach($tables as $table)
                            {
                                $query = $DatabaseHandler->query("DROP TABLE `$table`");
                                if(!$query)
                                {
                                    $error .= 'Error while cleaning table.<br>';
                                    $LogHandler->write('Error while truncating table '.$table.' Db Error: '.$DatabaseHandler->error, 'dberror');
                                }
                            }

                            $query = $DatabaseHandler->multi_query($dbdump);
                            if(!$query)
                            {
                                $error .= 'Error while restoring your database.<br>';
                                $LogHandler->write('Error while restoring your database. Db Error: '.$DatabaseHandler->error, 'dberror');
                            }
                            $success = 'Restored Successfully!';
                            session_destroy();
                            

                    }else{
                        if($version > PRODUCT_VERSION)
                        {
                            $txt = 'upgrade';
                        }else if($version < PRODUCT_VERSION)
                        {
                            $txt = 'downgrade';
                        }else{
                            $txt = 'upgrade/downgrade';
                        }
                        $error = 'The backup was generated for version '.$version.' of PHPMyLicense. Please '.$txt.' it first.';
                    }
                }
            }catch (Exception $e)
            {
                if($e->getCode() == 323)
                {
                    $error = 'Invalid Backup Password or Invalid Backup File Signature';
                }else{
                    $error = 'Unknown Error. Check the logs';
                    $LogHandler->write('Error in PhpZip - '.$e->getMessage(), 'systemerror');
                }
            }



        }else{
            $error = 'Unknown Error. Try again.';
        }
    }else{
        $error = 'File uploaded is not a Zip File';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Giovanne Oliveira">
    <link rel="shortcut icon" href="<?php echo ASSETS_URL;?>/img/favicon.png">

    <title><?php echo PRODUCT_NAME;?> Restore system from Backup</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo ASSETS_URL;?>/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL;?>/css/bootstrap-reset.css" rel="stylesheet">
    <!--external css-->
    <link href="<?php echo ASSETS_URL;?>/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="<?php echo ASSETS_URL;?>/jquery-easy-pie-chart/jquery.easy-pie-chart.css" rel="stylesheet" type="text/css" media="screen"/>
    <link rel="stylesheet" href="<?php echo ASSETS_URL;?>/css/owl.carousel.css" type="text/css">

    <!-- Custom styles for this template -->

    <link href="<?php echo ASSETS_URL;?>/css/style.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL;?>/css/style-responsive.css" rel="stylesheet" />

    <link rel="stylesheet" type="text/css" href="<?php echo ASSETS_URL;?>/bootstrap-fileupload/bootstrap-fileupload.css" />



    <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
    <!--[if lt IE 9]>
    <script src="<?php echo ASSETS_URL;?>/js/html5shiv.js"></script>
    <script src="<?php echo ASSETS_URL;?>/js/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<section id="container" >
    <!--header start-->
    <header class="header white-bg">
        <div class="sidebar-toggle-box">
            <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
        </div>
        <!--logo start-->
        <a href="index.html" class="logo">CNK<span>ProtectPHP</span></a>
        <!--logo end-->
        <div class="nav notify-row" id="top_menu">

        </div>
        <div class="top-nav ">

            <?php include 'assets/inc/topbar.php';?>
        </div>
    </header>
    <!--header end-->
    <!--sidebar start-->
    <?php include 'assets/inc/sidebar.php';?>
    <!--sidebar end-->
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper">

            <div class="row">
                <div class="col-lg-12">
                    <!--work progress start-->
                    <section class="panel">
                        <div class="panel-body">
                            <?php if(isset($error))
                            {?>
                                <div class="alert alert-block alert-danger fade in">
                                <button data-dismiss="alert" class="close close-sm" type="button">
                                    <i class="fa fa-times"></i>
                                </button>
                                <strong>Error!</strong> <?php echo $error;?>.
                                </div><?php }?>
                            <?php if(isset($success))
                            {?>
                                <div class="alert alert-block alert-success fade in">
                                <button data-dismiss="alert" class="close close-sm" type="button">
                                    <i class="fa fa-times"></i>
                                </button>
                                <strong>Yay!</strong> <?php echo $success;?>.
                                </div><?php }?>
                            <form method="post" name="frmUpload" id="frmUpload" enctype="multipart/form-data">

                                <div style="padding:50px;padding-top:50px;padding-bottom:100px" class="text-center">
                                    <i class="fa fa-upload" style="color:#676767;font-size:10em"></i>

                                    <h2>Restore System's Backup</h2>

                                    <div class="form-group">

                                        <div class="fileupload fileupload-new" data-provides="fileupload">
                                                <span class="btn btn-white btn-file">
                                                <span class="fileupload-new"><i class="fa fa-paper-clip"></i> Select backup file</span>
                                                <span class="fileupload-exists"><i class="fa fa-undo"></i> Change</span>
                                                <input type="file" name="zipfile" id="zipfile" class="default">
                                                </span>
                                            <span class="fileupload-preview" style="margin-left:5px;"></span>
                                            <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none; margin-left:5px;"></a>
                                        </div>

                                    </div>
                                    <div class="form-group">
                                        <input type="hidden" value="" name="hdnBackupPassword" id="hdnBackupPassword">
                                        <button type="button" id="btnSubmit" name="btnSend" class="btn btn-primary">Restore Backup</button>
                                    </div>
                                    <span class="inline-help">
                    Search your <strong>bkp</strong> file and upload it!
                    </span>

                                    <div class="clearfix"></div>
                                    <span class="inline-help text-warning">
                    <strong>Careful!</strong> This operation will overwrite all your current database and configuration files.
                    </span>
                                </div>


                            </form>
                        </div>
                    </section>
                    <!--work progress end-->
                </div>

        </section>
    </section>
    <!--main content end-->

    <!--footer start-->
    <?php include 'assets/inc/footer.php';?>
    <!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<script src="<?php echo ASSETS_URL;?>/js/jquery.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/bootstrap.min.js"></script>
<script class="include" type="text/javascript" src="<?php echo ASSETS_URL;?>/js/jquery.dcjqaccordion.2.7.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/jquery.scrollTo.min.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/jquery.nicescroll.js" type="text/javascript"></script>
<script src="<?php echo ASSETS_URL;?>/js/jquery.sparkline.js" type="text/javascript"></script>
<script src="<?php echo ASSETS_URL;?>/jquery-easy-pie-chart/jquery.easy-pie-chart.js"></script>
<script src="<?php echo ASSETS_URL;?>/js/owl.carousel.js" ></script>
<script src="<?php echo ASSETS_URL;?>/js/jquery.customSelect.min.js" ></script>
<script src="<?php echo ASSETS_URL;?>/js/respond.min.js" ></script>

<!--common script for all pages-->
<script src="<?php echo ASSETS_URL;?>/js/common-scripts.js"></script>

<!--script for this page-->
<script type="text/javascript" src="<?php echo ASSETS_URL;?>/bootstrap-fileupload/bootstrap-fileupload.js"></script>

<!-- SWAL -->
<script src="//cdn.jsdelivr.net/sweetalert2/6.5.6/sweetalert2.min.js"></script>
<link rel="stylesheet" href="//cdn.jsdelivr.net/sweetalert2/6.5.6/sweetalert2.min.css">
<script>
    /*$('#frmUpload').submit(function(event) {

        event.preventDefault(); //this will prevent the default submit

        swal({
            title: 'Enter the backup file\'s password',
            input: 'password',
            inputAttributes: {
                'maxlength': 32,
                'autocapitalize': 'off',
                'autocorrect': 'off'
            },
            inputValidator: function (value) {
                return new Promise(function (resolve, reject) {
                    if (value) {
                        resolve()
                    } else {
                        reject('You need to input something!')
                    }
                })
            }
        }).then(function (password) {
            if (password) {
                $("#hdnBackupPassword").val(password);
                $('#frmUpload').submit();
                //$(this).unbind('submit').submit(); // continue the submit unbind preventDefault
            }
        })


    })*/

    $('#btnSubmit').click(function(event) {

        //event.preventDefault(); //this will prevent the default submit

        swal({
            title: 'Enter the backup file\'s password',
            input: 'password',
            inputAttributes: {
                'maxlength': 32,
                'autocapitalize': 'off',
                'autocorrect': 'off'
            },
            inputValidator: function (value) {
                return new Promise(function (resolve, reject) {
                    if (value) {
                        resolve()
                    } else {
                        reject('You need to input something!')
                    }
                })
            }
        }).then(function (password) {
            if (password) {
                $("#hdnBackupPassword").val(password);
                $("#btnSubmit").html('Processing...');
                $("#btnSubmit").addClass('disabled');
                $('#frmUpload').submit();
                //$(this).unbind('submit').submit(); // continue the submit unbind preventDefault
            }
        })


    })

</script>

<?php
include 'assets/inc/changepwd.php';
?>

</body>
</html>
