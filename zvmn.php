<?php
include("dbConnection.php");

// Select Language first

if (!empty($_POST["state_id2"])) {

    $mn = $_POST["state_id2"];

    $query = mysqli_query($con, "SELECT DISTINCT X.* FROM
	(SELECT A.employeeId,A.language,B.name
	FROM
	(SELECT * FROM users WHERE type='VM-HO' AND language='$mn') AS A
	JOIN
	(SELECT * FROM employee) AS B
	ON A.employeeId = B.empId) AS X");
?>
    <option Readonly disabled selected class="bg-white" value="">Select Captain</option>
    <?php
    while ($row = mysqli_fetch_array($query)) {
    ?>
        <option value="<?php echo $row["employeeId"]; ?>"><?php echo $row["name"]; ?></option>
<?php
    }
}



// Branch Selection 
/* ===================================================*/

if (isset($_POST["state_id"])) {

    $nnn = $_POST["state_id"];

    $qu23 = mysqli_query($con, "SELECT * FROM `users` where employeeId='$nnn' AND type='VM-HO'");
    $row23 = mysqli_fetch_array($qu23);
    $lan = $row23['language'];



    $qu24 = mysqli_query($con, "SELECT * FROM `language` where id='$lan'");
    $row24 = mysqli_fetch_array($qu24);
    $lan1 = $row24['state'];


    $str_arr = explode(",", $lan1);
    $sat1 = $str_arr[0];
    $sat2 = $str_arr[1];

    $vn23 = mysqli_query($con, "SELECT * FROM `vmagent` WHERE agentId='$nnn'");
    $vn1 = mysqli_fetch_array($vn23);
    $vn_ary = $vn1["branch"];

    $data = array();
    $vmnameaccess = array();

    $branch_a  = '<input type="Hidden" name="branchA" class="form-control"  value="' . $vn_ary . '"/>';

    // get already access Branches
    $vm = mysqli_query($con, "SELECT * FROM `vmagent` WHERE agentId='$nnn'");

    $vm1 = mysqli_fetch_array($vm);
    $vm_arr = explode(",", $vm1["branch"]);
    $vmnameaccess = '<tr></tr>';
    foreach ($vm_arr as $branch_id) {

        $branchname = mysqli_query($con, "SELECT branchName FROM `branch` WHERE branchId='$branch_id'");
        $bn = mysqli_fetch_row($branchname);
        $vmnameaccess .= '<tr><td>' . $bn[0] . '</td></tr>';
    }




    $data['branch_a'] = $branch_a;
    $data['vmnameaccess'] = $vmnameaccess;

    //returns data as JSON format
    echo json_encode($data);
}


?>