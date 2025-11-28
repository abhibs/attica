<?php
	session_start();
	include("header.php");
	include("menu.php");
	include("dbConnection.php");
?>
<div id="wrapper">
	<div class="content">
		<div class="row-content">
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading">
						<h3 class="text-success"><b> <i style="color:#990000" class="fa fa-edit"></i> Report Issue</b></h3>
					</div>
					<div class="panel-body">
						<form method="POST" class="form-horizontal" action="add.php">
							<div class="form-group">	
								<div class="col-sm-3"><label class="text-success">Issue Type</label>
									<div class="input-group">
										<span class="input-group-addon"><span style="color:#990000" class="fa fa-info-circle"></span></span>
										<select class="form-control" name="issue" id="issue">
											<option>Select Issue Type</option>
											<option>System issue</option>
											<option>Internet issue</option>
											<option>CCTV camera issue</option>
											<option>Karat meter issue</option>
											<option>Printer/Scanner issue</option>
											<option>Power/UPS issue</option>
											<option>Excel issue</option>
											<option>Other Issue</option>
										</select>
									</div>
								</div>
								<div class="col-sm-5"><label class="text-success">Description</label>
									<div class="input-group">
										<span class="input-group-addon"><span style="color:#990000" class="fa fa-file-text"></span></span>
										<input type="text" class="form-control"  name="des" id="des" placeholder="Enter Description" >
									</div>
								</div>
								<div class="col-sm-2"><label>_________________</label><br>
									<button class="btn btn-success" name="submitissue" id="submitissue" type="submit" ><span class="fa fa-check"></span> Submit</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php include("footer.php");?></div>