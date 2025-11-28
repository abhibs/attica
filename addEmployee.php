<?php
    session_start();
    $type=$_SESSION['usertype'];        
    if($type=='Master'){
        include("header.php");
        include("menumaster.php");
    }
    else if($type=='HR'){
        include("header.php");
        include("menuhr.php");
    }
    else if($type=='Zonal'){
        include("header.php");
        include("menuZonal.php");
    }
    else if($type=='SubZonal'){
        include("header.php");
        include("menuSubZonal.php");
    }
    else if($type=='ZonalMaster'){
        include("header.php");
        include("menuzonalMaster.php");
    }
    else if($type=='SundayUser'){
        include("header.php");
        include("menuSundayUser.php");
    }
    else{
        include("logout.php");
    }    
    include("dbConnection.php");
?>
<style>
    #wrapper{
    background-color: #e6e6fa;
    }    
    #wrapper h3{
    text-transform:uppercase;
    font-weight:600;
    font-size: 20px;
    color:#123C69;
    }
    .form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
    background-color:#fffafa;
    }
    .text-success{
    color:#123C69;
    text-transform:uppercase;
    font-weight:bold;
    font-size: 12px;
    }
    .btn-primary{
    background-color:#123C69;
    }
    .theadRow {
    text-transform:uppercase;
    background-color:#123C69!important;
    color: #f2f2f2;
    font-size:11px;
    }    
    .dataTables_empty{
    text-align:center;
    font-weight:600;
    font-size:12px;
    text-transform:uppercase;
    }
    .btn-success{
    display:inline-block;
    padding:0.7em 1.4em;
    margin:0 0.3em 0.3em 0;
    border-radius:0.15em;
    box-sizing: border-box;
    text-decoration:none;
    font-size: 10px;
    font-family:'Roboto',sans-serif;
    text-transform:uppercase;
    color:#fffafa;
    background-color:#123C69;
    box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
    text-align:center;
    position:relative;
    }
    .btn-danger{
    display:inline-block;
    padding:0.7em 1.4em;
    margin:0 0.3em 0.3em 0;
    border-radius:0.15em;
    box-sizing: border-box;
    text-decoration:none;
    font-size: 11px;
    font-family:'Roboto',sans-serif;
    text-transform:uppercase;
    color:#fffafa;
    background-color:#e74c3c;
    box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
    text-align:center;
    position:relative;
    }    
    .fa_Icon {
    color:#ffd700;
    }
    .fa_icon{
    color:#990000;
    }
    .row{
    margin-left:0px;
    margin-right:0px;
    }
    
    #wrapper .panel-body{
    border: 5px solid #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
    background-color: #f5f5f5;
    }
    
    fieldset {
    margin-top: 1.5rem;
    margin-bottom: 1.5rem;
    border: none;
    border: 5px solid #fff;
    border-radius: 10px;
    padding: 5px;
    box-shadow: rgb(50 50 93 / 25%) 0px 50px 100px -20px, rgb(0 0 0 / 30%) 0px 30px 60px -30px, rgb(10 37 64 / 35%) 0px -2px 6px 0px inset;
    }
    legend{
    margin-left:8px;
    width:350px;
    background-color: #123C69;
    padding: 5px 15px;
    line-height:30px;
    font-size: 18px;
    color: white;
    text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
    transform: translateX(-1.1rem);
    box-shadow: -1px 1px 1px rgba(0,0,0,0.8);
    margin-bottom:0px;
    letter-spacing: 2px;
    text-transform:uppercase;
    }
    button {
    transform: none;
    box-shadow: none;
    }
    
    button:hover {
    background-color: gray;
    cursor: pointer;
    }
    
    legend:after {
    content: "";
    height: 0;
    width:0;
    background-color: transparent;
    border-top: 0.0rem solid transparent;
    border-right:  0.35rem solid black;
    border-bottom: 0.45rem solid transparent;
    border-left: 0.0rem solid transparent;
    position:absolute;
    left:-0.075rem;
    bottom: -0.45rem;
    }
</style>
<div id="wrapper">
    <div class="row content">
        
        <?php 
            if(isset($_GET['empId']) && $_GET['empId']!=''){
                $empid = $_GET['empId'];
                $empData = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM employee WHERE id='$empid' ORDER BY id DESC"));
            ?>
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-body" style="background-color:#e6e6fa;">
                        <h3 class="text-success"><i class="fa_icon fa fa-user"></i> Modify Employee</h3><hr>
                        <form method="POST" class="form-horizontal" action="edit.php">
                            <input type="hidden" name="empIdRow" value="<?php echo $empid; ?>">
                            <input type="hidden" name="userType" value="<?php echo $type; ?>">
                            <div class="col-lg-2">
                                <p class="text-success"><b>Employee Id</b></p>
                                <div class="input-group">
                                    <span class="input-group-addon"><span class="fa_icon fa fa-address-card"></span></span>
                                    <input type="text" name="empId" id="empId" class="form-control" required value="<?php echo $empData['empId']; ?>">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <p class="text-success"><b>Name</b></p>
                                <div class="input-group">
                                    <span class="input-group-addon"><span class="fa_icon fa fa-user"></span></span>
                                    <input type="text" name="name" id="name" class="form-control" required value="<?php echo $empData['name']; ?>">
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <p class="text-success"><b>Contact</b></p>
                                <div class="input-group">
                                    <span class="input-group-addon"><span class="fa_icon fa fa-phone"></span></span>
                                    <input type="text" class="form-control" name="number" id="number" required value="<?php echo $empData['contact']; ?>">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <p class="text-success"><b>Designation</b></p>
                                <div class="input-group">
                                    <span class="input-group-addon"><span class="fa_icon fa fa-briefcase"></span></span>
                                    <select class="form-control" style="padding:0px 5px" name="designation" id="designation" required>
                                        <option selected="true" value="">Designation</option>
                                        <option value="BM">BM</option>
                                        <option value="ABM">ABM</option>
                                        <option value="TE">TE</option>
                                        <option value="VM">VM</option>
                                        <option value="ACCOUNT">ACCOUNT</option>
                                        <option value="CSR">CSR</option>
                                        <option value="SOFTWARE">SOFTWARE</option>
                                        <option value="IT">IT</option>
                                        <option value="HR">HR</option>
                                        <option value="DIGITAL MARKETING">DIGITAL MARKETING</option>
                                        <option value="HOUSE KEEPING">HOUSE KEEPING</option>
                                        <option value="INFRA">INFRA</option>
                                        <option value="AP CLUSTER HEAD">AP CLUSTER HEAD</option>
                                        <option value="TN CLUSTER HEAD">TN CLUSTER HEAD</option>
                                        <option value="ZONAL HEAD">ZONAL HEAD</option>
                                        <option value="BRANCH CO-ORDINATOR">BRANCH CO-ORDINATOR</option>
                                        <option value="HO CO-ORDINATOR">HO CO-ORDINATOR</option>
                                        <option value="MD CO-ORDINATOR">MD CO-ORDINATOR</option>
                                    </select>
                                </div>
                            </div>

                            <!-- NEW: Salary -->
                            <div class="col-lg-2">
                                <p class="text-success"><b>Salary</b></p>
                                <div class="input-group">
                                    <span class="input-group-addon"><span class="fa_icon fa fa-inr"></span></span>
                                    <input type="number" step="0.01" min="0" class="form-control" name="salary" id="salary" required value="<?php echo isset($empData['salary']) ? $empData['salary'] : ''; ?>">
                                </div>
                            </div>

                            <!-- NEW: Advance -->
                            <div class="col-lg-2">
                                <p class="text-success"><b>Advance</b></p>
                                <div class="input-group">
                                    <span class="input-group-addon"><span class="fa_icon fa fa-credit-card"></span></span>
                                    <input type="number" step="0.01" min="0" class="form-control" name="advance" id="advance" required value="<?php echo isset($empData['advance']) ? $empData['advance'] : '0.00'; ?>">
                                </div>
                            </div>

                            <!-- NEW: Status -->
                            <div class="col-lg-2">
                                <p class="text-success"><b>Status</b></p>
                                <div class="input-group">
                                    <span class="input-group-addon"><span class="fa_icon fa fa-toggle-on"></span></span>
                                    <select class="form-control" name="status" id="status" required>
                                        <?php 
                                            $curStatus = isset($empData['status']) && $empData['status']!='' ? $empData['status'] : 'Active';
                                        ?>
                                        <option value="Active"   <?php echo ($curStatus=='Active')?'selected':''; ?>>Active</option>
                                        <option value="Inactive" <?php echo ($curStatus=='Inactive')?'selected':''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <button type="submit" name="updateemp" class="btn btn-success btn-block" style="margin-top:28px"><span class="fa_Icon fa fa-sign-in"></span> UPDATE</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php } else  {?>
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-body" style="background-color:#e6e6fa;">
                        <h3 class="text-success"><i class="fa_icon fa fa-user"></i> Add Employee </h3><hr>
                        <form method="POST" class="form-horizontal" action="add.php">
                            <div class="col-lg-2">
                                <p class="text-success"><b>Employee Id</b></p>
                                <div class="input-group">
                                    <span class="input-group-addon"><span class="fa_icon fa fa-address-card"></span></span>
                                    <input type="text" name="empId" id="empId" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <p class="text-success"><b>Name</b></p>
                                <div class="input-group">
                                    <span class="input-group-addon"><span class="fa_icon fa fa-user"></span></span>
                                    <input type="text" name="name" id="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <p class="text-success"><b>Contact</b></p>
                                <div class="input-group">
                                    <span class="input-group-addon"><span class="fa_icon fa fa-phone"></span></span>
                                    <input type="text" class="form-control" name="number" id="number" required>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <p class="text-success"><b>Designation</b></p>
                                <div class="input-group">
				    <span class="input-group-addon"><span class="fa_icon fa fa-briefcase"></span></span>
				    <!--input type="text" class="form-control" name="designation" id="designation" required value="<!?php echo $empData['designation']; ?>"-->
                                    <select class="form-control" style="padding:0px 5px" name="designation" id="designation" required>
                                        <option selected="true" value="">Designation</option>
                                        <option value="BM">BM</option>
                                        <option value="ABM">ABM</option>
                                        <option value="TE">TE</option>
                                        <option value="VM">VM</option>
                                        <option value="ACCOUNT">ACCOUNT</option>
                                        <option value="CSR">CSR</option>
                                        <option value="SOFTWARE">SOFTWARE</option>
                                        <option value="IT">IT</option>
                                        <option value="HR">HR</option>
                                        <option value="DIGITAL MARKETING">DIGITAL MARKETING</option>
                                        <option value="HOUSE KEEPING">HOUSE KEEPING</option>
                                        <option value="INFRA">INFRA</option>
                                        <option value="AP CLUSTER HEAD">AP CLUSTER HEAD</option>
                                        <option value="TN CLUSTER HEAD">TN CLUSTER HEAD</option>
					<option value="ZONAL HEAD">ZONAL HEAD</option>
					<option value="BRANCH CO-ORDINATOR">BRANCH CO-ORDINATOR</option>
                                        <option value="HO CO-ORDINATOR">HO CO-ORDINATOR</option>
                                        <option value="MD CO-ORDINATOR">MD CO-ORDINATOR</option>
                            </div>

                            <!-- NEW: Status -->
                            <div class="col-lg-2">
                                <p class="text-success"><b>Status</b></p>
                                <div class="input-group">
                                    <span class="input-group-addon"><span class="fa_icon fa fa-toggle-on"></span></span>
                                    <select class="form-control" name="status" id="status" required>
                                        <option value="Active" selected>Active</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <button type="submit" name="submitemp" class="btn btn-success btn-block" style="margin-top:28px"><span class="fa_Icon fa fa-sign-in"></span> ADD EMPLOYEE</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
        
    <?php if($type=='HR'){ ?>
            <div class="col-lg-12">
                <div class="hpanel">
                    <fieldset>
                        <legend> <i style="padding-top:15px" class="fa_Icon fa fa-eye"></i>  Employee Details </legend>
                        <table id="example2" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr class="theadRow">
                                    <th><span class="fa fa-sort-numeric-asc"></span></th>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Designation</th>
                                    <!-- NEW columns -->
                                    <th>Salary</th>
                                    <th>Advance</th>
                                    <th>Status</th>
                                    <!-- /NEW columns -->
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $i=1;
                                    $sql = mysqli_query($con,"SELECT * FROM employee order by empId ASC;");
                                    while($row = mysqli_fetch_assoc($sql)){
                                        echo "<tr>";
                                        echo "<td>" . $i ."</td>";
                                        echo "<td>" . htmlspecialchars($row['empId']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['contact']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['designation']) . "</td>";
					// NEW columns
					
                                        $sal = isset($row['salary']) ? number_format((float)$row['salary'],2,'.','') : '';
                                        $adv = isset($row['advance']) ? number_format((float)$row['advance'],2,'.','') : '';
                                        $sts = isset($row['status']) ? $row['status'] : 'Active';
                                        echo "<td>" . $sal . "</td>";
                                        echo "<td>" . $adv . "</td>";
                                        echo "<td>" . htmlspecialchars($sts) . "</td>";
                                        // /NEW columns
                                        echo "<td><a class='btn btn-success' href='addEmployee.php?empId=".$row['id']."'><i class='fa fa-edit'></i></a></td>";
                                        $name = $row['name'];
                                        echo "<td><a class='btn btn-danger' href='delete.php?emplId=".$row['id']."' onClick=\"javascript: return confirm('Press Ok to delete Employee: ".$name."!');\"><i class='fa fa-times'></i></a></td>";
                                        echo "</tr>";
                                        $i++;
                                    }
                                ?>
                            </tbody>
                        </table>
                    </fieldset>
                </div>
            </div>
        <?php } ?>
        <div style="clear:both"></div>
    </div>
<?php include("footer.php");?>

