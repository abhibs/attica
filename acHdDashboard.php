<?php
    error_reporting(E_ERROR | E_PARSE);
    session_start();
    $type=$_SESSION['usertype'];
    if($type=='AccHead') {
        include("header.php");
        include("menuaccHeadPage.php");
	}
    else{
        include("logout.php");
	}
    include("dbConnection.php");
    date_default_timezone_set("Asia/Kolkata");
	$date = date('Y-m-d');  
?>
<style>
	#silver {
	height: 140px;
	width: 100%;
	}
	#gold {
	height: 140px;
	width: 100%;
	}
</style>
<div id="wrapper" style="top:50px">
    <div class="content animate-panel">
        <div class="row m-t-md">
            
            <!-- Total Gold -->
            <?php
				$totalGold = mysqli_fetch_array(mysqli_query($con,"SELECT SUM(grossA) AS grossA,SUM(netA) AS netA,SUM(grossW) AS grossW,SUM(netW) AS netW FROM trans WHERE metal='Gold' and date='$date' AND status='Approved'"));
			?>
            <div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body" id="gold">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success">Total Gold</h3>
						</div>
                        <div class="stats-icon pull-right">
                            <i style="color:#990000" class="fa fa-balance-scale fa-4x"></i>
						</div>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label">Gross Weight:</small>
                                    <h4> <?php echo round($totalGold['grossW'],2) ?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label">Net Amount:</small>
                                    <h4><?php echo $totalGold['netA']; ?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center">
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="aggregate.php">View Details</a>
					</div>
				</div>
			</div>
			
            <!-- Karnataka Gold -->
			<?php
				$bangaloreGold = mysqli_fetch_assoc(mysqli_query($con,"SELECT SUM(grossA) as grossA, SUM(netA) as netA, SUM(grossW) AS grossW, COUNT(*) AS bills FROM trans WHERE date = '$date' AND metal = 'Gold' AND status='Approved' AND  branchId IN (SELECT branchId FROM `branch` WHERE  city = 'Bengaluru')"));
				$karnatakaGold = mysqli_fetch_assoc(mysqli_query($con,"SELECT SUM(grossA) as grossA,SUM(netA) as netA,SUM(grossW) AS grossW, COUNT(*) AS bills FROM trans WHERE date = '$date' AND metal = 'Gold' AND status='Approved' AND  branchId IN (SELECT branchId FROM `branch` WHERE state = 'Karnataka' AND city != 'Bengaluru')"));
			?>
            <div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success"> KA Gold</h3>
						</div>
                        <div class="stats-icon pull-right">
                            <i style="color:#990000" class="fa fa-balance-scale fa-4x"></i>
						</div>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> Bangalore GWeight:</small>
                                    <h4><?php
                                        if($bangaloreGold['grossA'] != null){
                                            $bangaloreMargin = round((($bangaloreGold['grossA']-$bangaloreGold['netA'])/$bangaloreGold['grossA'])*100,1);
                                        }
                                        else{
                                            $bangaloreMargin = 0;
                                        }
										echo round($bangaloreGold['grossW'],2)."(";										
										echo is_nan($bangaloreMargin)? 0:$bangaloreMargin;
										echo ")";
									?>
									</h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> KA GWeight:</small>
                                    <h4><?php
                                        if($karnatakaGold['grossA'] != null){
                                            $karnatakaMargin = round((($karnatakaGold['grossA']-$karnatakaGold['netA'])/$karnatakaGold['grossA'])*100,1);
                                        }
                                        else{
                                            $karnatakaMargin = 0;
                                        }
										echo round($karnatakaGold['grossW'],2)."(";
										echo is_nan($karnatakaMargin)? 0:$karnatakaMargin;
										echo ")";
									?>
									</h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center">
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="transactionReports.php?city=Bengaluru">View Details</a>
					</div>
				</div>
			</div>
            
            <!-- TN Gold -->
			<?php
				$chennaiGold = mysqli_fetch_assoc(mysqli_query($con,"SELECT SUM(grossA) as grossA,SUM(netA) as netA,SUM(grossW) AS grossW, COUNT(*) AS bills FROM trans WHERE date = '$date' AND metal = 'Gold' AND status='Approved' AND  branchId IN (SELECT branchId FROM `branch` WHERE  city = 'Chennai')"));
				$tamilnaduGold = mysqli_fetch_assoc(mysqli_query($con,"SELECT SUM(grossA) as grossA,SUM(netA) as netA,SUM(grossW) AS grossW, COUNT(*) AS bills FROM trans WHERE date = '$date' AND metal = 'Gold' AND status='Approved' AND  branchId IN (SELECT branchId FROM `branch` WHERE state IN ('Tamilnadu','Pondicherry') AND city != 'Chennai') "));
			?>
            <div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success"> TN Gold</h3>
						</div>
                        <div class="stats-icon pull-right">
                            <i style="color:#990000" class="fa fa-balance-scale fa-4x"></i>
						</div>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> Chennai GWeight:</small>
                                    <h4><?php
                                        if($chennaiGold['grossA'] != null){
                                            $chennaiMargin = round((($chennaiGold['grossA']-$chennaiGold['netA'])/$chennaiGold['grossA'])*100,1);
                                        }
                                        else{
                                            $chennaiMargin = 0;
                                        }
										echo round($chennaiGold['grossW'],2)."(";
										echo is_nan($chennaiMargin)? 0:$chennaiMargin;
										echo ")";
									?>
									</h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> TN GWeight:</small>
									<h4><?php
									    if($tamilnaduGold['grossA'] != null){
									        $tamilnaduMargin = round((($tamilnaduGold['grossA']-$tamilnaduGold['netA'])/$tamilnaduGold['grossA'])*100,1);
									    }
										else{
										    $tamilnaduMargin = 0;
										}
										echo round($tamilnaduGold['grossW'],2)."(";
										echo is_nan($tamilnaduMargin)? 0:$tamilnaduMargin;
										echo ")";
									?>
									</h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center">
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="transactionReports.php?city=Chennai">View Details</a>
					</div>
				</div>
			</div>
            
            <!-- AP/T Gold -->
			<?php
				$hyderabadGold = mysqli_fetch_assoc(mysqli_query($con,"SELECT SUM(grossA) as grossA,SUM(netA) as netA,SUM(grossW) AS grossW, COUNT(*) AS bills FROM trans WHERE date='$date' AND metal='Gold' AND status='Approved' AND  branchId IN (SELECT branchId FROM `branch` WHERE  city='Hyderabad')"));
				$andhraTelanganaGold = mysqli_fetch_assoc(mysqli_query($con,"SELECT SUM(grossA) as grossA,SUM(netA) as netA,SUM(grossW) AS grossW, COUNT(*) AS bills FROM trans WHERE date='$date' AND metal='Gold' AND status='Approved' AND  branchId IN (SELECT branchId FROM `branch` WHERE state IN ('Andhra Pradesh','Telangana') AND city!='Hyderabad')"));
			?>
            <div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success"> AP/T Gold</h3>
						</div>
						<div class="stats-icon pull-right">
							<i style="color:#990000" class="fa fa-balance-scale fa-4x"></i>
						</div>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> Hyderabad GWeight:</small>
                                    <h4><?php
                                        if($hyderabadGold['grossA'] != null){
                                            $hydMargin = round((($hyderabadGold['grossA']-$hyderabadGold['netA'])/$hyderabadGold['grossA'])*100,1);
                                        }
										else{
										    $hydMargin = 0;
										}
										echo round($hyderabadGold['grossW'],2)."(";
										echo is_nan($hydMargin)? 0:$hydMargin;
										echo ")";
									?>									
									</h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> AP/T GWeight:</small>
                                    <h4><?php
                                        if($andhraTelanganaGold['grossA'] != null){
                                            $APTmargin = round((($andhraTelanganaGold['grossA']-$andhraTelanganaGold['netA'])/$andhraTelanganaGold['grossA'])*100,1);
                                        }
										else{
										    $APTmargin = 0;
										}
										echo round($andhraTelanganaGold['grossW'],2)."(";
										echo is_nan($APTmargin)? 0:$APTmargin;
										echo ")";
									?>
									</h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center">
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="transactionReports.php?city=Hyderabad">View Details</a>
					</div>
				</div>
			</div>
			
			<!--   TOTAL SILVER   -->
			<?php
				$tatalSilver = mysqli_fetch_assoc(mysqli_query($con,"SELECT SUM(netW) AS netW, ROUND(SUM(grossW),2) AS grossW,SUM(netA) AS netA FROM trans WHERE metal='Silver' and date='$date' AND status='Approved'"));
			?>
			<div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body" id="silver">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success">Total Silver</h3>
						</div>
                        <div class="stats-icon pull-right">
                            <i style="color:#990000" class="fa fa-balance-scale  fa-4x"></i>
						</div>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label">Gross Weight:</small>
                                    <h4><i style="color:#990000" class="fa fa-balance-scale"></i> <?php echo $tatalSilver['grossW'];?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label">Net Amount:</small>
                                    <h4><?php echo $tatalSilver['netA']; ?></h4>                                  
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center">
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="silverReports.php">View Details</a>
					</div>
				</div>
			</div>
			
			<!-- Karnataka Bills -->
            <div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success">KA Bills<?php echo "(".($bangaloreGold['bills']+$karnatakaGold['bills']).")";   ?></h3>
						</div>
                        <div class="stats-icon pull-right">
                            <i style="color:#990000" class="fa fa-users fa-4x"></i>
						</div>
                        <span class="font-bold no-margins">
						</span>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> Bangalore</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $bangaloreGold['bills']; ?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> Karnataka</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $karnatakaGold['bills']; ?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="viewCustomers.php">View Details</a>
					</div>
				</div>
			</div>
            
            <!-- Tamil Nadu Bills -->
            <div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success">TN Bills<?php echo "(".($chennaiGold['bills']+$tamilnaduGold['bills']).")";   ?></h3>
						</div>
                        <div class="stats-icon pull-right">
                            <i style="color:#990000" class="fa fa-users fa-4x"></i>
						</div>
                        <span class="font-bold no-margins">
						</span>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> Chennai</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $chennaiGold['bills']; ?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> TamilNadu</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $tamilnaduGold['bills']; ?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="viewCustomers.php">View Details</a>
					</div>
				</div>
			</div>
            
            <!-- AP/T Bills -->
            <div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success">AP/T Bills<?php echo "(".($hyderabadGold['bills']+$andhraTelanganaGold['bills']).")";   ?></h3>
						</div>
                        <div class="stats-icon pull-right">
                            <i style="color:#990000" class="fa fa-users fa-4x"></i>
						</div>
                        <span class="font-bold no-margins">
						</span>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> Hyderabad</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $hyderabadGold['bills']; ?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> AP/T</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $andhraTelanganaGold['bills']; ?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="viewCustomers.php">View Details</a>
					</div>
				</div>
			</div>
			
            <!-- Todays Rate -->
			<?php
				$goldRate = mysqli_fetch_assoc(mysqli_query($con,"SELECT cash,transferRate FROM gold WHERE date='$date' and type='Gold' and city = 'AGPL000' order by id DESC"));
				$silverRate = mysqli_fetch_assoc(mysqli_query($con,"SELECT cash FROM gold WHERE date='$date' and type='Silver' and city = 'AGPL000' order by id DESC"));
			?>
            <div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success">Today's Rate</h3>
						</div>
                        <div class="stats-icon pull-right">
                            <i style="color:#990000" class="fa fa-rupee fa-4x"></i>
						</div>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label">Gold Rate:</small>
                                    <h4><i style="color:#990000" class="fa fa-rupee"></i> <?php echo $goldRate['cash'];?>/<?php echo $goldRate['transferRate']; ?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label">Silver Rate:</small>
                                    <h4><i style="color:#990000" class="fa fa-rupee"></i> <?php echo $silverRate['cash'];?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center">
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="#">View Details</a>
					</div>
				</div>
			</div>
            
		</div>		
        <?php  include("footer.php");  ?>
	</div>												
