<?php
/**
  * Project: CnkProtectPHP
* User: CnkPoncol.com
 */

include_once 'system/autoloader.php';
$Logged = $Tools->CheckIfLogged($_SESSION);
if (!$Logged) {
    header("Location: login.php?go=" . base64_encode($_SERVER["REQUEST_URI"]) . "");
}

$sql = "SELECT * FROM settings";
$query = $DatabaseHandler->query($sql);
if ($query) {
    $data = $query->fetch_array();
    $configurations = json_decode($data['configurations'], true);
    $purchasecode = $data['purchasecode'];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Giovanne Oliveira">
    <link rel="shortcut icon" href="<?php echo ASSETS_URL; ?>/img/favicon.png">

    <title><?php echo PRODUCT_NAME; ?> Settings</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo ASSETS_URL; ?>/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/css/bootstrap-reset.css" rel="stylesheet">
    <!--external css-->
    <link href="<?php echo ASSETS_URL; ?>/font-awesome/css/font-awesome.css" rel="stylesheet"/>
    <link href="<?php echo ASSETS_URL; ?>/jquery-easy-pie-chart/jquery.easy-pie-chart.css" rel="stylesheet"
          type="text/css" media="screen"/>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/owl.carousel.css" type="text/css">

    <!--right slidebar-->
    <link href="<?php echo ASSETS_URL; ?>/css/slidebars.css" rel="stylesheet">

    <!-- Custom styles for this template -->

    <link href="<?php echo ASSETS_URL; ?>/css/style.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/css/style-responsive.css" rel="stylesheet"/>


    <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
    <!--[if lt IE 9]>
    <script src="<?php echo ASSETS_URL; ?>/js/html5shiv.js"></script>
    <script src="<?php echo ASSETS_URL; ?>/js/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<section id="container">
    <!--header start-->
    <header class="header white-bg">
        <div class="sidebar-toggle-box">
            <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
        </div>
        <!--logo start-->
        <a href="" class="logo">CNK<span>ProtectPHP</span></a>
        <!--logo end-->
        <div class="nav notify-row" id="top_menu">

        </div>
        <div class="top-nav ">

            <?php include 'assets/inc/topbar.php'; ?>
        </div>
    </header>
    <!--header end-->
    <!--sidebar start-->
    <?php include 'assets/inc/sidebar.php'; ?>
    <!--sidebar end-->
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper">
            <!--state overview start-->
            <?php include 'assets/inc/overview.php'; ?>
            <!--state overview end-->
            <div class="row">
                <div class="col-md-12">
                    <!--work progress start-->
                    <section class="panel">
                        <header class="panel-heading">
                            Settings
                        </header>
                        <div class="panel-body">
                            <form class="form-horizontal tasi-form" id="frmSettings">
                                <!--<div class="form-group">
                                    <label class="col-sm-2 col-sm-2 control-label">Two Factor Authentication</label>
                                    <div class="col-sm-10">
                                        <?php
                                if ($_SESSION['tfa'] == true) { ?>
                                        <a  id="btn2FAToggle" onClick="javascript:DeactivateTFA();" class="btn btn-danger"><i class="fa fa-lock"></i> <span id="btn2FAToggleText">Deactivate Two Factor Authentication</span></a>
                                        <span class="help-block">Deactivate Two Factor Authentication for the logged user.</span>
                             <?php } else { ?>
                             
                                        <a  id="btn2FAToggle" onClick="javascript:Activate2FA();" class="btn btn-success"><i class="fa fa-lock"></i> <span id="btn2FAToggleText">Activate Two Factor Authentication</span></a>
                                        <span class="help-block">Activate or deactivate Two Factor Authentication for the logged user.</span>
                                        
                                        <?php }
                                ?>
                                        
                                    </div>
                                </div>-->
                                
                                <div class="form-group">
                                    <label class="col-sm-2 col-sm-2 control-label">Download System Backup</label>
                                    <div class="col-sm-10">
                                        <a id="btnDownloadBackup" onClick="javascript:DownloadBackupFile();"
                                           class="btn btn-info"><i class="fa fa-download"></i> <span
                                                    id="btnDownloadText">Download System Backup</span></a> <a
                                                id="btnRestoreBackup" href="restore.php" class="btn btn-info"><i
                                                    class="fa fa-upload"></i> <span id="btnRestoreText">Restore System Backup</span></a>
                                        <span class="help-block">CnkProtectPHP will encrypt your Backup with AES-256 bits Winzip-Compatible Zip File.</span>
                                    </div>
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
    <?php include 'assets/inc/footer.php'; ?>
    <!--footer end-->
</section>

<div aria-hidden="true" aria-labelledby="mdlRSAKeys" role="dialog" tabindex="-1" id="mdlRSAKeys" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="mdlRSAKeys_Header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h4 class="modal-title">RSA Key</h4>
            </div>
            <div class="modal-body">

                <form class="form-horizontal" id="frmSubmitRSAKey" role="form">
                    <div class="form-group">
                        <label for="inputKey" class="col-lg-2 col-sm-2 control-label">RSA Key</label>
                        <div class="col-lg-10">
                            <textarea class="form-control" id="RSAKey" rows="20" placeholder="RSA Key">

                            </textarea>
                        </div>
                    </div>
                </form>

            </div>

        </div>
    </div>
</div>

<!-- js placed at the end of the document so the pages load faster -->
<script src="<?php echo ASSETS_URL; ?>/js/jquery.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/bootstrap.min.js"></script>
<script class="include" type="text/javascript" src="<?php echo ASSETS_URL; ?>/js/jquery.dcjqaccordion.2.7.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/jquery.scrollTo.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/jquery.nicescroll.js" type="text/javascript"></script>
<script src="<?php echo ASSETS_URL; ?>/js/jquery.sparkline.js" type="text/javascript"></script>
<script src="<?php echo ASSETS_URL; ?>/jquery-easy-pie-chart/jquery.easy-pie-chart.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/owl.carousel.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/jquery.customSelect.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/respond.min.js"></script>

<!--right slidebar-->
<script src="<?php echo ASSETS_URL; ?>/js/slidebars.min.js"></script>

<!--common script for all pages-->
<script src="<?php echo ASSETS_URL; ?>/js/common-scripts.js"></script>

<!--custom switch-->
<script src="<?php echo ASSETS_URL; ?>/js/bootstrap-switch.js"></script>

<!-- SWAL -->
<script src="//cdn.jsdelivr.net/sweetalert2/6.5.6/sweetalert2.min.js"></script>
<link rel="stylesheet" href="//cdn.jsdelivr.net/sweetalert2/6.5.6/sweetalert2.min.css">

<?php
include 'assets/inc/changepwd.php';
?>

<script>
    //$("[data-toggle='switch']").wrap('<div class="switch" />').parent().bootstrapSwitch();

    /*function DeactivateTFA()
    {
        swal({
            title: "Deactivate Two Factor Authentication",
            text: "Are you sure of this operation? Your account will be vulnerable.",
            type: "warning",
            showCancelButton: true,
            closeOnConfirm: false,
            confirmButtonText: "Yes, I'm sure.",
            cancelButtonText: "No, get me out of here!",
            confirmButtonColor: "#808080",
            cancelButtonColor: "#FF0000",
            showLoaderOnConfirm: true,
        }).then(function (password) {
            if (password) {
                $.ajax({
                    type: "GET",
                    url: "<?php echo BASE_URL;?>/ajax/?handler=toggletfa&act=deactivate",
                    dataType: "json",
                    success: function (result) {
                        if(result.status == 200)
                        {
                            swal("Two Factor Auth", "Deactivated Successfully", "success");
                            setTimeout(function(){
                                location.reload();
                            }, 2500);
                        }else{
                            $("#btnDownloadBackup").removeClass('disabled');
                            $("#btnDownloadBackup").html('Download System Backup');
                            toastr[result.message.type](result.message.text, result.message.header);
                        }
                    },
                    beforeSend: function () {
                        $("#btnDownloadBackup").addClass('disabled');
                        $("#btnDownloadBackup").html('Processing...');
                    },
                    error: function () {
                        $("#btnDownloadBackup").removeClass('disabled');
                        $("#btnDownloadBackup").html('Download System Backup');
                        toastr['error']('Unknown error', 'Oops!');
                    }
                });
            }
        })
    }*/
    function DownloadBackupFile() {

        swal({
            title: 'Enter a password for the backup file',
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
                $.ajax({
                    type: "GET",
                    url: "<?php echo BASE_URL;?>/ajax/?handler=sysbkp&pwd=" + password,
                    dataType: "json",
                    success: function (result) {
                        if (result.status == 200) {
                            $("#btnDownloadBackup").html('Generated');
                            $("#btnDownloadBackup").removeClass('btn-info');
                            $("#btnDownloadBackup").addClass('btn-success');
                            $(window.document.location).attr('href', result.url);
                        } else {
                            $("#btnDownloadBackup").removeClass('disabled');
                            $("#btnDownloadBackup").html('Download System Backup');
                            toastr[result.message.type](result.message.text, result.message.header);
                        }
                    },
                    beforeSend: function () {
                        $("#btnDownloadBackup").addClass('disabled');
                        $("#btnDownloadBackup").html('Processing...');
                    },
                    error: function () {
                        $("#btnDownloadBackup").removeClass('disabled');
                        $("#btnDownloadBackup").html('Download System Backup');
                        toastr['error']('Unknown error', 'Oops!');
                    }
                });
            }
        })
    }

    function generateRSAKey() {

        swal({
            title: "Are you sure?",
            text: "It will overwrite the old one and may break integrations.",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Yes, keep going!",
            showLoaderOnConfirm: true,
            preConfirm: function(){
                $.ajax({
                    type: "POST",
                    data: {
                        token: '<?php echo $TOTP->generateCode();?>',
                        handler: 'genRSAKeypair'
                    },

                    url: "ajax/",
                    dataType: "json",
                    success: function (result) {
                        swal({
                            title: result.message.header,
                            text: result.message.text,
                            type: result.message.type,
                            showCancelButton: false,
                            confirmButtonText: 'Understood'
                        });
                    },
                    error: function () {
                        swal({
                            title: 'Oops!',
                            text: 'Unknown Error. Contact Support Team.',
                            type: 'danger',
                            showCancelButton: false,
                            confirmButtonText: 'Understood'
                        });
                    }
                })
                .done
            },
            allowOutsideClick: () => !Swal.isLoading()
        });

    }

    function getRSAKey(scope, button) {
        $.ajax({
            type: "POST",
            data: {
                scope: scope,
                token: '<?php echo $TOTP->generateCode();?>',
                handler: 'getRSAKey'
            },

            url: "ajax/",
            dataType: "json",
            success: function (result) {

                if (result.status == 200) {
                    button.removeClass('disabled');
                    if (scope == 'private') {
                        $("#mdlRSAKeys_Header").html('RSA Private Key');
                    } else if (scope == 'public') {
                        $("#mdlRSAKeys_Header").html('RSA Public Key');
                    }
                    $("#RSAKey").html(result.resource);
                    $('#mdlRSAKeys').modal('show');
                } else {
                    button.removeClass('disabled');
                    toastr[result.message.type](result.message.text, result.message.header);
                }


            },
            beforeSend: function () {

                button.addClass('disabled');

            },
            error: function () {
                button.removeClass('disabled');
                button.html('Error!');
                button.addClass('btn-danger');
                toastr['error']("Unknown Error. Contact the support team.", "Oops!");
            }
        });
    }


    function GetCryptographicKeyFromAPI() {
        $.ajax({

            type: "GET",

            url: "<?php echo PHPMYLICENSE_API;?>/public/generatekey",
            dataType: "json",
            success: function (result) {

                if (result.status == 200) {
                    $("#encryption_key").removeClass('spinner');
                    $("#encryption_key").prop('disabled', false);
                    $("#encryption_key").val(result.key);
                } else {
                    $("#encryption_key").removeClass('spinner');
                    $("#encryption_key").prop('disabled', false);
                    toastr[result.message.type](result.message.text, result.message.header);
                }

            },
            beforeSend: function () {

                $("#encryption_key").addClass('spinner');
                $("#encryption_key").prop('disabled', true);
            },
            error: function () {
                $("#encryption_key").removeClass('spinner');
                $("#encryption_key").prop('disabled', false);
                toastr['error']('Unknown error', 'Oops!');
            }
        });
    }

    $("#returnencrypted").on("switch-change", function (event, data) {
        if (data.value == true) {
            $("#encryptionkeycontainer").slideDown();
        } else {
            $("#encryptionkeycontainer").slideUp();
        }
    });
    $("#signresponse").on("switch-change", function (event, data) {
        if (data.value == true) {
            $("#signresponsecontainer").slideDown();
        } else {
            $("#signresponsecontainer").slideUp();
        }
    });
    $("#btnUpdate").click(function (e) {

        var serialmask = $("#mask").val();
        var returndata = $("#returndata").bootstrapSwitch('status');
        var returnencrypted = $("#returnencrypted").bootstrapSwitch('status');
        var encryption_key = $("#encryption_key").val();
        var updatechannel = $("#updatechannel").val();
        var signresponse = $("#signresponse").bootstrapSwitch('status');
        var signaturetype = $("#signaturetype").val();
        var btn = $("#btnText");
        var spinner = $("#spinner");
        $.ajax({

            type: "POST",
            data: {
                serialmask: serialmask,
                returndata: returndata,
                returnencrypted: returnencrypted,
                encryption_key: encryption_key,
                updatechannel: updatechannel,
                signresponse: signresponse,
                signaturetype: signaturetype,
                token: '<?php echo $TOTP->generateCode();?>',
                handler: 'updatesettings'
            },

            url: "ajax/",
            dataType: "json",
            success: function (result) {

                if (result.status == 200) {
                    spinner.removeClass('fa-spin');
                    toastr[result.message.type](result.message.text, result.message.header);
                    btn.html('Updated.');
                } else {
                    btn.removeClass('disabled');
                    spinner.removeClass('fa-spin');
                    toastr[result.message.type](result.message.text, result.message.header);
                    btn.html('Update Settings');
                }


            },
            beforeSend: function () {

                btn.html('Updating...');
                btn.addClass('disabled');
                spinner.addClass('fa-spin');

            },
            error: function () {
                btn.removeClass('disabled');
                spinner.removeClass('fa-spin');
                btn.html('Error!');
                btn.addClass('btn-danger');
                toastr['error']("Unknown Error. Contact the support team.", "Oops!");
            }
        });


    });

</script>

</body>
</html>

