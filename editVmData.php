<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];
if ($type == 'ITMaster') {
    include("header.php");
    include("menuItMaster.php");
} else {
    include("logout.php");
    exit;
}
include("dbConnection.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$res = [];

if ($id > 0) {
    // Fetch the VM Agent record
    $sql = "SELECT * FROM vmagent WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $res = $result->fetch_assoc();
    
 // If not found in employee, check in users
  if (!$res) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $res = $result->fetch_assoc();
    }
    // If not found in vmagent, check in employee
    if (!$res) {
        $sql = "SELECT * FROM employee WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $res = $result->fetch_assoc();
    }

   
}

if (isset($_POST['updateData'])) {
    $id = intval($_POST['id']);
    $table = $_POST['table'];
    
    switch ($table) {
        case 'users':
            $employeeId = $_POST['employeeId'];
			$username = $_POST['username'];
			$language = $_POST['language'];
		
			$updateSql = "UPDATE users SET employeeId = ?, username = ?, language = ? WHERE id = ?";
			$stmt = $con->prepare($updateSql);
			$stmt->bind_param('sssi', $employeeId, $username, $language, $id); 
			break;
        
        case 'employee':
            $empId = $_POST['empId'];
            $name = $_POST['name'];
            $updateSql = "UPDATE employee SET empId = ?, name = ? WHERE id = ?";
            $stmt = $con->prepare($updateSql);
            $stmt->bind_param('ssi', $empId, $name, $id);
            break;
        
        case 'vmagent':
            $agentId = $_POST['agentId'];
            $branch = $_POST['branch'];
            $language = $_POST['language'];
            $grade = $_POST['grade'];
            
            $updateSql = "UPDATE vmagent SET agentId = ?, branch = ?, language = ?, grade = ? WHERE id = ?";
            $stmt = $con->prepare($updateSql);
            $stmt->bind_param('ssssi', $agentId, $branch, $language, $grade, $id);
            break;
        
        default:
            echo "Invalid table";
            exit;
    }
    
    if ($stmt->execute()) {
		echo '<script>alert("Record updated successfully");</script>';
		echo '<script>setTimeout("location.href = \'addVmNew.php\';", 150);</script>';
	} else {
		echo "Error updating record: " . $stmt->error;
	}

	
    $stmt->close();
}

?>

<style> 
	.panel-heading input[type=text]{
		box-sizing: border-box;
		border: 2px solid #ccc;
		border-radius: 4px;
		font-size: 16px;
		background-color: white;
		/* background-image: url('images/searchicon.png'); */
		background-position: 220px 12px; 
		background-repeat: no-repeat;
		padding: 12px 50px 12px 15px;
		-webkit-transition: width 0.4s ease-in-out;
		transition: width 0.4s ease-in-out;
	}
	#search_branchId,#search_date{
		padding: 10px;
		height: 50px;
		font-size: 16px;
		color: grey;
		box-sizing: border-box;
		border: 2px solid #ccc!important;
	}
	#wrapper{
		background: #f5f5f5;
	}	
	#wrapper h3{
		text-transform:uppercase;
		font-weight:600;
		font-size: 18px;
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
		font-size: 12px;
		font-family:'Roboto',sans-serif;
		text-transform:uppercase;
		color:#fffafa;
		background-color:#123C69;
		box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
		text-align:center;
		position:relative;
	}
	.fa_Icon {
		color: #ffcf40;
	}
	.fa-Icon {
		color: #990000;
	}
	tbody{
	    font-weight: 600;
	}
	.modal-title {
		font-size: 20px;
		font-weight: 600;
		color:#708090;
		text-transform:uppercase;
	}	
	.modal-header{
		background: #123C69;
	}	
	#wrapper .panel-body{
		border: 5px solid #fff;
		border-radius: 10px;
		padding: 20px;
		box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
		background-color: #f5f5f5;
		min-height: 300px;
	}	
	.preload{ 
		width:100px;
		height: 100px;
		position: fixed;
		top: 40%;
		left: 70%;
	}	
	.ajaxload{ 
		width:100px;
		height: 300px;
		position: fixed;
		top: 20%;
		left: 20%;
	}
	.buttons-csv,.btn-info{
	    font-size: 10px;
	}
	fieldset {
		margin-top: 1.5rem;
		margin-bottom: 1.5rem;
		border: none;
		box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
		border-radius:5px;
	}
	legend{
		margin-left:8px;
		width:450px;
		background-color: #123C69;
		padding: 5px 15px;
		line-height:30px;
		font-size: 14px;
		color: white;
		text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
		transform: translateX(-1.1rem);
		box-shadow: -1px 1px 1px rgba(0,0,0,0.8);
		margin-bottom:0px;
		letter-spacing: 2px;
	}
	.card {
		position: relative;
		display: flex;
		flex-direction: column;
		min-width: 0;
		word-wrap: break-word;
		background-color: #fff;
		background-clip: border-box;
		border: 0 solid rgba(0,0,0,.125);
		border-radius: .25rem;
		box-shadow: 0 1px 3px 0 rgba(0,0,0,.1), 0 1px 2px 0 rgba(0,0,0,.06);
	}
	.card-body {
		flex: 1 1 auto;
		min-height: 1px;
		padding: 1rem;
	}
	h4, h6{
		font-weight: bold;
	}
	.form-control{
		height:25px;
	}
	@media only screen and (max-width: 600px) {
		
		legend{

			width:295px;
			font-size: 10px;

		}	
	
	}
</style> 

<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading">	
						<div class="row">
							<div class="col-lg-12">
								<div class="col-lg-7">	
									<h3 class="text-success"> 
										<i class="fa-Icon fa fa-address-card-o" aria-hidden="true"></i> Update Data
									</h3>
								</div>	
							</div>
						</div>
					</div>
					<div class="panel-body">
						<div class="container1" >
						
							<div class="col-lg-4">
								<fieldset class="transaction-detail-box">
									<legend><i style="padding-top:15px" class="fa_Icon fa fa-address-card-o"></i>VM AGENT</span></legend>
									<div class="panel-body">
                                    <form method="POST" class="form-horizontal" action="">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($res['id']); ?>">
                                        <input type="hidden" name="table" value="vmagent">
											<ul class="list-group list-group-flush">
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">EMPLOYEE ID</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" name="agentId" id="agentId" class="form-control" autocomplete="off" value="<?php echo htmlspecialchars($res['agentId']); ?>"></h6>
												</div>
												</div>				  
											</li>

											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">BRANCH</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"> <input type="text" name="branch" id="branch" class="form-control" autocomplete="off" value="<?php echo htmlspecialchars($res['branch']); ?>"></h6>
												</div>
												</div>
											</li>
											

											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">GRADE</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0 text-uppercase"><select class="form-control" style="padding:0px 5px" name="grade" id="grade" >
                                                    <option value="" <?php echo !$res['grade'] ? 'selected' : ''; ?>>Grade</option>
                                                    <option value="A" <?php echo $res['grade'] == 'A' ? 'selected' : ''; ?>>A</option>
                                                    <option value="B" <?php echo $res['grade'] == 'B' ? 'selected' : ''; ?>>B</option>
                                                    <option value="C" <?php echo $res['grade'] == 'C' ? 'selected' : ''; ?>>C</option>
                                                </select></h6>
												</div>
												</div>				  
											</li>
											
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">LANGUAGE</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0 text-uppercase"> <input type="text" name="language" id="language" class="form-control" autocomplete="off" value="<?php echo htmlspecialchars($res['language']); ?>"></h6>
												</div>
												</div>				  
											</li>
									
											</ul>
                                            <div class="col-lg-1 pull-left">
                                                <button class="btn btn-success btn-block" style="width:100px;height:45px;" name="updateData" id="updateData" type="submit"><span style="color:#ffcf40"></span>UPDATE VM</button>
                                            </div>	

                                        </form>
									</div>
								</fieldset>
							</div>
							
							<div class="col-lg-4">
							<fieldset class="transaction-detail-box">
							<legend><i style="padding-top:15px" class="fa_Icon fa fa-address-book"></i>EMPLOYEE </legend>
								<div class="panel-body">
                                    <form method="POST" class="form-horizontal" action="">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($res['id']); ?>">
                                        <input type="hidden" name="table" value="employee">
											<ul class="list-group list-group-flush">

											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">EMPLOYEE ID</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" name="empId" id="empId" class="form-control" autocomplete="off" value="<?php echo htmlspecialchars($res['empId']); ?>"></h6>
												</div>
												</div>				  
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">NAME</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" name="name" id="name"  value="<?php echo htmlspecialchars($res['name']); ?>" class="form-control" autocomplete="off"></h6>
												</div>
												</div>				  
											</li>
                                            				
											</ul>
                                           
											<div class="col-lg-1 pull-center">
                                                <button class="btn btn-success btn-block" style="width:100px;height:45px;" name="updateData" id="updateData" type="submit"><span style="color:#ffcf40"></span>UPDATE VM</button>
                                            </div>
                                    </form>				
								</div>
							</fieldset>
							</div>
                             
							<div class="col-lg-4">
							<fieldset class="transaction-detail-box">
								<legend><i style="padding-top:15px" class="fa_Icon fa fa-users"></i> USERS :</legend>
								<div class="panel-body">
                                    <form method="POST" class="form-horizontal" action="">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($res['id']); ?>">
                                        <input type="hidden" name="table" value="users">
											<ul class="list-group list-group-flush">

											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">EMPLOYEE ID</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" name="employeeId" id="employeeId" class="form-control" autocomplete="off" value="<?php echo htmlspecialchars($res['employeeId']); ?>"></h6>
												</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">USER NAME</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><input type="text" name="username" id="username"  class="form-control" value="<?php echo htmlspecialchars($res['username']); ?>" autocomplete="off"></h6>
												</div>
												</div>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">

												<div class="row">
												<div class="col-sm-4">
												<h6 class="mb-0">LANGUAGE</h6>
												</div>				  
												<div class="col-sm-1"> : </div>
												<div class="col-sm-7">
												<h6 class="mb-0"><select class="form-control" style="padding:0px 5px" name="language" id="language" >
                                                        <option value="" <?php echo !$res['language'] ? 'selected' : ''; ?>>Language</option>
                                                        <option value="1" <?php echo $res['language'] == '1' ? 'selected' : ''; ?>>Kannada</option>
                                                        <option value="2" <?php echo $res['language'] == '2' ? 'selected' : ''; ?>>Tamil</option>
                                                        <option value="3" <?php echo $res['language'] == '3' ? 'selected' : ''; ?>>Telugu</option>
                                                    </select></h6>
												</div>
												</div>
											</li>
																						
											</ul>
											<div class="col-lg-1 pull-center">
                                                <button class="btn btn-success btn-block" style="width:100px;height:45px;" name="updateData" id="updateData" type="submit"><span style="color:#ffcf40"></span>UPDATE VM</button>
                                            </div>
                                    </form>					
								</div>
							</fieldset>
							</div>
							
							</form>
							
						</div>

	
					</div>
				</div>
			</div>
		</div>		
	</div>
	<div style="clear:both"></div>	
	<?php include("footer.php"); ?>
</div>
                   