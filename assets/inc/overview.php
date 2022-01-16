<?php
/**
 * Project: CnkProtect
 * User: CnkPoncol.com
 */
?>
<div class="row state-overview">

    <?php
    //$query = $DatabaseHandler->query("SELECT purchasecode FROM settings");
    //$data = $query->fetch_array();
    if(!defined('OFFLINE_MODE'))
    {
        $latestversion = $Tools->GetLatestVersion(PURCHASE_CODE);
        if($latestversion > PRODUCT_VERSION)
        {?><div class="alert alert-warning fade in">
            <button data-dismiss="alert" class="close close-sm" type="button">
                <i class="fa fa-times"></i>
            </button>
            <strong>Hey!</strong> An update to the version <?php echo $latestversion;?> is avaiable! Go to <a href="update.php">Update Center</a> to Install.
            </div>
        <?php }?>
        <?php
        if(isset($_SESSION['admin_override']))
        {
            ?>
            <div class="alert alert-danger fade in">
                <button data-dismiss="alert" class="close close-sm" type="button">
                    <i class="fa fa-times"></i>
                </button>
                <b style="text-align:center"><center>ADMIN OVERRIDE - SUPPORT MODE - AUTHORIZED USERS ONLY</center></b>
            </div>
            <?php

        }

    }else {
        ?>
        <div class="alert alert-danger fade in" style="text-align: center;">
            <button data-dismiss="alert" class="close close-sm" type="button">
                <i class="fa fa-times"></i>
            </button>
            <b>You are in Offline Mode.</b><br> Your Offline key was issued in :keyissue: and is valid until :expiry:
        </div>
        <?php
    }
    ?>
    <div class="col-lg-4 col-sm-6">
        <section class="panel">
            <div class="symbol terques">
                <i class="fa fa-key"></i>
            </div>
            <div class="value">
                <h1>
                    <?php
                    $sql = "SELECT id FROM licenses";
                    $query = $DatabaseHandler->query($sql);
                    if($query)
                    {
                        echo $query->num_rows;
                    }else{
                        echo 'ERROR';
                    }
                    ?>
                </h1>
                <p>Licenses</p>
            </div>
        </section>
    </div>
    <div class="col-lg-4 col-sm-6">
        <section class="panel">
            <div class="symbol red">
                <i class="fa fa-tags"></i>
            </div>
            <div class="value">
                <h1>
                    <?php
                    $sql = "SELECT id FROM products";
                    $query = $DatabaseHandler->query($sql);
                    if($query)
                    {
                        echo $query->num_rows;
                    }else{
                        echo 'ERROR';
                    }

                    ?>
                </h1>
                <p>Products</p>
            </div>
        </section>
    </div>
    <div class="col-lg-4 col-sm-6">
        <section class="panel">
            <div class="symbol blue">
                <i class="fa fa-cogs"></i>
            </div>
            <div class="value">
                <h1>
                    <?php echo PRODUCT_VERSION;?>
                </h1>
                <p>System Version</p>
            </div>
        </section>
    </div>

</div>