<?php
require_once 'includes/header.inc.php';
$device = new Device($sqlDataBase);
$rate = new Rate($sqlDataBase);
$rateTypes = $rate->GetRateTypes();

if (isset($_POST['device_option'])) {
    $device_option = $_POST['device_option'];
    if ($device_option != 'new') {
        $device->LoadDevice($device_option);
    }
}

if (isset($_POST['ModifyDevice'])) {
    $device->LoadDevice($_POST['device_id']);
    $device->SetShortName($_POST['dnsName']);
    $device->SetFullName($_POST['deviceName']);
    $device->SetLocation($_POST['location']);
    $device->SetDescription($_POST['description']);
    $device->SetStatus($_POST['status']);
    $device->UpdateDevice();
    $ratesArr = $_POST['ratesBox'];

    foreach ($ratesArr as $key => $value) {
        $rateId = $value;
        $rateValue = $_POST["rate-" . $value] / 60;
        $minTime = $_POST["mintime-" . $value];
        $rateTypeId = $_POST["rate_type-" . $value];
        $device->UpdateDeviceRate($rateId, $rateValue, $minTime, $rateTypeId);
    }

}

if (isset($_POST['add_rate'])) {

    $rate->CreateRate($_POST['new_rate_name'], $_POST['new_rate_type']);
}

if (isset($_POST['CreateNewDevice'])) {
    $device->CreateDevice($_POST['dnsName'], $_POST['deviceName'], $_POST['location'], $_POST['description'], $_POST['status']);

}

?>
<form action="./index.php?view=<?php echo $pages->GetPageId('Edit Devices'); ?>" method=POST>
    <div class="alert alert-info">
        <h4>Devices Configuration</h4>

        <p>Create devices profile</p>

        <p>Device profile is used to keep track of device specific properties
            such as rates, permissions and descriptions.</p>
    </div>
    <div class="form-group">
        <?php
        if ($device->GetDeviceId() > 0) {
            echo "<input name=\"ModifyDevice\" type=\"submit\" class=\"btn btn-primary\" id=\"Modify\" value=\"Modify\">";
        } else {
            echo "  <input name=\"CreateNewDevice\" type=\"submit\" class=\"btn btn-primary\" id=\"Submit\" value=\"Create\" >";
        }
        ?>
    </div>
    <div class="row-fluid">
        <div class="col-md-6">
            <div class="well form-horizontal">
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="editDevice">Device</label>

                    <div class="col-sm-6">
                        <select name="device_option" class="form-control">
                            <option selected value='new'>New</option>
                            <?php
                            $devicesList = $device->GetDevicesList();
                            foreach ($devicesList as $id => $deviceInfo) {
                                echo "<option value=" . $deviceInfo["id"];
                                if ($device->GetDeviceId() == $deviceInfo["id"]) {
                                    echo " selected";
                                }
                                echo ">" . $deviceInfo["full_device_name"] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <input name="Select" type="submit" class="btn btn-primary" id="Select" value="Select"/>
                    </div>
                </div>
            </div>
            <div class="well form-horizontal">
                <div class="form-group">

                    <label class="col-sm-3 control-label" for="editDevice">
                        Device ID (
                        <?php echo $device->GetDeviceId(); ?>
                        ):
                    </label>

                    <div class="col-sm-9"><input name="device_id" type="hidden"
                                                 value="<?php echo $device->GetDeviceId(); ?>">
                        <input name="dnsName" type="text"
                               value="<?php echo $device->getShortName(); ?>" class="form-control">
                    </div>
                </div>
                <div class="form-group">

                    <label class="col-sm-3 control-label" for="editDevice">Auth. Token:</label>

                    <div class="col-sm-9">
                        <input type="text" name="auth_key" value="<?php echo $device->GetDeviceToken(); ?>"
                               class="form-control" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="editDevice">Device Name:</label>

                    <div class="col-sm-9">
                        <input type="text" name="deviceName" class="form-control"
                               value="<?php echo $device->GetFullName(); ?>">
                    </div>

                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="editDevice">Status</label>

                    <div class="col-sm-9">
                        <select name="status" class="form-control">
                            <?php
                            $statusList = $device->DeviceStatusList();
                            $queryStatusOptions = "SELECT id,statusname FROM status WHERE type=1";
                            foreach ($statusList as $id => $statusOption) {
                                echo "<option value=" . $statusOption["id"];
                                if ($device->GetStatus() == $statusOption["id"]) {
                                    echo " SELECTED";
                                }
                                echo ">" . $statusOption["statusname"] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="editDevice">Location:</label>

                    <div class="col-sm-9">
                        <input type="text" name="location" class="form-control"
                               value="<?php echo $device->GetLocation(); ?>"></div>
                </div>
                <div class="form-group">

                    <label class="col-sm-3 control-label" for="editDevice">Notes</label>

                    <div class="col-sm-9"><textarea name="description" class="form-control">
                            <?php echo $device->GetDescription(); ?>
                        </textarea>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-md-6">
            <div class="well form-horizontal">
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="editDevice">
                        Rate
                    </label>
                    <div class="col-sm-4">
                        <input type="text" name="new_rate_name" class="form-control">
                    </div>
                    <div class="col-sm-4">
                        <select name="new_rate_type" class="form-control">
                            <?php
                            foreach ($rateTypes as $id => $rateTypeInfo)
                            {
                                echo "<option value=" . $rateTypeInfo['id'].">". $rateTypeInfo['rate_type_name'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <input type="submit" class="btn btn-primary" value="Add" name="add_rate">
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Device Rates <?php echo $device->GetFullName(); ?><h4>
                </div>
                <div class="panel-body">
                <table class="table table-hover">
                    <tr>
                        <th>Rate Name</th>
                        <th>Rate ($)</th>
                        <th>Min. Time</th>
                        <th>Usage Period</th>
                    </tr>
                    <?php
                    if ($device->GetDeviceId() > 0) {
                        $deviceRates = $device->GetRatesList();
                        foreach ($deviceRates as $id => $deviceRateInfo) {
                            echo "<tr>
							        <td>
							            <input type=\"checkbox\" name=\"ratesBox[]\" value=\"" . $deviceRateInfo["rate_id"] . "\" style=\"display:none;\" CHECKED>
							            <h5>" . $deviceRateInfo["rate_name"] . ":</h5>
							            </td>
							            <td>
							            <input type=\"text\" value=" . round($deviceRateInfo["rate"] * 60) . " name=\"rate-" . $deviceRateInfo["rate_id"] . "\" size=\"3\" maxlength=\"5\" class=\"form-control\">
							            </td>
							            <td>
							            <input type=\"text\" value=" . $deviceRateInfo["min_use_time"] . " name=\"mintime-" . $deviceRateInfo["rate_id"] . "\" size=\"5\" maxlength=\"5\" class=\"form-control\">
							            </td>";
                            echo "  <td>
                                        <select name=\"rate_type-" . $deviceRateInfo["rate_id"] . "\" class=\"form-control\">";

                            $rateTypeNotSelected = true;

                            foreach ($rateTypes as $id => $rateTypeInfo)
                            {
                                echo "<option value=" . $rateTypeInfo['id'];
                                if ($rateTypeInfo['id'] == $deviceRateInfo['rate_type_id']) {
                                    echo " SELECTED";
                                    $rateTypeNotSelected = false;

                                }
                                echo ">" . $rateTypeInfo['rate_type_name'] . "</option>";
                            }
                            if ($rateTypeNotSelected) {
                                echo "<option value=0 SELECTED>Not Set</option>";
                            }

                            echo "</select>";
                            echo "</td></tr>";
                        }
                    }

                    ?>
                </table>
                    </div>
                </div>
            </div>
        </div>
</form>
<?php
	require_once 'includes/footer.inc.php';