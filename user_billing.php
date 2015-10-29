<?php
require_once 'includes/header.inc.php';
	
$rateTypesList = array(Bills::CONTINUOUS_RATE => "Continuous", Bills::MONTHLY_RATE => "Monthly");

$userToBill = new User($sqlDataBase);
$bills = new Bills($sqlDataBase);
$userCfop = new UserCfop($sqlDataBase);

switch ($accessControl->GetPermissionLevel($authenticate->getAuthenticatedUser()->GetUserId(), AccessControl::RESOURCE_PAGE, $pages->GetPageId('User Billing'))) {
    case AccessControl::PERM_ADMIN:
        $selectableUsersList = $userToBill->GetAllUsers();
        break;
    case AccessControl::PERM_SUPERVISOR:
        $selectableUsersList = $userToBill->GetGroupUsers($authenticate->getAuthenticatedUser()->GetGroupId());
        break;
    case AccessControl::PERM_ALLOW:
        $selectableUsersList = array();
        break;
}

if (isset($_POST['monthSelected'])) {
    list($month, $year) = explode(" ", $_POST['monthSelected']);
} else {
    $month = Date("n");
    $year = Date("Y");
}

if (isset($_POST['selectedUser'])) {
    $userToBill->LoadUser($_POST['selectedUser']);
} else {
    $userToBill->LoadUser($authenticate->getAuthenticatedUser()->GetUserId());
}

?>
<div class="alert alert-info">
    <h4>User Billing</h4>

    <p>Your usage billing is reported bellow, billing is charged on a monthly cycle</p>

    <p>Please report any inconsistencies you find.</p>
</div>
<div class="row-fluid">
<form action="./index.php?view=<?php echo $pages->GetPageId('User Billing'); ?>" method=POST class="form-inline well">

            <div class="form-group ">
            <div class="col-sm-3">
                <select name="selectedUser" class="form-control">
                    <?php
                    if (empty($selectableUsersList)) {
                        echo "<option value=" . $userToBill->GetUserId() . ">" . $userToBill->GetUserName() . "</option>";
                    } else {
                        foreach ($selectableUsersList as $id => $availUser) {
                            echo "<option value=" . $availUser['id'];
                            if ($userToBill->GetUserId() == $availUser['id']) {
                                echo " SELECTED ";
                            }
                            echo ">" . $availUser['user_name'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            </div>
    <div class="form-group">
            <div class="col-sm-3">
                <select name="monthSelected" class="form-control">
                    <?php
                    $availableBillingMonths = $bills->GetAvailableBillingMonths();
                    foreach ($availableBillingMonths as $id => $charge) {
                        echo "<option value=\"" . $charge['month'] . " " . $charge['year'] . "\"";
                        if ($charge['month'] == $month && $charge['year'] == $year) {
                            echo " SELECTED";
                        }
                        echo ">" . $charge['mon_yr'] . "</option>";
                    }

                    ?>
                </select>
            </div>
    <div class="form-group">
            <div class="col-sm-2">
                <input type="submit" name="selectUserDate"
                       value="View Billing" class="btn btn-primary">
            </div>
        </div>
            <div class="col-sm-4">
            </div>

</form>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4>Account Information</h4>
    </div>
    <div class="panel-body">
        <table class="table table-striped">
            <?php
            echo "<tr><th>Netid: </th><td>" . $userToBill->GetUserName() . "</td></tr>";
            echo "<tr><th>Name:</th><td>" . $userToBill->GetFirst() . " " . $userToBill->GetLast() . "</td></tr>";
            echo "<tr><th>E-Mail:</th><td>" . $userToBill->GetEmail() . "</td></tr>";

            echo "</table>
    </div></div>";

            foreach ($rateTypesList as $rateTypeId => $rateTypeName) {
                echo "<div class=\"panel panel-default\">
            <div class=\"panel-heading\">
            <h4>" . $rateTypeName . " Billing</h4>
            </div>
            <div class=\"panel-body\">
    <table class=\"table table-striped table-hover\">
        <tr>
            <th>Session id</th>
            <th>Date</th>
            <th>CFOP</th>
            <th>Equipment</th>
            <th>Usage(hrs)</th>
            <th>Rate</th>
            <th>Total</th>
        </tr>";

                $i = 0;
                $bills->setUserId($userToBill->GetUserId());
                if ($rateTypeId == Bills::MONTHLY_RATE) {
                    $bills->setGroupBy(Bills::GROUP_DEVICE);
                }
                $monthCharges = $bills->GetMonthCharges($year, $month, $rateTypeId);


                foreach ($monthCharges as $id => $charge) {
                    $rate = $charge['rate'];

                    echo "<tr>
                    <td>" . $charge['id'] . "</td>
                    <td>" . $charge['start'] . "</td>
                    <td>" . UserCfop::formatCfop($charge['cfop']) . "</td>
                    <td>" . $charge['full_device_name'] . "</td>
                    <td>" . round(($charge['elapsed'] / 60), 2) . "</td>
                    <td>" . round(($rate * 60), 2) . "</td>
                    <td>$" . round($bills->CalcTotal($charge['elapsed'],$rateTypeId,$rate,$charge['min_use_time']),2) . "</td>
                    </tr>";
                }
                echo "</table>
            </div>
            </div>";
            }
            ?>
        </div>
        <?php
require_once 'includes/footer.inc.php';
?>
