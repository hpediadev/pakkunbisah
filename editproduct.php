<?php
/**
 * Project: CnkProtectPHP
* User: CnkPoncol.com
 */



include_once 'system/autoloader.php';
$Logged = $Tools->CheckIfLogged($_SESSION);
if(!$Logged)
{
    header("Location: login.php?go=".base64_encode($_SERVER["REQUEST_URI"])."");
}


if(isset($_GET['d']))
{
    $id = base64_decode($_GET['d']);
    $sql = "SELECT * FROM products WHERE id = '$id'";
    $query = $DatabaseHandler->query($sql);
    $data = $query->fetch_array();

}else{
    header("Location: products.php");
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

    <title><?php echo PRODUCT_NAME;?> Edit Product</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo ASSETS_URL;?>/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL;?>/css/bootstrap-reset.css" rel="stylesheet">
    <!--external css-->
    <link href="<?php echo ASSETS_URL;?>/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <!--toastr-->
    <link href="<?php echo ASSETS_URL;?>/toastr-master/toastr.css" rel="stylesheet" type="text/css" />

    <!-- Custom styles for this template -->

    <link href="<?php echo ASSETS_URL;?>/css/style.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL;?>/css/style-responsive.css" rel="stylesheet" />

    <link rel="stylesheet" type="text/css" href="<?php echo ASSETS_URL;?>/bootstrap-datepicker/css/datepicker.css" />


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
        <a href="./" class="logo">CNK<span>ProtectPHP</span></a>
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
                        <div class="panel-body progress-panel">
                            <div class="task-progress">
                                <h1>Edit this Product</h1>
                                <p> Correct all informations you need.</p>
                            </div>
                        </div>
                        <form role="form" id="frmEditProduct">
                            <div class="form-group">
                                <label for="txtDomain">ID</label>
                                <input type="text" id="txtId" disabled class="form-control" value="<?php echo $data['id'];?>" placeholder="ID">
                            </div>
                            <div class="form-group">
                                <label for="txtFullName">Full Name</label>
                                <input type="text" id="txtFullName" class="form-control" value="<?php echo $data['fullname'];?>" placeholder="Full Name">
                            </div>
                            <div class="form-group">
                                <label for="txtShortName">Short Name</label>
                                <input type="text" class="form-control" id="txtShortName" value="<?php echo $data['shortname'];?>" placeholder="Short Name">
                            </div>
                            <div class="form-group">
                                <label for="exampleInputFile">Sandbox</label>
                                <select class="form-control m-bot15" id="slcSandbox">
                                    <option value="1" <?php if($data['sandbox'] == 1){echo 'selected';}?>>Active</option>
                                    <option value="0" <?php if($data['sandbox'] == 0){echo 'selected';}?>>Inactive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="txtPrice">Price</label>
                                <input type="text" class="form-control" id="txtPrice" value="<?php echo $data['price'];?>" placeholder="Price">
                            </div>

                            <div class="form-group">
                                <label for="exampleInputFile">Currency</label>
                                <select class="form-control m-bot15" id="slcCurrency">
                                    <?php
                                        $currencies = json_decode(file_get_contents(ASSETS_URL.'/inc/currencies.json'));
                                        foreach($currencies as $c)
                                        {
                                            if($data['currency'] == $c->code)
                                            {
                                                echo "<option value=\"".$c->code."\" selected>".$c->name."</option>";
                                            }else{
                                                echo "<option value=\"".$c->code."\">".$c->name."</option>";
                                            }

                                        }

                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="exampleInputFile">Renewal Cycle</label>
                                <select class="form-control m-bot15" id="slcRenewalCycle">
                                    <option value="1" <?php if($data['renewalcycle'] == 1){echo 'selected';}?>>Weekly</option>
                                    <option value="2" <?php if($data['renewalcycle'] == 2){echo 'selected';}?>>Montly</option>
                                    <option value="3" <?php if($data['renewalcycle'] == 3){echo 'selected';}?>>Yearly</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="txtDomain">Trial Time</label>
                                <div id="spinner3">
                                    <div class="input-group" style="width:150px;">
                                        <input type="text" id="TrialTime" class="spinner-input form-control" value="<?php echo $data['trialtime'];?>" maxlength="3" readonly>
                                        <div class="spinner-buttons input-group-btn">
                                            <button type="button" class="btn btn-default spinner-up">
                                                <i class="fa fa-angle-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-default spinner-down">
                                                <i class="fa fa-angle-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" id="btnSubmit" class="btn btn-info">Update Product</button>
                        </form>
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
<script src="<?php echo ASSETS_URL;?>/js/jquery.customSelect.min.js" ></script>
<script src="<?php echo ASSETS_URL;?>/js/respond.min.js" ></script>

<!--right slidebar-->
<script src="<?php echo ASSETS_URL;?>/js/slidebars.min.js"></script>

<!--common script for all pages-->
<script src="<?php echo ASSETS_URL;?>/js/common-scripts.js"></script>

<!--script for this page-->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>

<script type="text/javascript" src="<?php echo ASSETS_URL;?>/fuelux/js/spinner.min.js"></script>
<?php
include 'assets/inc/changepwd.php';
?>

<script>
    $('#spinner3').spinner({value:0, min: 0, max: 30});
    $("#txtPrice").maskMoney();
    $(function () {
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
        $("#frmEditProduct").submit(function (e) {
            e.preventDefault();

            var id = $("#txtId").val();
            var fullname = $("#txtFullName").val();
            var shortname = $("#txtShortName").val();
            var sandbox = $("#slcSandbox").val();
            var price = $("#txtPrice").val();
            var currency = $("#slcCurrency").val();
            var renewalcycle = $("#slcRenewalCycle").val();
            var trialtime = $("#TrialTime").val();
            $.ajax({

                type: "POST",
                data: {
                    id:id,
                    fullname:fullname,
                    shortname:shortname,
                    sandbox:sandbox,
                    trialtime:trialtime,
                    price:price,
                    currency:currency,
                    renewalcycle:renewalcycle,
                    token:'<?php echo $TOTP->generateCode();?>',
                    handler: 'editproduct'
                },

                url: "ajax/",
                dataType: "json",
                success: function (result) {

                    if(result.status == 200)
                    {
                        $("#btnSubmit").html('Success...');
                        $(window.document.location).attr('href', 'products.php');
                    }else{
                        $("#btnSubmit").html('Update Product');
                        toastr[result.message.type](result.message.text, result.message.header);
                        $("#btnSubmit").removeClass('disabled');
                    }


                },
                beforeSend: function () {

                    $("#btnSubmit").html('Processing...');
                    $("#btnSubmit").addClass('disabled');

                },
                error: function () {
                    $("#btnSubmit").removeClass('disabled');
                    $("#btnSubmit").html('Edit Product');
                    toastr['error']("Unknown Error. Contact the support team.", "Oops!");
                }
            });
            return false;
        });
    });

</script>
</body>
</html>
