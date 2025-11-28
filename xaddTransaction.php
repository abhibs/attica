<?php
    session_start();
    error_reporting(E_ERROR | E_PARSE);
    $type = $_SESSION['usertype'];
    if($type == 'Branch'){
        include("header.php");
        include("menu.php");
    }
    else{
        include("logout.php");
    }
    include("dbConnection.php");
    $date = date('Y-m-d');
    $branch = $_SESSION['branchCode'];
    $state = mysqli_fetch_assoc(mysqli_query($con,"SELECT SUBSTRING_INDEX(state, ' ', 1) as stateName, ws_access FROM branch WHERE branchId='$branch'"));
    
    /*  fetch the weighing scale access status */
	$ws_access=$state["ws_access"]; 

    /*  Code to fetch Customer transaction Details  */
    if(isset($_SESSION['customerID'])){
        $customerId = $_SESSION['customerID'];
        $phone = $_SESSION['mobile'];
        $cusDetail = mysqli_fetch_assoc(mysqli_query($con,"SELECT name,mobile FROM customer WHERE mobile='$phone' LIMIT 1"));
        //Existing Bill Count
        $XqueryResult = mysqli_fetch_assoc(mysqli_query($con,"SELECT count(id) AS totalBills FROM trans WHERE phone='$phone'"));
	}
    if(isset($_SESSION['bill'])){
        $margin = 0;
        $billId = $_SESSION['bill'];
        $ornamentResult = mysqli_fetch_assoc(mysqli_query($con,"SELECT SUM(weight) AS grossW, SUM(sWaste) AS stone , SUM(reading) AS reading ,SUM(gross) As grossA FROM ornament WHERE billId = '$billId' AND date='$date'"));
        if(isset($ornamentResult['grossA'])){
			$margin = $ornamentResult['grossA'] * 0.03;	
			$netATotal = $ornamentResult['grossA']-$margin;
			$marginPerc = round(($margin/$ornamentResult['grossA']) * 100,1);
		}
        //IMPS Details
		if($_SESSION['paymentType'] == 'NEFT/RTGS' || $_SESSION['paymentType'] == 'Cash/IMPS'){
			$impsData = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM bankdetails WHERE billID='$billId' AND date='$date' AND customerId='$_SESSION[customerID]' LIMIT 1"));
		}
	}
	else{		
		unset($_SESSION['metalType']);
		unset($_SESSION['rate1']);
		unset($_SESSION['paymentType']);
	}
?>
	<link rel="stylesheet" href="transaction.css">
<?php if(isset($_SESSION["transaction_error"])){?>
<div class="modal fade" id="error-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content" style="box-shadow: rgb(0 0 0 / 30%) 0px 19px 38px, rgb(0 0 0 / 22%) 0px 15px 12px;">
			<div class="modal-header" style="background: #7a2048;">
			<h2 class="modal-title" style="font-size:20px;color:#faf6f2;">TRANSACTION ERROR</h2>
				<div class="close error-close" data-dismiss="modal" onclick="unset_trans_error()" style="margin-top: -30px;color:#ffffff;"><div class="close_icon"> <i class="fa fa-times" aria-hidden="true"></i> </div></div>
			</div>
			<div class="modal-body" style="background: #faf6f2;">  
				<p class="text-danger text-center" id="exampleModalLabel1" style="text-transform:uppercase;font-size:18px;"><?php echo $_SESSION["transaction_error"];?></p>
			</div>
			<div class="modal-footer" style="margin-top:0px;">
			
			<button type="button" class="btn btn-danger center-block error-close" data-dismiss="modal" onclick="unset_trans_error()"> <i class="fa fa-times" aria-hidden="true"></i> CLOSE </button>
			</div>
		</div>
	</div> 
</div>
<?php } ?>
<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="col-xs-6">
                    <h3> &nbsp; <span class="fa_Icon fa fa-edit"></span> NEW TRANSACTION </h3>
                </div>
                <div class="col-xs-2">
        			<a class="pull-right" style="margin-right:10px;" id="open-alert-modal" title="Reset this bill"> <img src="images/reset-icon-btn.png" style="width:100px"/> </a>
				</div>
                <div class="col-xs-4 input-group">
                    <!--<a class="btn btn-info pull-right" style="margin-right:10px;" id="open-alert-modal"><i style="color:#ffa500" class="fa fa-refresh"></i> RESET BILL </a>-->
                    <span class="input-group-addon" style="background-color: #123C69;color:#ffa500;"><span class="fa fa-rupee"></span></span>
                    <input type="hidden" id='session_branchID' value="<?php echo $_SESSION['branchCode']; ?>" >
                    <input type="hidden" id='session_branchState' value="<?php echo $state['stateName']; ?>" >
                    <input type="text" name="available" id="available" class="form-control" readonly>
                </div>
                <div style="clear:both"></div>
                <div class="panel-body" style="border-radius:15px;box-shadow: rgb(60 64 67 / 30%) 0px 1px 2px 0px, rgb(60 64 67 / 15%) 0px 2px 6px 2px;background-color:#f5f5f5;">
                    <h4 align="center" class="col-sm-6 font-extra-bold no-margins">
                        <span class="fa_Icon fa fa-money"></span> TODAY'S RATE<hr>
                    </h4>
                    <h4 align="center" class="col-sm-6 font-extra-bold no-margins">
                        <span class="fa_Icon fa fa-users"></span> CUSTOMER DETAILS<hr>
                    </h4>
                    <form autocomplete="off" id="add-ornament" method="POST" action="xaddOrnament.php" enctype="multipart/form-data">                        
                        <div class="form-group">
							<div class="col-xs-2">
								<label class="text-success">Select Metal</label>
								<div class="input-group">
									<span class="input-group-addon"><span class="fa_Icon fa fa-diamond"> </span></span>
									<select class="form-control" required style="padding:0px 5px" name="gspd" id="gspd">
										<option value="Gold" selected="true">Gold</option>
										<option value="Silver">Silver</option>
									
									</select>
								</div>
							</div>
							<div class="col-sm-2">
								<label class="text-success">Payment Type</label>
								<div class="input-group">
									<span class="input-group-addon"><span class="fa_Icon fa fa-money"></span></span>
									<select  name="paymentType" style="padding:0px 5px" required id="paymentType" class="form-control">
										<option selected="true" disabled="disabled" value="">PAYMENT TYPE</option>
										<option value="Cash"> Cash</option>
										<option value="NEFT/RTGS">IMPS</option>
										<option value="Cash/IMPS">CASH/IMPS</option>
									</select>
								</div>
								<p class="text-danger ptype-error"></p>
							</div>
							<div class="col-xs-2">
								<label class="text-success">Today's Rate</label>
								<div class="input-group todays-rate">
									<span class="input-group-addon"><span class="fa_Icon fa fa-rupee"></span></span>
									<input type="text" id="ex2" name="ex2" readonly style="padding:0px 5px" class="form-control" placeholder="Today's Rate">
								</div>
							</div>
							<div class="col-sm-2">
								<label class="text-success">Customer Name</label>
								<div class="input-group">
									<span class="input-group-addon"><span class="fa_Icon fa fa-user"></span></span>
									<input type="text" name="name" id="name" class="form-control" style="padding:0px 5px" value="<?php echo $cusDetail['name']; ?>" readonly>
								</div>
							</div>
							<div class="col-sm-2">
								<label class="text-success">Mobile</label>
								<div class="input-group">
									<span class="input-group-addon"><span class="fa_Icon fa fa-phone-square"></span></span>
									<input type="text" name="phone" id="phone" class="form-control" style="padding:0px 5px" value="<?php echo $cusDetail['mobile']; ?>" readonly>
								</div>
							</div>
							<div class="col-sm-2">
								<label class="text-success">Existing bills</label>
								<div class="input-group">
									<span class="input-group-addon"><span class="fa_Icon fa fa-edit"></span></span>
									<input type="text" name="bill2" id="bill2"  class="form-control" value="<?php echo $XqueryResult['totalBills'];?>" readonly>
								</div>
							</div>
								
                            <div style="clear:both"></div>			
					<div class="accordion js-accordion col-sm-12">
						<div id="IMPSdetails" class="accordion__item js-accordion-item">
							<div class="accordion-header js-accordion-header"><i style="padding-top:15px" class="trans_Icon fa fa-bank"></i> IMPS DETAILS</div> 
							<div class="accordion-body js-accordion-body">
								<div class="accordion-body__contents">							
									<div class="transaction-body">
											<span class="IMPSdetails-error"></span>
											<div class="col-sm-3">
												<label class="text-success">Account Holder Name</label>
												<div class="input-group">
													<span class="input-group-addon"><span class="fa_Icon fa fa-user"></span></span>
													<input type="text" name="accountHolder" placeholder="Account Holder Name"  id="accountHolder" class="form-control" required value="<?php echo $impsData['accountHolder']; ?>">
												</div>
											</div>
											<div class="col-sm-3">
												<label class="text-success">Relationship </label>
												<div class="input-group">
													<span class="input-group-addon"><span class="fa_Icon fa fa-venus-mars"></span></span>
													<select  class="form-control m-b" name="relationship" id="relationship" required>
														<option selected="true" disabled="disabled" value="">RELATIONSHIP</option>
														<?php if($_SESSION['paymentType'] == "NEFT/RTGS" || $_SESSION['paymentType'] == "Cash/IMPS") {?>
														<option value="<?php echo $impsData['relationship']; ?>" selected><?php echo $impsData['relationship']; ?></option>
														<?php } ?>
														<option value="Myself">Myself</option>
														<option value="Father/Mother">Father / Mother</option>
														<option value="Husband/Wife">Husband / Wife</option>
														<option value="Son/Daughter">Son / Daughter</option>
														<option value="Brother/Sister">Brother / Sister</option>
														<option value="others">Others</option>
													</select>
												</div>
											</div>
											<div class="col-sm-3">
												<label class="text-success">Bank Name </label>
												<div class="input-group">
													<span class="input-group-addon"><span class="fa_Icon fa fa-building-o"></span></span>
													<input type="text" name="bankname" placeholder="Bank Name"  id="bankname" class="form-control" required value="<?php echo $impsData['bank']; ?>">
												</div>
											</div>
											<div class="col-sm-3">
												<label class="text-success">Branch Name </label>
												<div class="input-group">
													<span class="input-group-addon"><span class="fa_Icon fa fa-map-marker"></span></span>
													<input type="text" name="branchname" placeholder="Branch Name"  id="branchname" class="form-control" required value="<?php echo $impsData['branch']; ?>">
												</div>
											</div>
											<label class="col-sm-12"></label>
											<div class="col-sm-3">
												<label class="text-success">Account Number </label>
												<div class="input-group">
													<span class="input-group-addon"><span class="fa_Icon fa fa-credit-card-alt"></span></span>
													<input type="text" name="accountnumber" placeholder="Account Number" id="accountnumber" class="form-control" required value="<?php echo $impsData['account']; ?>">
												</div>
											</div>
											<div class="col-sm-3">
												<label class="text-success">IFSC Number </label>
												<div class="input-group">
													<span class="input-group-addon"><span class="fa_Icon fa fa-keyboard-o"></span></span>
													<input type="text" name="ifsc" maxlength="11" placeholder="IFSC Number" id="ifsc" class="form-control" required value="<?php echo $impsData['ifsc']; ?>">
												</div>
											</div>
											<div style="clear:both"></div>
											
									</div>						
								</div>
							</div><!-- end of accordion body -->
				</div><!-- end of accordion item -->
				<div class="accordion__item js-accordion-item active ornament-details">
					<div class="accordion-header js-accordion-header "><i style="padding-top:15px" class="trans_Icon fa fa-diamond"></i> ADD ORNAMENT DETAILS</div> 
					<div class="accordion-body js-accordion-body">
					<div class="accordion-body__contents">
                            <div style="clear:both"></div>
							
								<div class="transaction-body">
								<span class="ornament-detail-error"></span>
                                <div class="col-sm-3">
                                    <label class="text-success">Ornament Type</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><span class="fa_Icon fa fa-snowflake-o"></span> </span>
                                        <select id="type" style="padding:0px 3px" name="type" class="form-control" required>
    						<option selected="true" disabled="disabled" value="">SELECT ORNAMENT</option>

    						<!-- Gold ornaments -->
						<option data-metal="gold" value="24K Coin/Bar">24K Coin/Bar</option>
						<option data-metal="gold" value="22K Coin/Bar">22K Coin/Bar</option>
						<option data-metal="gold" value="Anklet">Anklet</option>
						<option data-metal="gold" value="Drops/Studs">Drops/Studs</option>
						<option data-metal="gold" value="Ring">Ring</option>
						<option data-metal="gold" value="Baby Bangles">Baby Bangles</option>
						<option data-metal="gold" value="Bangles">Bangles</option>
						<option data-metal="gold" value="Chain">Chain</option>
						<option data-metal="gold" value="Chain with Locket">Chain with Locket</option>
						<option data-metal="gold" value="Bracelet">Bracelet</option>
						<option data-metal="gold" value="Thala/Mangalya Chain with Black Beads">Thali Chain with Black Beads</option>
						<option data-metal="gold" value="Necklace">Necklace</option>
						<option data-metal="gold" value="Matti">Matti</option>
						<option data-metal="gold" value="Waist Belt/Chain">Waist Belt/Chain</option>
						 <option data-metal="gold" value="Locket">Locket</option>
						<option data-metal="gold" value="Others">Others</option>
								
	
					<!-- Silver ornaments -->
						<option data-metal="silver" value="Silver-Bar/Coin">Bar/Coin</option>
						<option data-metal="silver" value="Silver-Ornaments">Ornaments</option>
						<!-- <option data-metal="silver" value="Silver Plate/Utensils">Silver Plate/Utensils</option> -->
						<option data-metal="silver" value="Silver-Others">Others</option>
					</select>                                   
         
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <label class="text-success">No of Pieces</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><span class="fa_Icon fa fa-balance-scale"></span> </span>
                                        <input type="text" name="piece" id="piece" placeholder="No of pieces" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <label class="text-success">Ornament Weight</label>
                                    <?php if($ws_access==1){ ?>
									<span class="pull-right" id="clear_me" onClick="window.location.reload();return false;"><a>Clear</a></span>
									<?php } ?>
                                    <div class="input-group ornament-weight">
                                        <span class="input-group-addon"><span class="fa_Icon fa fa-balance-scale"></span> </span>
                                        <input type="text" name="weight" id="weight" placeholder="Weight" class="form-control" required>
                                    </div>
									<span class="weight-error text-danger"></span>
                                </div>
                                <div class="col-sm-3">
                                    <label class="text-success">Stone Weight</label>
                                    <div class="input-group ornament-sweight">
                                        <span class="input-group-addon"><span class="fa_Icon fa fa-balance-scale"></span> </span>
                                        <input type="text" name="sweight" required style="padding:0px 10px" id="sweight" placeholder="Stone weight" class="form-control" required>
                                    </div>
									<span class="sweight-error text-danger"></span>
                                </div>
                                <label class="col-sm-12"></label>
                                <div class="col-sm-3">
                                    <label class="text-success">Net Weight</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><span class="fa_Icon fa fa-balance-scale"></span></span>
                                        <input type="text" placeholder="Net Weight" readonly required name="reading" id="reading" class="form-control">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <label class="text-success">Purity</label>
                                    <div class="input-group purity-range">
                                        <span class="input-group-addon"><span class="fa_Icon fa fa-tag"></span></span>
                                        <select class="form-control m-b" name="nine" id="nine" required>
                                            <option selected="true" disabled="disabled" value="">PURITY</option>
                                            <option value="24Karat">24 Karat (92 to 99.9)</option>
											<option value="916">916 (HallMark) (88 to 91.6)</option>
											<option value="22KNON916">22K (NON 916) (80 to 87)</option>
											<option value="OT">Others (35 to 79)</option>
                                            <option value="Silver">Silver</option>
                                        </select>
                                    </div>
									<span class="purity-error text-danger"></span>
                                </div>
                                <div class="col-sm-3">
                                    <label class="text-success">Purity %</label>
                                    <div class="input-group purity-value">
                                        <span class="input-group-addon"><span class="fa_Icon fa fa-percent"></span></span>
                                        <input type="number" name="purity" placeholder="Purity" maxlength="4" required id="purity" class="form-control">
                                    </div>
									<span class="pValue-error text-danger"></span>
                                </div>
                                <div class="col-sm-3">
                                    <br>
                                    <button type="button" class="btn btn-success btn-block btn-transaction" id="submitTran" name="submitTran" onclick="submit_transaction()">
                                        <span style="color:#ffcf40" class="fa fa-plus"></span> ADD ORNAMENT
                                    </button>
                                    <br/>
                                </div>
                                <div style="clear:both"></div>
								</div>
                            
						</div>
					</div><!-- end of accordion body -->
				</div><!-- end of accordion item -->
			</div><!-- end of accordion -->				
                      
				</form>
				<div id="transaction_record">
                    <?php 
					 $bill = $_SESSION['bill'];
						
						if($bill!=""){
						
						    $sql = mysqli_query($con,"SELECT * FROM ornament WHERE billId='$bill' AND date='$date'");
						    $num_rows=mysqli_num_rows($sql);

						    $totalsql = mysqli_query($con,"SELECT metal,SUM(weight) AS Weight,SUM(sWaste) AS sWaste, SUM(reading) AS reading,SUM(gross) AS Gross,SUM(pieces) as totalPieces FROM ornament WHERE billId='$bill' AND date='$date'");
						    $totalResult = mysqli_fetch_assoc($totalsql);
						    if(isset($totalResult['Gross']) && isset($totalResult['reading']) ){
							    $totalPurity = round((($totalResult['Gross']/$totalResult['reading'])/$_SESSION['rate1'])*100,2);
						    }
						
						}
					?>
                      
						<div class="col-lg-8">
						<fieldset class="ornament-detail-box">
							<legend><i style="padding-top:15px" class="trans_Icon fa fa-list"></i> ORNAMENT DETAILS LIST</legend>
                            
								<div style="background-color:#fffafa;border-radius:5px;border:none;" class="panel-body">
									<div class="table-responsive" style="margin-top: -30px;">
                                        <table cellpadding="1" cellspacing="1" class="table styled-table">
                                            <thead>
                                                <tr class="text-success">
                                                    <th>METAL</th>
                                                    <th>ORNAMENT</th>
                                                    <th>WEIGHT</th>
                                                    <th>STONE</th>
                                                    <th>NET WEIGHT</th>
                                                    <th>PURITY</th>
                                                    <th>GROSS AMOUNT</th>
                                                    <th>ACTION</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ornament-list">
                                                <?php
                                                if($bill!=""){
                                                    
                                                    if($num_rows > 0){
                                                        while($row = mysqli_fetch_assoc($sql)){
														
                                                            if($row['nine']== "916"){
                                                                echo "<tr class='active-row'>";
                                                            }
                                                            else{
                                                                echo "<tr class='passive-row'>";
                                                            }
                                                            echo "<td>" . $row['metal'] . "</td>";
                                                            echo "<td>" . $row['type'] ."  (". $row['pieces']. ")</td>";
                                                            echo "<td>" . $row['weight'] . "</td>";
                                                            echo "<td>" . $row['sWaste'] . "</td>";
                                                            echo "<td>" . $row['reading'] . "</td>";
                                                            echo "<td>" . $row['purity'] . "</td>";
                                                            echo "<td>" . $row['gross'] . "</td>";
                                                            echo "<td><b><a class='text-danger'  title='Delete Record' onclick='delete_ornament(".$row['ornamentId'].")'><i class='fa fa-trash' aria-hidden='true'></i> Delete</a></b></td><tr>";
														
													    }
                                                   
                                                        echo "<tr class='totalResult'><td>Total</td>";
                                                        echo "<td>".$totalResult['totalPieces']."</td>";
                                                        echo "<td>".round($totalResult['Weight'],3)."</td>";
                                                        echo "<td>".round($totalResult['sWaste'],3)."</td>";
                                                        echo "<td>".round($totalResult['reading'],3)."</td>";
                                                        echo "<td>".$totalPurity."</td>";
                                                        echo "<td>".$totalResult['Gross']."</td>";
                                                        echo "<td></td></tr>";
                                                    }
                                                }
                                                ?>
                                            </tbody>
										</table>
                                    </div>
								</div>
                       </fieldset>
					   
					   <form method="POST" autocomplete="off" id="ornament-total" action="xaddOrnament.php" enctype="multipart/form-data">
							<input type="hidden" id="billId" value="<?php echo $_SESSION['bill']; ?>">
							<input type="hidden" name = "name" value = "<?php echo $cusDetail['name']; ?>">
							<input type="hidden" name = "phone" value = "<?php echo $cusDetail['mobile']; ?>">
							<input type="hidden" name = "bill2" value = "<?php echo $XqueryResult['totalBills']; ?>">
							
							<div class="hpanel boxShadow proceed" style="display:none">
								<div style="background:#fffafa;border-radius:5px" class="panel-body">
									<div class="col-sm-5">
										<label class="text-success">Packet Number</label>
										<div class="input-group">
										<span class="input-group-addon"><span class="fa_Icon fa fa-lock" required></span></span>
										<input type="text" name="packetNo" class="form-control packetNumberClass" required>
										</div>
									</div>
									
									<div class="col-sm-5">
										<label class="text-success">Employee Id</label>
										<div class="input-group">
										<span class="input-group-addon"><span class="fa_Icon fa fa-user" required></span></span>
										<input type="text" name="empId" class="form-control" required>
										</div>
									</div>

									<div class="col-sm-5" style="margin-top:23px;">								
										<button class="btn btn-success btn-block btn-transaction"  name="submitT" id="submitT" type="submit">
											<span style="color:#ffcf40" class="fa fa-check"></span> Proceed to Upload Docs
										</button>
									</div>
								</div>
							</div>
							
						</div>

						<div class="col-lg-4">
							<fieldset class="ornament-total">
								<legend><i style="padding-top:15px" class="trans_Icon fa fa-calculator"></i> ORNAMENT TOTAL</legend>
								
								<div style="background-color:#fffafa;border-radius:5px;border:none;" class="panel-body">
								<div class="col-sm-6">
									<label class="text-success">Gross Wt</label>
									<div class="input-group">
										<span class="input-group-addon"><span class="fa_Icon fa fa-balance-scale"></span></span>
										<input type="text" name="grossW" readonly id="grossW" class="form-control" placeholder="Gross" value="<?php echo round($ornamentResult['grossW'],2); ?>">
									</div>
								</div>
								<div class="col-sm-6">
									<label class="text-success">Gross Amount</label>
									<div class="input-group">
										<span class="input-group-addon"><span class="fa_Icon fa fa-rupee"></span></span>
										<input type="text" name="grossA" id="grossA" readonly class="form-control" value="<?php echo $ornamentResult['grossA'];?>">
									</div>
								</div>
								<div class="col-sm-6">
									<label class="text-success">Net Weight</label>
									<div class="input-group">
										<span class="input-group-addon"><span class="fa_Icon fa fa-balance-scale"></span></span>
										<input type="text" name="netW" readonly id="netW" class="form-control" value="<?php echo round($ornamentResult['reading'],3);?>">
									</div>
								</div>
								<div class="col-sm-6">
									<label class="text-success">Margin Amount</label>
									<div class="input-group">
										<span class="input-group-addon"><span class="fa_Icon fa fa-rupee"></span></span>
										<input type="text" name="margin" readonly id="margin" class="form-control" value="<?php echo round($margin); ?>">
									</div>
								</div>
								<div class="col-sm-6">
									<label class="text-success">Margin %</label>
									<div class="input-group">
										<span class="input-group-addon"><span class="fa_Icon fa fa-rupee"></span></span>
									<input type="number" name="marginP" id="marginP" class="form-control" min="-4" max="6"step="0.01" value="<?php echo $marginPerc ?>" oninput="
												if (this.value !== '') {
													let v = parseFloat(this.value);
													if (v < -4) this.value = -4;
													else if (v > 6) this.value = 6;
												}"required>
										<!--<input type="text" name="marginP" id="marginP" class="form-control" value="<!?php echo $marginPerc?>">-->
										<!--<input type="text" name="marginP" id="marginP" class="form-control" value="<!?php echo $marginPerc?>" readonly>-->
									</div>
								</div>
								<div class="col-sm-6">
									<label class="text-success">Net Amount</label>
									<div class="input-group">
										<span class="input-group-addon"><span class="fa_Icon fa fa-rupee"></span></span>
										<input type="text" name="net1" id="net1" class="form-control" value="<?php echo round($netATotal); ?>" readonly>
									</div>
								</div>
								<div class="col-sm-6">
									<label class="text-success">Amount Payable</label>
									<div class="input-group">
										<span class="input-group-addon"><span class="fa_Icon fa fa-rupee" required></span></span>
										<input type="text" name="payable" id="payableA" class="form-control" required>
										<!--<input type="text" name="payable" id="payableA" class="form-control" value="<?php echo round($netATotal); ?>" readonly required>-->
									</div>
								</div>
								<div id="neft-rtgs">
							 
									<div class="col-sm-6">
										<label class="text-success">IMPS</label>
										<div class="input-group">
											<span class="input-group-addon"><span class="fa_Icon fa fa-rupee" required></span></span>
											<input type="text" name="impsA" id="impsA" class="form-control" required>
										</div>
									</div>
									<div class="col-sm-6">
										<label class="text-success">Cash</label>
										<div class="input-group">
											<span class="input-group-addon"><span class="fa_Icon fa fa-rupee" required></span></span>
											<input type="text" name="cashA" id="cashA" class="form-control" required readonly>
										</div>
									</div>
							
								</div>
									<div class="col-sm-2" id="done_checkbox" style="margin-top:23px;">
										<label style="padding-top:5px;"><input type="checkbox" id="done" ><span class="text-success"> Done </span></label>
									</div>
								</div>
							</fieldset>
							
						</div>
				
						<div class="col-lg-12"></div>
					</div>
								
						<div class="col-lg-8"></div>
						<div class="col-lg-12"></div>
                           
						</form>
					</div>
				</div>
            </div>
        </div>
    </div>
    <div style="clear:both"></div>
	<div class="modal fade" id="alert-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content" style="box-shadow: rgb(0 0 0 / 30%) 0px 19px 38px, rgb(0 0 0 / 22%) 0px 15px 12px;">
				<div class="modal-header" style="background-color:#EDA909;">				
					<div class="close" data-dismiss="modal"><div class="close_icon"> <i class="fa fa-times" aria-hidden="true"></i> </div></div>
				</div>
				<form method="post" action="compose_mail" id="compose_mail" enctype="multipart/form-data">
				<div class="modal-body">  
				<h4 class="modal-title" id="exampleModalLabel1">DO YOU WANT TO RESET YOUR BILL TRANSACTION ?</h4>
				</div>
				<div class="modal-footer">                                       
					<button type="button" class="btn btn-danger pull-left" data-dismiss="modal"> <i class="fa fa-thumbs-o-down" aria-hidden="true"></i> NO</button>
					<button type="button" class="btn btn-primary pull-right" onClick="reset_transaction()"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i> YES </button>
				</div>
				</form>
			</div>
		</div> 
	</div>

	<?php include("footer-transaction.php"); ?>
	<script>
	
		var bill_id = "<?php echo $_SESSION['bill'];?>" ;
		var paymentType = "<?php echo $_SESSION['paymentType'];?>" ;
		var gspd = "<?php echo $_SESSION['metalType'];?>" ;
		var rate = "<?php echo $_SESSION['rate1'];?>" ;
		var transaction_error ="<?php echo $_SESSION['transaction_error'];?>" ;
		
		var ws_access=<?php echo $ws_access;?>;
		
		if(paymentType=='NEFT/RTGS' || paymentType=='Cash'){
        	$("#neft-rtgs").css("display","none");
		}
		
		$(document).ready(function(){
			
			trans_error();
			bill_session();
			trans_session();
			
			function trans_error(){
				if(transaction_error==""){
					$('#error-modal').modal('hide');
				}else{
					$('#error-modal').modal();					
				}
			}
			
			function bill_session(){
				if(bill_id==""){
					$("#transaction_record").css("display","none");
				}else{
					$("#transaction_record").css("display","block");
					$("#accountHolder,#relationship,#bankname,#branchname,#accountnumber,#ifsc").attr("disabled","disabled");
				}
			}
			
			function trans_session(){
				
				if(paymentType=="" || paymentType== null){
					document.getElementById("paymentType").disabled = false;
					document.getElementById("gspd").disabled = false;
					$("#IMPSdetails").css("display","none");

				}else{
					
					document.getElementById("paymentType").disabled = true;
					document.getElementById("gspd").disabled = true;
					document.getElementById("paymentType").value = paymentType;
					document.getElementById("ex2").value = rate;
					var metalType = document.getElementById("gspd");
					metalType.value = gspd;
					var pType = document.getElementById("paymentType");
					pType.value = paymentType;
				}
				
				if(paymentType=="Cash"){
					$("#neft-rtgs").css("display","none");
					$("#IMPSdetails").css("display","none");
					$("#impsA,#cashA").attr('required', false);
				}
				
				if(paymentType=="Cash/IMPS"){
					
					$("#neft-rtgs").css("display","block");
					$("#IMPSdetails").css("display","block");
					$("#impsA,#cashA").attr('required', true);
					document.getElementById("accountHolder").disabled = true;
					document.getElementById("relationship").disabled = true;
					document.getElementById("bankname").disabled = true;
					document.getElementById("branchname").disabled = true;
					document.getElementById("accountnumber").disabled = true;
					document.getElementById("ifsc").disabled = true;
					
				}
				
				if(paymentType=="NEFT/RTGS" || paymentType=="IMPS"){
			
					$("#neft-rtgs").css("display","none");
					$("#IMPSdetails").css("display","block");
					$("#impsA,#cashA").attr('required', true);
					$('#impsA').attr("readonly", true);
					document.getElementById("accountHolder").disabled = true;
					document.getElementById("relationship").disabled = true;
					document.getElementById("bankname").disabled = true;
					document.getElementById("branchname").disabled = true;
					document.getElementById("accountnumber").disabled = true;
					document.getElementById("ifsc").disabled = true;
					
				}
			}
		
		});
		

        $(document).ready(function(){
            $('#submitT').attr("disabled", true);
            $('#done').change(function() {
                if(this.checked) {
					$('.proceed').css("display", "block")
                    $('#submitT').attr("disabled", false);
                }
                else{
					
                    $('#submitT').attr("disabled", true);
					$('.proceed').css("display", "none")
                }
            });
        });

        $(document).ready(function(){
            const state = {
                Karnataka : 'KA',
                Tamilnadu : 'TN',
                Telangana : 'TS',
                Andhra : 'AP',
                Chennai : 'CH'
            };
            var branchState = $('#session_branchState').val();
            $(".packetNumberClass").val(state[branchState]);
        });

        $(document).ready(function(){
			// on change of amount payable
            $("#payableA").keyup(function(){
                $("#done").prop("checked", false);
				$('#submitT').attr("disabled", true);
				$('.proceed').css("display", "none");
				var gross = parseInt($("#grossA").val());
				var payable = Math.round($("#payableA").val());
				var margin = Math.round(gross - payable);
				var marginP = parseFloat((margin/gross)*100).toFixed(2);
				var payType = $("#paymentType").val();
				
				if(payType=="Cash"){
					$("#cashA").val(payable);
					var cashA = $("#cashA").val();
					var impsA = Math.round(payable) - Math.round(cashA);
					$("#impsA").val(impsA);
				}
				
				if(payType=="NEFT/RTGS"){
					$("#impsA").val(payable);
					var impsA = $("#impsA").val();
					var cashA = Math.round(payable) - Math.round(impsA);
					$("#cashA").val(cashA);
				}	
				
				if(isNaN(payable) || payable==""){					
					var margin_percentage = 3;
					var margin_amount= Math.round((margin_percentage * gross)/100);
					var net_amount = Math.round(gross-margin_amount);
		
					$("#margin").val(margin_amount);
					$("#net1").val(net_amount);
					$("#marginP").val(margin_percentage); 
				}else{
					$("#margin").val(margin);
					$("#net1").val(payable);
					$("#marginP").val(marginP); 
					
				}
            });
			// on change of margin percentage
			$("#marginP").change(function(){
				var gross = $("#grossA").val();
				var marginP = parseFloat($("#marginP").val()).toFixed(2);
				var margin = Math.round((marginP * gross)/100);
				var net1 = Math.round(gross-margin);
				$("#margin").val(margin);
                $("#net1").val(net1);
            });
        });

        $(document).ready(function(){
            $("#paymentType").change(function(){
				//Remove error class if payment type is selected
				$(".ptype-error").html("");
				$("#paymentType").removeClass("error");
				$(".tr-error").html("");
                //CHANGE DIV
                var type = $("#paymentType").val();
				
				if(type == "Cash"){
					$("#IMPSdetails").hide();
					$("#neft-rtgs").css("display","none");
					$('#impsA,#cashA').prop('required',false);
					$("#accountHolder").attr('required', false);
					$("#bankname").attr('required', false);
					$("#relationship").attr('required', false);
					$("#branchname").attr('required', false);
					$("#accountnumber").attr('required', false);
					$("#ifsc").attr('required', false);
				}else if(type=="NEFT/RTGS"){
					$("#IMPSdetails").show();
					$("#neft-rtgs").css("display","none");
					$('#impsA,#cashA').prop('required',false);
					$("#accountHolder").attr('required', true);
					$("#bankname").attr('required', true);
					$("#relationship").attr('required', true);
					$("#branchname").attr('required', true);
					$("#accountnumber").attr('required', true);
					$("#ifsc").attr('required', true);
				}else{
					$("#IMPSdetails").show();
					$("#neft-rtgs").css("display","block");
					$('#impsA,#cashA').prop('required',true);
					$("#accountHolder").attr('required', true);
					$("#bankname").attr('required', true);
					$("#relationship").attr('required', true);
					$("#branchname").attr('required', true);
					$("#accountnumber").attr('required', true);
					$("#ifsc").attr('required', true);
				}

                //RATE
                var data=$('#gspd').val();
                var dat=$('#paymentType').val();
                var res=$.ajax({
                    type: "POST",
                    url: "getToday.php",
                    data:{data:data,dat:dat},

                });
                res.done(function(msg){
					if(msg==0){
						$(".todays-rate").css("border", "1px solid #990000");
						$(".todays-rate").after("<span class='tr-error text-danger'>TODAYS RATE IS NOT SET</span>");
						$("#ex2").val(msg);
					}else{
						$(".todays-rate").css("border", "none");
						$("#ex2").val(msg);
					}
                    
                });
            });
        });

         $(document).ready(function(){
            var branch  = $("#session_branchID").val();
            var req = $.ajax({
				url:"xbalance.php",
				type:"POST",
				data:{branchId:branch},
				dataType:'JSON'
			});
            req.done(function(e){
                var available = e.balance;
                $("#available").val("Available Balance : "+available);
			});
		});

        $(document).ready(function(){
            $("#impsA").change(function(){				
                var impsA = parseInt($("#impsA").val());				
                var amountP = parseInt($("#payableA").val());
                var cashA = amountP - impsA;
                $("#cashA").val(cashA);				
            });
        });

		$(document).ready(function(){
			$('#submitTran').prop('disabled', true);
			$('#open-alert-modal').click(function(e){
				$('#alert-modal').modal('show');
			});
		});
		// Submitting transaction details
 		function submit_transaction(){
		
			$(".ptype-error").html("");
			$("#paymentType").removeClass("error");
			$("#IMPSdetails .input-group,.ornament-details .input-group").css("border", "");
			$("#IMPSdetails,.ornament-details").css("box-shadow", "");
			$(".IMPSdetails-error, .ornament-detail-error").html("");

			var paymentType = $("#paymentType").val();
			var paymentVal = $("#ex2").val();
			
			if(paymentType=="" || paymentType==null || paymentVal==0 || paymentVal==null){
				
				$(".ptype-error").html("Payment Type or Rate is not set");
				$("#paymentType").addClass("error");

			}else{

				if(paymentType=="NEFT/RTGS" || paymentType=="Cash/IMPS"){
					if($('#relationship option:selected').val()=="" || $('#accountHolder').val()=="" || $('#bankname').val()=="" || $('#branchname').val()=="" || $('#accountnumber').val()=="" || $('#ifsc').val()==""){
						$("#IMPSdetails .input-group").css("border", "1px solid #990000");
						$("#IMPSdetails").css("box-shadow", "0px 0px 5px #c0392b");
						$(".IMPSdetails-error").html("<p class='text-danger' style='font-size:12px;margin-left:10px;'>Please fill all the bank details</p>");
						//$( "#IMPSdetails .js-accordion-header" ).trigger("click");
						$( "#IMPSdetails .js-accordion-header" ).addClass("active");
						$( "#IMPSdetails .js-accordion-body" ).css("display","block");
						return;
					}
				}
				
				if($('#type option:selected').val()=="" || $('#piece').val()=="" || $('#weight').val()=="" || $('#sweight').val()=="" || $('#purity').val()=="" || $('#nine option:selected').val()=="") {
					
					$(".ornament-details .input-group").css("border", "1px solid #990000");
					$(".ornament-details").css("box-shadow", "0px 0px 5px #c0392b");
					$(".ornament-detail-error").html("<p class='text-danger' style='font-size:12px;margin-left:10px;'>Please fill all the ornament details</p>");
					return;
				} else {
					$("#transaction_record").css("display","block");
					
					var submitTransaction = "submitTransaction";
					$.ajax({
						url: $('#add-ornament').attr('action'),
						url: "xaddOrnament.php",
						type: "post",
						data: $('#add-ornament').serialize() + "&submitTransaction=" + submitTransaction,
						dataType: 'json',
						success: function(response){
							
							if(ws_access==1){
								location.reload();					
							}else{
							
							
								$('#ornament-list').html(response.ornamentList);
								$("#type,#nine").prop("selectedIndex", 0);
								$('#piece,#weight,#sweight,#reading,#purity').val('');

								$("#grossW").val(response.grossW);
								$("#netW").val(response.netW);
								$("#grossA").val(response.grossA);
								$("#margin").val(response.margin);
								$("#net1").val(response.net1);
								$("#marginP").val(response.marginP);
								
								// $("#payableA").val(response.net1);
								
								if(paymentType!="" || paymentType!= null){
									document.getElementById("paymentType").disabled = true;
									document.getElementById("gspd").disabled = true;
									var metalType = document.getElementById("gspd");
									metalType.value = response.gspd;
									var pType = document.getElementById("paymentType");
									pType.value = paymentType;

								}
		
								if(response.paymentType=="Cash"){
									$("#neft-rtgs").css("display","none");
									document.getElementById("accountHolder").disabled = true;
									document.getElementById("relationship").disabled = true;
									document.getElementById("bankname").disabled = true;
									document.getElementById("branchname").disabled = true;
									document.getElementById("accountnumber").disabled = true;
									document.getElementById("ifsc").disabled = true;
									$('#impsA,#cashA').prop('required',false);
								}
		
								if(response.paymentType=="NEFT/RTGS" || response.paymentType=="IMPS"){
									$("#neft-rtgs").css("display","none");
									document.getElementById("accountHolder").disabled = true;
									document.getElementById("relationship").disabled = true;
									document.getElementById("bankname").disabled = true;
									document.getElementById("branchname").disabled = true;
									document.getElementById("accountnumber").disabled = true;
									document.getElementById("ifsc").disabled = true;
								}
								
								if(response.paymentType=="Cash/IMPS"){
									$("#neft-rtgs").css("display","block");
									document.getElementById("accountHolder").disabled = true;
									document.getElementById("relationship").disabled = true;
									document.getElementById("bankname").disabled = true;
									document.getElementById("branchname").disabled = true;
									document.getElementById("accountnumber").disabled = true;
									document.getElementById("ifsc").disabled = true;
									$('#impsA,#cashA').prop('required',true);
								}
							}

						}
					});

				}
			}
		}
		
		$('.ornament-details .form-control').filter('[required]').on('keyup',function() {
			if($('#type option:selected').val()=="" || $('#piece').val()=="" || $('#weight').val()=="" || $('#sweight').val()=="" || $('#purity').val()=="" || $('#nine option:selected').val()==""){
				$('#submitTran').prop('disabled', true);
			}else{
				$('#submitTran').prop('disabled', false);
			}
		});
		
		$('#IMPSdetails .form-control').filter('[required]').on('keyup',function() {
			if($('#relationship option:selected').val()=="" || $('#accountHolder').val()=="" || $('#bankname').val()=="" || $('#branchname').val()=="" || $('#accountnumber').val()=="" || $('#ifsc').val()==""){
				$('#submitTran').prop('disabled', true);
			}else{
				$('#submitTran').prop('disabled', false);
			}
		});
		
		$('#type').filter('[required]').on('change',function() {
			if($('#type option:selected').val()=="" || $('#piece').val()=="" || $('#weight').val()=="" || $('#sweight').val()=="" || $('#purity').val()=="" || $('#nine option:selected').val()==""){
				$('#submitTran').prop('disabled', true);
			}else{
				$('#submitTran').prop('disabled', false);
			}
		});
		
		$('#relationship').filter('[required]').on('change',function() {
			if($('#relationship option:selected').val()=="" || $('#accountHolder').val()=="" || $('#bankname').val()=="" || $('#branchname').val()=="" || $('#accountnumber').val()=="" || $('#ifsc').val()==""){
				$('#submitTran').prop('disabled', true);
			}else{
				$('#submitTran').prop('disabled', false);
			}
		});	
		
		function delete_ornament(ornament_id){
			var billId = $("#billId").val();
			$.ajax({
				url: "xdeleteOrnament.php",
				type: "post",
				data: { type: "physical", id : ornament_id, billId : billId},
				dataType: 'json',
				success: function(response){

					$('#ornament-list').html(response.ornamentList);
					$("#type,#nine").prop("selectedIndex", 0);
					$('#piece,#weight,#sweight,#reading,#purity').val('');
					$("#grossW").val(response.grossW);
					$("#netW").val(response.netW);
					$("#grossA").val(response.grossA);
					$("#margin").val(response.margin);
					$("#net1").val(response.net1);
					$("#marginP").val(response.marginP);
					
				// 	$("#payableA").val(response.net1);
					
				}
			}); 
		}

		function reset_transaction(){
			$.ajax({
				url: "xaddOrnament.php",
				type: "post",
				data: { submitTs: "submitTs"},
				success: function(response){
				setTimeout("location.reload(true);", 1000);
					
				}
			}); 
		}

		function unset_trans_error(){
			var transaction_error = "<?php unset($_SESSION['transaction_error']);?>";
		}
		
		$(document).keypress(
			function(event){
			if (event.which == '13') {
			event.preventDefault();
			}
		});
		
		$( "#submitT" ).one( "click", function( event ) {
			document.getElementById("submitT").submit();
			$('#submitT').attr("disabled", true);				
			
		}); 
		
	</script>

	<script>	
		$(document).ready(function(){
			

			if(ws_access==1){
				
				$("#weight").focusout(function() {				
					//var weight=$("#weight").val();
					//var weight = parseFloat($("#weight").val()).toFixed(2);
					var weight = ParseFloat($("#weight").val(),2);
					$("#weight").val(weight);

					if(weight=="" || isNaN(weight)){
						document.getElementById('weight').value = '';
						document.getElementById('reading').value = '';
						$("#weight").removeAttr('readonly');
					}else{
						$("#weight").prop('readonly', true);					
					}				
				});
				
				$("#weight").on('keypress',function(e) {
   
					var wgtValue = document.getElementById("weight").value.trim();
					var wgt = wgtValue.replace(/[^\d.-]/g, '');

					$("#weight").val(wgt);
					setInterval(function(){
						$("#weight").trigger("focusout");
					}, 1500);
					
				});
				
				$('#weight').bind("cut copy paste",function(e) {
					e.preventDefault();
				});	
				
				function ParseFloat(str,val) {
					str = str.toString();
					str = str.slice(0, (str.indexOf(".")) + val + 1); 
					return Number(str);   
				}				
				
			}
			
		});
	</script>
	<script>
		document.getElementById('gspd').addEventListener('change', function () {
			const selectedMetal = this.value.toLowerCase();
			const ornamentSelect = document.getElementById('type');
			const options = ornamentSelect.querySelectorAll('option');

			options.forEach(option => {
				const metal = option.getAttribute('data-metal');

				if (!metal || metal === selectedMetal) {
					option.style.display = '';
				} else {
					option.style.display = 'none';
				}
			});

			// Reset selected value if not valid for selected metal
			if (ornamentSelect.selectedOptions.length > 0) {
				const selectedOption = ornamentSelect.selectedOptions[0];
				if (selectedOption.getAttribute('data-metal') !== selectedMetal) {
					ornamentSelect.value = '';
				}
			}
		});

		// Trigger change on page load to apply filter for default selection (Gold)
		window.addEventListener('DOMContentLoaded', function () {
			document.getElementById('gspd').dispatchEvent(new Event('change'));
		});
	</script>

