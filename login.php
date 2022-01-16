<?php
/**
 * Project: CnkProtect
* User: CnkPoncol.com
 */
include 'system/autoloader.php';
if (isset($_GET["go"])) {
    $go = base64_decode($_GET['go']);
} else {
    $go = './';
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

    <title><?php echo PRODUCT_NAME; ?> Login</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo ASSETS_URL; ?>/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/css/bootstrap-reset.css" rel="stylesheet">
    <!--external css-->
    <link href="<?php echo ASSETS_URL; ?>/font-awesome/css/font-awesome.css" rel="stylesheet"/>
    <!-- Custom styles for this template -->
    <link href="<?php echo ASSETS_URL; ?>/css/style.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/css/style-responsive.css" rel="stylesheet"/>
    <!--toastr-->
    <link href="<?php echo ASSETS_URL; ?>/toastr-master/toastr.css" rel="stylesheet" type="text/css"/>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.0/ladda-themeless.min.css" rel="stylesheet" type="text/css"/>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
    <!--[if lt IE 9]>
    <script src="<?php echo ASSETS_URL;?>/js/html5shiv.js"></script>
    <script src="<?php echo ASSETS_URL;?>/js/respond.min.js"></script>
    <![endif]-->
    <style>
        .custom-txtbox{
            text-align:center;
            width:20%;
            margin:0 auto;
        }
    </style>
</head>

<body class="login-body">

<div class="container">

    <form class="form-signin" id="frmLogin">
        <h2 class="form-signin-heading"><?php echo PRODUCT_NAME; ?> LOGIN</h2>

        <div class="login-wrap">
            <input type="text" class="form-control" id="txtUser" placeholder="User ID" autofocus>
            <input type="password" class="form-control" id="txtPass" placeholder="Password">
            <label class="checkbox">
                <span class="pull-right">
                   

                </span>
            </label>
            <button class="btn btn-lg btn-login ladda-button btn-block" id="btnDoLogin" data-style="zoom-in" type="submit"><span class="ladda-label">Sign in</span></button>


        </div>
    </form>


    


</div>


<!-- js placed at the end of the document so the pages load faster -->
<script src="<?php echo ASSETS_URL; ?>/js/jquery.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/bootstrap.min.js"></script>
<!--toastr-->
<script src="<?php echo ASSETS_URL; ?>/toastr-master/toastr.js"></script>

<script type="text/javascript" src="<?php echo ASSETS_URL; ?>/bootstrap-inputmask/bootstrap-inputmask.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.0/spin.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.0/ladda.min.js"></script>


</body>
</html>

<script>
    $(function () {
        //$("#frmLogin").jCryption();
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "3000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
        $("#frmLogin").submit(function (e) {
            e.preventDefault();

            var username = $("#txtUser").val();
            var password = $("#txtPass").val();
            var l = Ladda.create( document.querySelector( '#btnDoLogin' ) );
            $.ajax({

                type: "POST",
                data: {
                    user: username,
                    pass: password,
                    handler: 'login'
                },

                url: "ajax/",
                dataType: "json",
                success: function (result) {

                    if (result.status == 200) {
                        if(result.twofactor == true)
                        {
                            Create2FADialogbox(result.seed);
                        }else{
                            $(window.document.location).attr('href', '<?php echo $go;?>');
                        }

                    } else {
                        l.stop();
                        toastr[result.message.type](result.message.text, result.message.header);
                    }


                },
                beforeSend: function () {

                    l.start();

                },
                error: function () {
                    l.stop();
                    toastr['error']("Unknown Error. Contact the support team.", "Oops!");
                }
            });
            return false;
        });

        $("#frmRecoverPwd").submit(function (e) {
            e.preventDefault();

            var email = $("#recoverEmail").val();
            var btn = $("#btnSubmitResetPwd");
            $.ajax({

                type: "POST",
                data: {
                    email: email,
                    handler: 'resetpwd'
                },

                url: "ajax/",
                dataType: "json",
                success: function (result) {

                    if (result.status == 200) {
                        btn.html('Processed.');
                        toastr[result.message.type](result.message.text, result.message.header);
                    } else {
                        btn.html('Submit');
                        toastr[result.message.type](result.message.text, result.message.header);
                        btn.removeClass('disabled');
                    }


                },
                beforeSend: function () {

                    btn.html('Processing...');
                    btn.addClass('disabled');

                },
                error: function () {
                    btn.removeClass('disabled');
                    btn.html('Submit');
                    toastr[error]("Unknown Error. Contact the support team.", "Oops!");
                }
            });
            return false;
        });

        $("#frmSubmitGAuth").submit(function (e) {
            e.preventDefault();

            var l = Ladda.create( document.querySelector( '#btnSubmit2FA' ) );
            $.ajax({

                type: "POST",
                data: {
                    token: $("#GAuthToken").val(),
                    seed:$("#hdnSeed").val(),
                    handler: '2fa-authenticate'
                },

                url: "ajax/",
                dataType: "json",
                success: function (result) {

                    if (result.status == 200) {
                        $(window.document.location).attr('href', '<?php echo $go;?>');
                    } else {
                        l.stop();
                        toastr[result.message.type](result.message.text, result.message.header);
                    }


                },
                beforeSend: function () {

                    l.start();

                },
                error: function () {
                    l.stop();
                    toastr['error']("Unknown Error. Contact the support team.", "Oops!");
                }
            });
            return false;
        });


    });

    function Create2FADialogbox(s)
    {
        $("#mdl2FA").modal('show');
        $("#hdnSeed").val(s);
    }

</script>
</body>
</html>