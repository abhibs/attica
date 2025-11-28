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
	$branchId = $_SESSION['branchCode'];
	
	$closedRowSQL = mysqli_query($con,"SELECT * FROM closing Where date='$date' and branchId = '$branchId'");
	$closedRowCount = mysqli_num_rows($closedRowSQL);

	$lastChequesRow = mysqli_fetch_assoc(mysqli_query(
		$con,
		"SELECT cheques
		   FROM closing
		  WHERE branchId='$branchId'
		  ORDER BY date DESC, closingID DESC
		  LIMIT 1"
	  ));
	  $lastCheques = isset($lastChequesRow['cheques']) && $lastChequesRow['cheques'] !== null
		? (int)$lastChequesRow['cheques']
		: 0;
?>
<style>
	#results img{
		width:100px;
	}
	#wrapper{
		background: #f5f5f5;
	}
	
	#wrapper h1,#wrapper h3{
		text-transform:uppercase;
		font-weight:600;
		font-size: 20px;
		color:#123C69;
	}
	
	#wrapper h4{
		text-transform:uppercase;
		font-weight:600;
		font-size: 16px;
		color:#123C69;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
		background-color:#fffafa;
	}
	.quotation h3{
		color:#123C69;
		font-size: 18px!important;
	}
	.text-success{
		color:#123C69;
		text-transform:uppercase;
		font-weight:bold;
		font-size: 11px;
	}
	.btn-primary{
		background-color:#123C69;
	}
	.btn-info{
		background-color:#123C69;
		border-color:#123C69;
		font-size:12px;
	}	
	.btn-info:hover, .btn-info:focus, .btn-info:active, .btn-info.active{
		background-color:#123C69;
		border-color:#123C69;
	}
	.fa_Icon{
		color:#ffa500;
	}
	thead {
		text-transform:uppercase;
		background-color:#123C69;

	}
	thead tr{
		color: #f2f2f2;
		font-size:12px;
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

	.modaldesign {
		float: right;
		cursor: pointer;
		padding: 5px;
		background:none;
		color: #f0f8ff;
		border-radius: 5px;
		margin: 15px;
		font-size: 20px;
	}
	#available{
		text-transform:uppercase;
	}
	.panel-heading{
		margin-bottom: 15px;
	}
	.panel-box{
		margin-top: 20px;
		border: 4px solid #fff;
		border-radius:10px;
		padding: 10px;
		overflow: hidden;
		background-image: -moz-linear-gradient(top, #f5f5f5, #f6f2ec);
		background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #f5f5f5),color-stop(1, #f6f2ec));
		filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#f5f5f5', EndColorStr='#f6f2ec');
		-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorStr='#f5f5f5', EndColorStr='#f6f2ec')";
		-moz-box-shadow: 0 0 2px rgba(0, 0, 0, 0.35), 0 85px 180px 0 #fff, 0 12px 8px -5px rgba(0, 0, 0, 0.85);
		-webkit-box-shadow: 0 0 2px rgb(0 0 0 / 35%), 0 85px 810px -68px #fff, 0 12px 8px -5px rgb(0 0 0 / 65%);
		box-shadow: 0 0 2px rgb(0 0 0 / 35%), 0 85px 180px 0 #fff, 0 12px 8px -5px rgb(0 0 0 / 85%);	
	}
	input[data-readonly] {
		pointer-events: none;
		background-color:#fffafa;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<?php if($closedRowCount == 0){
				$totalTrans = mysqli_fetch_assoc(mysqli_query($con,"SELECT SUM(amountPaid) AS total,SUM(releases) AS rel FROM trans WHERE branchId='$branchId' AND status='Approved' AND date='$date'"));
				$goldSQL = mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(id) AS goldCount,ROUND(SUM(grossW),2) AS grossW,ROUND(SUM(netW),2) AS netW,ROUND(SUM(grossA),2) AS grossA,ROUND(SUM(netA),2) AS netA FROM trans WHERE branchId='$branchId' AND status='Approved' AND metal='Gold' AND date='$date'"));
				$silverSQL = mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(id) AS silverCount,ROUND(SUM(grossW),2) AS grossW,ROUND(SUM(netW),2) AS netW,ROUND(SUM(grossA),2) AS grossA,ROUND(SUM(netA),2) AS netA FROM trans WHERE branchId='$branchId' AND status='Approved' AND metal='Silver' AND date='$date'"));
			?>
			<div class="hpanel panel-box">
				<div class="panel-heading">
					<h3 class="text-success no-margins"><span style="color:#900" class="fa fa-file-text"></span> <b>DAILY CLOSING</b>
					<button style="float:right" onclick="window.location.reload();" class="btn btn-success"><b><i style="color:#ffcf40" class="fa fa-spinner"></i> RELOAD</b></button></h3>
				</div>
				<div class="panel-body">
					<form method="POST" action="add.php">
						<input type="hidden" name="branchId" id='session_branchID' value="<?php echo $_SESSION['branchCode']; ?>">
						<div class="col-sm-2">
							<label class="text-success">OPENING BALANCE</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
								<input type="text" readonly class="form-control" name="open" id="open">
							</div>
						</div>
						<div class="col-sm-2">
							<label class="text-success">FUND REQUESTED</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
								<input type="text" name="totalamount" readonly id="totalamount" class="form-control">
							</div>
						</div>
						<div class="col-sm-2">
							<label class="text-success">FUND RECEIVED</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
								<input type="text" name="fundR" readonly id="fundR" class="form-control" >
							</div>
						</div>
						<div class="col-sm-2">
							<label class="text-success">FUND TRANSFERED</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
								<input type="text" name="fundTranfer" readonly id="fundTranfer" class="form-control" >
							</div>
						</div>
						<div class="col-sm-2">
							<label class="text-success">TOTAL TRANSACTIONS</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-edit"></span></span>
								<input type="text" name="totalTran" readonly id="totalTran" class="form-control" value="<?php echo $goldSQL['goldCount']+$silverSQL['silverCount']+0; ?>">
							</div>
						</div>
						<div class="col-sm-2">
							<label class="text-success">TRANSACTION AMOUNT</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
								<input type="text" name="totalTranAmount" readonly id="totalTranAmount" class="form-control" value="<?php echo $totalTrans['total']+$totalTrans['rel']+0; ?>">
							</div>
						</div>
						<label class="col-sm-12"><br></label>
						<div class="col-sm-2">
							<label class="text-success">EXPENSE</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-money"></span></span>
								<input type="text" name="todaysExpense" readonly id="todaysExpense" class="form-control">
							</div>
						</div>
						<div class="col-sm-2">
							<label class="text-success">GROSS WEIGHT (<span style="color:#b8860b">GOLD</span>)</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-balance-scale"></span></span>
								<input type="text" name="grossWeightG" id="grossWeightG" readonly class="form-control" value="<?php echo $goldSQL['grossW']+0; ?>" >
							</div>
						</div>
						<div class="col-sm-2">
							<label class="text-success">NET WEIGHT (<span style="color:#b8860b">GOLD</span>)</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-balance-scale"></span></span>
								<input type="text" name="netWeightG" id="netWeightG" readonly class="form-control" value="<?php echo $goldSQL['netW']+0; ?>" >
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success">GROSS AMOUNT(<span style="color:#b8860b">GOLD</span>)</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
								<input type="text" name="grossAmountG" id="grossAmountG" readonly class="form-control" value="<?php echo $goldSQL['grossA']+0; ?>" >
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success">NET AMOUNT(<span style="color:#b8860b">GOLD</span>)</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
								<input type="text" name="netAmountG" id="netAmountG" readonly class="form-control" value="<?php echo $goldSQL['netA']+0; ?>" >
							</div>
						</div>
						<label class="col-sm-12"><br></label>
						<div class="col-sm-3">
							<label class="text-success">GROSS WEIGHT (<span style="color:#b8860b">SILVER</span>)</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-balance-scale"></span></span>
								<input type="text" name="grossWeightS" id="grossWeightS" readonly class="form-control" value="<?php echo $silverSQL['grossW']+0; ?>">
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success">NET WEIGHT(<span style="color:#b8860b">SILVER</span>)</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-balance-scale"></span></span>
								<input type="text" name="netWeightS" id="netWeightS" readonly class="form-control" value="<?php echo $silverSQL['netW']+0; ?>">
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success">GROSS AMOUNT(<span style="color:#b8860b">SILVER</span>)</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
								<input type="text" name="grossAmountS" id="grossAmountS" readonly class="form-control" value="<?php echo $silverSQL['grossA']+0; ?>">
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success">NET AMOUNT(<span style="color:#b8860b">SILVER</span>)</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
								<input type="text" name="netAmountS" id="netAmountS" readonly class="form-control" value="<?php echo $silverSQL['netA']+0; ?>">
							</div>
						</div>
						<label class="col-sm-12"><br></label>
						<!-- Cheques in Branch (count + value) -->
						<div class="col-sm-12">
						<label class="text-success">Cheques Left in Branch</label>
						<div class="row">
							<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon">
								<span style="color:#990000" class="fa fa-file-text-o"></span>
								</span>
								<input type="number" min="0" step="1"
									name="cheques" id="chequesCount"
									class="form-control" value="<?php echo $lastCheques; ?>">
								<input type="hidden" name="cheques_original" value="<?php echo $lastCheques; ?>">
							</div>
							</div>
						</div>
					</div>

						<label class="col-sm-12"><br></label>
						<h3> &nbsp; <span style="color:#900" class="fa fa-money"></span> TODAY'S DENOMINATIONS</h3>
						<table class="table table-striped table-bordered table-hover">
							<tbody>
								<tr class="text-success" align="center">
									<td><b><span style="color:#990000" class="fa fa-money"></span> 2000 X</b></td>
									<td><b><span style="color:#990000" class="fa fa-money"></span> 500 X</b></td>
									<td><b><span style="color:#990000" class="fa fa-money"></span> 200 X</b></td>
									<td><b><span style="color:#990000" class="fa fa-money"></span> 100 X</b></td>
									<td><b><span style="color:#990000" class="fa fa-money"></span> 50 X</b></td>
									<td><b><span style="color:#990000" class="fa fa-money"></span> 20 X</b></td>
									<td><b><span style="color:#990000" class="fa fa-money"></span> 10 X</b></td>
									<td><b><span style="color:#990000" class="fa fa-money"></span> 5 X</b></td>
									<td><b><span style="color:#990000" class="fa fa-money"></span> 2 X</b></td>
									<td><b><span style="color:#990000" class="fa fa-money"></span> 1 X</b></td>
								</tr> 
								<tr>
									<td><input type="text" name="aa" id="aa" class="form-control" onchange=javascript:calls1(this.form);></td>
									<td><input type="text" name="cc" id="cc" class="form-control" onchange=javascript:calls3(this.form);></td>
									<td><input type="text" name="bb" id="bb" class="form-control" onchange=javascript:calls2(this.form);></td>
									<td><input type="text" name="dd" id="dd" class="form-control" onchange=javascript:calls4(this.form);></td>
									<td><input type="text" name="ee" id="ee" class="form-control" onchange=javascript:calls5(this.form);></td>
									<td><input type="text" name="jj" id="jj" class="form-control" onchange=javascript:calls10(this.form);></td>
									<td><input type="text" name="ff" id="ff" class="form-control" onchange=javascript:calls6(this.form);></td>
									<td><input type="text" name="gg" id="gg" class="form-control" onchange=javascript:calls7(this.form);></td>
									<td><input type="text" name="hh" id="hh" class="form-control" onchange=javascript:calls8(this.form);></td>
									<td><input type="text" name="ii" id="ii" class="form-control" onchange=javascript:calls9(this.form);></td>
								</tr>
								<tr>
									<td><input type="text" id="aaa" name="aaa" readonly class="form-control"></td> 
									<td><input type="text" id="ccc" name="ccc" readonly class="form-control"></td>
									<td><input type="text" id="bbb" name="bbb" readonly class="form-control"></td>
									<td><input type="text" id="ddd" name="ddd" readonly class="form-control"></td>
									<td><input type="text" id="eee" name="eee" readonly class="form-control"></td>
									<td><input type="text" id="jjj" name="jjj" readonly class="form-control"></td>
									<td><input type="text" id="fff" name="fff" readonly class="form-control"></td>
									<td><input type="text" id="ggg" name="ggg" readonly class="form-control"></td>
									<td><input type="text" id="hhh" name="hhh" readonly class="form-control"></td>
									<td><input type="text" id="iii" name="iii" readonly class="form-control"></td>
								</tr>
								<tr>
									<td colspan="2">
										<b class="text-success">CLOSING BALANCE</b>
										<div class="input-group">
											<span class="input-group-addon"><span style="color:#990000" class="fa fa-money"></span></span>
											<input readonly type="text" name="balance" id="balance" class="form-control">
										</div>
									</td>
									<td colspan="2">
										<b class="text-success">DENOMINATION TOTAL</b>
										<div class="input-group">
											<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
											<input type="text" data-readonly class="form-control" name="total" id="total" required>
										</div>
									</td>
									<td colspan="2">
										<b class="text-success">DIFFERENCE IN DENOMINATION</b>
										<div class="input-group">
											<span class="input-group-addon"><span style="color:#990000" class="fa fa-money"></span></span>
											<input type="text" readonly class="form-control" name="diff" id="diff">
										</div>
									</td>
									<td colspan="2">
										<b style="color:#f00" class="text-success">* DAILY CLOSING AMOUNT
											<div class="input-group">
												<span class="input-group-addon"><span style="color:#990000" class="fa fa-lock"></span></span>
												<select class="form-control" required name="ho" id="ho" style="padding: 5px;color: #555;font-size: 11px;">
													<option selected disabled value="">SELECT ACTION</option>
													<option value="Forward to HO">Forward to HO</option>
													<option value="Carry Forward">Carry Forward</option>
													<option value="Pending Cases">Pending Cases</option>
												</select>
											</div>
										</b>
									</td>
									<td colspan="2" style="text-align:center"><br>
										<button class="btn btn-success" name="submitClosing" id="submitClosing" type="submit" ><span style="color:#ffcf40" class="fa fa-save"></span> Submit Daily Closing</button>
									</td>
								</tr>
							</tbody>
						</table>
					</form>
				</div>
			</div>
			<?php }else{  ?>
			<div class="col-lg-2"></div>
			<div class="col-lg-8 panel-box">
			<h1 align="center">DAILY BUSINESS CLOSED</h1><br>
			<h4 align="center">CONTACT HO TO RE-OPEN THE BUSINESS</h4><br>
			<?php
				$closedRow = mysqli_fetch_array($closedRowSQL);
				echo "<p align='center'><a target='_blank' class='btn btn-success' href='dailyPdf1.php?branch=".$closedRow['branchId']."&date=".$closedRow['date']."&a=".$closedRow['one']."&b=".$closedRow['two']."&c=".$closedRow['three']."&d=". $closedRow['four']."&e=".$closedRow['five']."&f=".$closedRow['six']."&g=".$closedRow['seven'] ."&h=".$closedRow['eight']."&i=".$closedRow['nine']."&j=".$closedRow['ten']."'><i style='color:#ffcf40' class='fa fa-print'></i> PRINT DAILY CLOSING REPORT </a></p></div><div class='col-lg-2'></div>";
			}  ?>

		</div>
	</div>
	<div style="clear:both"></div>
	<?php include("footer.php");?>
	<script>
	
        function validate()
        {
        var chks = document.getElementsByName('multiple[]');
        var hasChecked = false;
        for (var i = 0; i < chks.length; i++)
        {
        if (chks[i].checked)
        {
        hasChecked = true;
        break;
        }
        }
        if (hasChecked == false)
        {
        alert("Please select at least one.");
        return false;
        }
        return true;
        }
        
        
        function cal0(form) {
        var stock = form.cash.value;
        var Total = +stock;
        form.transfers.value = Total;
        }
        
        
        function call(form) {
        var stock = form.totalamount.value;
        form.balance.value = stock;
        }
        
        
        function calls1(form) {
        var stock = form.aa.value;
        var stock1 = form.balance.value;
        var total= stock * 2000;
        form.aaa.value = total;
        var diff= form.diff.value;
        var zero= 0 ;
        if( diff == zero)
        {
        form.diff.value= +stock1 - +total ;
        
        }
        else if(diff>=total)
        {
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        }
        else if(total>diff){
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        
        }
        var tot= form.total.value;
        if(tot==zero)
        {
        form.total.value= total ;	
        }
        else if (tot>zero)
        {
        var t= +tot + +total;
        form.total.value=t;
        }
        }
        
        
        function calls2(form) {
        var stock = form.bb.value;
        var stock1 = form.balance.value;
        var total= stock * 200;
        form.bbb.value = total;
        var diff= form.diff.value;
        var zero= 0 ;
        if( diff == zero)
        {
        form.diff.value= +stock1 - +total ;
        
        }
        else if(diff>=total)
        {
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        }
        else if(total>diff){
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        
        }
        var tot= form.total.value;
        if(tot==zero)
        {
        form.total.value= total ;	
        }
        else if (tot>zero)
        {
        var t= +tot + +total;
        form.total.value=t;
        }
        }
        
        function calls3(form) {
        var stock = form.cc.value;
        var stock1 = form.balance.value;
        var total= stock * 500;
        form.ccc.value = total;
        var diff= form.diff.value;
        var zero= 0 ;
        if( diff == zero)
        {
        form.diff.value= +stock1 - +total ;
        
        }
        else if(diff>=total)
        {
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        }
        else if(total>diff){
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        
        }
        var tot= form.total.value;
        if(tot==zero)
        {
        form.total.value= total ;	
        }
        else if (tot>zero)
        {
        var t= +tot + +total;
        form.total.value=t;
        }
        }
        
        
        function calls4(form) {
        var stock = form.dd.value;
        var stock1 = form.balance.value;
        var total= stock * 100;
        form.ddd.value = total;
        var diff= form.diff.value;
        var zero= 0 ;
        if( diff == zero)
        {
        form.diff.value= +stock1 - +total ;
        
        }
        else if(diff>total)
        {
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        }
        else if(total>=diff)
        {
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        
        }
        var tot= form.total.value;
        if(tot==zero)
        {
        form.total.value= total ;	
        }
        else if (tot>zero)
        {
        var t= +tot + +total;
        form.total.value=t;
        }
        }
        
        
        function calls5(form) {
        var stock = form.ee.value;
        var stock1 = form.balance.value;
        var total= stock * 50;
        form.eee.value = total;
        var diff= form.diff.value;
        var zero= 0 ;
        if( diff == zero)
        {
        form.diff.value= +stock1 - +total ;
        
        }
        else if(diff>total)
        {
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        }
        else if(total>=diff){
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        
        }
        var tot= form.total.value;
        if(tot==zero)
        {
        form.total.value= total ;	
        }
        else if (tot>zero)
        {
        var t= +tot + +total;
        form.total.value=t;
        }
        }
        
        
        function calls6(form) {
        var stock = form.ff.value;
        var stock1 = form.balance.value;
        var total= stock * 10;
        form.fff.value = total;
        var diff= form.diff.value;
        var zero= 0 ;
        if( diff == zero)
        {
        form.diff.value= +stock1 - +total ;
        
        }
        else if(diff>=total)
        {
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        }
        else if(total>diff){
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        
        }
        var tot= form.total.value;
        if(tot==zero)
        {
        form.total.value= total ;	
        }
        else if (tot>zero)
        {
        var t= +tot + +total;
        form.total.value=t;
        }
        }
        
        
        function calls7(form) {
        var stock = form.gg.value;
        var stock1 = form.balance.value;
        var total= stock * 5;
        var diff= form.diff.value;
        form.ggg.value = total;
        var zero= 0 ;
        if( diff == zero)
        {
        form.diff.value= +stock1 - +total ;
        
        }
        else if(diff>total)
        {
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        }
        else if(total>=diff){
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        
        }
        var tot= form.total.value;
        if(tot==zero)
        {
        form.total.value= total ;	
        }
        else if (tot>zero)
        {
        var t= +tot + +total;
        form.total.value=t;
        }
        }
        
        
        
        function calls8(form) {
        var stock = form.hh.value;
        var stock1 = form.balance.value;
        var total= stock * 2;
        var diff= form.diff.value;
        form.hhh.value = total;
        
        var zero= 0 ;
        if( diff == zero)
        {
        form.diff.value= +stock1 - +total ;
        
        }
        else if(diff>=total)
        {
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        }
        else if(total>diff){
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        
        }
        var tot= form.total.value;
        if(tot==zero)
        {
        form.total.value= total ;	
        }
        else if (tot>zero)
        {
        var t= +tot + +total;
        form.total.value=t;
        }
        }
        
        
        function calls9(form) {
        var stock = form.ii.value;
        var stock1 = form.balance.value;
        var total= stock * 1;
        form.iii.value = total;
        var diff= form.diff.value;
        var zero = 0;
        if( diff == zero)
        {
        form.diff.value= +diff - +total ;
        
        }
        else if(diff>=total)
        {
        var diffe= +diff - +total;
        form.diff.value=diffe ;
        }
        else if(total>diff){
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        }
        var tot= form.total.value;
        if(tot==zero)
        {
        form.total.value= total ;	
        }
        else if (tot>zero)
        {
        var t= +tot + +total;
        form.total.value=t;
        }
        }
        
        
        function calls10(form) {
        var stock = form.jj.value;
        var stock1 = form.balance.value;
        var total= stock * 20;
        form.jjj.value = total;
        var diff= form.diff.value;
        var zero = 0;
        if( diff == zero)
        {
        form.diff.value= +stock1 - +total ;
        
        }
        else if(diff>=total)
        {
        var diffe= +diff - +total;
        form.diff.value=diffe ;
        }
        else if(total>diff){
        var diffe= +diff - +total;
        form.diff.value= diffe ;
        }
        var tot= form.total.value;
        if(tot==zero)
        {
        form.total.value= total ;	
        }
        else if (tot>zero)
        {
        var t= +tot + +total;
        form.total.value=t;
        }
        }

		$(document).ready(function(){
			if($("#session_branchID").val()){
				let branchId = $("#session_branchID").val();
				var req = $.ajax({
					url:"xbalance.php",
					type:"POST",
					data:{branchId:branchId},
					dataType:'JSON'
				});
				req.done(function(e){
					$("#open").val(e.open);
					$("#totalamount").val(e.totalFund);
					$("#fundR").val(e.fundRec);
					$("#fundTranfer").val(e.fundTransfer);
					$("#todaysExpense").val(e.expense);
					$("#balance").val(e.balance);
				});
			}
			
		});
	</script>

