<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
include("dbConnection.php");

$type = $_SESSION['usertype'];

if ($type == 'Master') {
    include("header.php");
    include("menumaster.php");
} else {
    include("logout.php");
}


$date = date('Y-m-d');


if (isset($_POST['insertDataBtn'])) {
    $billNumber = mysqli_real_escape_string($con, $_POST['billId']);
    $invoiceNumber = $_SESSION['invoiceNumber'];
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $pincode = $_POST['pin'];
    $grossW = $_POST['grossW'];
    $stoneW = $_POST['netW'];
    $amount = $_POST['payableA'];
    $rate = $_POST['intrest'];
    $rateAmount = $_POST['intrestrate'];
    $branchId = $_POST['branchId'];
    $empId = $_POST['empId'];
    $empName = $_POST['empName'];
    $status = $_POST['status'];
    $date = date('Y-m-d');
    $time = date('H:i:s');
    $customerImage='';
    $ornamentImage='';
    $kyc1='';
    $kyc2='';

    echo "billNumber: $billNumber, name: $name, contact: $contact, address: $address, pin: $pin,branchId:$branchId,empId=$empId";

    $sqlQuery = mysqli_query($con, "INSERT INTO pledge_bill (billId, invoiceId, name, contact, address, city, pincode,kyc1,kyc2,customerImage,ornamentImage ,grossW, stoneW, amount, rate, rateAmount, branchId, empId, empName, status, date, time) VALUES ('$billNumber', '$invoiceNumber', '$name', '$contact', '$address', '$city', '$pincode', '$kyc1','$kyc2','$customerImage','$ornamentImage','$grossW', '$stoneW', '$amount', '$rate', '$rateAmount', '$branchId', '$empId', '$empName', 'Billed', '$date', '$time')");

    if ($sqlQuery) {
        echo "<script>alert('Data Added successfully')</script>";
        echo "<script>setTimeout(\"location.href = 'pledgeMaster.php';\", 150);</script>";
    } else {
        echo "<script>alert('Error!! Data Cannot Be Added: " . mysqli_error($con) . "')</script>";
        header("Location: pledgenewUser.php");
    }
}


$joinQuery = "SELECT e.empId, e.name FROM employee AS e INNER JOIN users AS u ON e.empId = u.employeeId";



?>

<style>
	.ornament-detail-box{
		margin-top: 20px;
		margin-bottom: 20px;
		box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
	}
	.boxShadow{
		margin-top: 20px;
		box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
	}
	tr{
		border: 1px solid transparent;
		z-index:7;
	}
	td{
		transition: all 0.2s ease-in-out;
	}
	tr:hover {
		box-shadow: 0px 9px 4px -6px grey;
		
	}
	tr:hover td{
		cursor:pointer;
		border-top: 1px solid grey;
		border-bottom: 1px solid grey;
	}

	#wrapper .btn-success{
		background-color:#123C69;
		border-color:#123C69;
		margin-left: 0px;
		font-size: 12px;
	}
	input[type="text"]:focus,select:focus { 
		border-color: #13637e #13637e #13637e #13637e;
		outline: none;
		-moz-box-shadow: 0 0 10px #13637e;
		-webkit-box-shadow: 0 0 10px #13637e;
		box-shadow: 0 0 10px #13637e;
	}
	.ornament-details .input-group,#IMPSdetails .input-group,#ornament-total .input-group,#add-ornament .input-group{
		box-shadow: 0 0 2px #13637e;
		border-radius: 5px;
	}
	#wrapper{
	/*	background: #f5f5f5;*/
		background-image: linear-gradient(to right, #F5F7FA, #B8C6DB);
		
	}
	
	#wrapper h3{
		color:#123C69;
		text-transform:uppercase;
		font-weight:600;
		font-size: 20px;
	}
	#wrapper h4{
		color:#123C69;
		text-transform:uppercase;
		font-weight:600;
		font-size: 16px;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
		background-color:#fffafa;
	}
	.text-success{
		color:#123C69;
		text-transform:uppercase;
		font-weight:600;
		font-size: 12px;
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

	fieldset {
		margin-top: 1.8rem;
		margin-bottom: 1.5rem;
		border: none;
		/*box-shadow: 0px 0px 2px rgba(0,0,0,1);*/
		box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
		background-color: #fffafa;
		border-radius:3px;
	}

	legend, button {
		margin-left:8px;
		width:300px;
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

	.trans_Icon{
		color:#990000;
	}
	.fa_Icon{
		color:#990000;
	}
	.transaction-body{
		background: #fffafa;
		padding: 20px;
		position: relative;
		border-radius:10px;
		padding:20px;
		border:none;
		
	}
	#available{
		text-transform:uppercase;
		text-align: center;
		font-weight: 600;
		font-size:12px;
		color: #fffafa;
		background-color: #123C69;
	}
	
	.accordion {
		font-size: 1rem;
		width: 100%;
		margin: 0 auto;
		border-radius: 5px;
		margin-top:15px;
	}

	.accordion-header,
	.accordion-body {
		background: #ffffff;
	}

	.accordion-header {
		padding: 0px 10px 10px 20px;
		background: #123C69;
		color: white;
		cursor: pointer;
		font-size: 14px;
		letter-spacing: 5px;
		transition: all .3s;
		text-transform: uppercase;
	}
	
	.accordion__item .accordion__item {
	  border-bottom: 1px solid #123C69;
	}

	.accordion-header:hover {
	  background: #123C69;
	  position: relative;
	  z-index: 5;
	}

	.accordion-body {
	  background: #fcfcfc;
	  color: #353535;
	  display: none;
	  box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
	}

	.accordion-body__contents {
	  /* padding: 1.5em 1.5em; */
	  font-size: 14px;
	}

	.accordion__item.active:last-child .accordion-header {
		border-radius: none;
	}

	.accordion:first-child > .accordion__item > .accordion-header {
		border-bottom: 1px solid transparent;
	}

	.accordion__item > .accordion-header:after {
		content: "^";
		font-family: IonIcons;
		font-size: 1.5em;
		float: right;
		position: relative;
		top: 10px;
		transition: .3s all;
		transform: rotate(0deg);
	}

	.accordion__item.active > .accordion-header:after {
		transform: rotate(-180deg);
		margin-right: 5px;
		margin-top: -10px;
	}

	.accordion__item.active .accordion-header {
		background: #123C69;
	}

	.accordion__item .accordion__item .accordion-header {
		background: #f1f1f1;
		color: #353535;
	}

	@media screen and (max-width: 1000px) {
		body {
			padding: 1em;
		}
		.accordion {
			width: 100%;
		}
	}
	
	label {
		display: inline-block;
		max-width: 100%;
		margin-bottom: 5px;
		font-weight: 600;
	}
	
	input[type="checkbox"] {
		position: relative;
		width: 1.7em;
		height: 1.7em;
		color: #008000;
		border: 1px solid grey;
		border-radius: 4px;
		appearance: none;
		outline: 0;
		cursor: pointer;
		transition: background 175ms cubic-bezier(0.1, 0.1, 0.25, 1);
	}
	
	input[type="checkbox"]:before {
		position: absolute;
		content: '';
		display: block;
		top: 0px;
		left: 7px;
		width: 8px;
		height: 14px;
		border-style: solid;
		border-color: white;
		border-width: 0 2px 2px 0;
		transform: rotate(45deg);
		opacity: 0;
	}
  
	input[type="checkbox"]:checked {
		color: white;
		border-color: #008000;
		background: #008000;
	}
	
    input[type="checkbox"]::before {
		opacity: 1;
    }
	.btn-danger{
		background: #EE2935;
	}
	
	.btn-transaction{
		display: inline-block;
		padding: 0.7em 0.2em;
		margin: 0 0.3em 0.3em 0;
		border-radius: 0.15em;
		box-sizing: border-box;
		text-decoration: none;
		font-family: 'Roboto',sans-serif;
		text-transform: uppercase;
		font-weight: 400;
		font-size:12px;
		color: #fffafa;
		background-color: #123C69;
		box-shadow: inset 0 -0.6em 0 -0.35em rgb(0 0 0 / 17%);
		text-align: center;
		position: relative;
	}
	
	.modal-title {
		font-size: 25px;
		font-weight: 300;
		text-align: center;
	}
	.row {
		padding:0px 16px;
	}

    .styled-table {
        width: 100%;
        
        border-spacing: 0;
    }

    .styled-table th,
    .styled-table td {
       
        padding: 8px;
        text-align: left;
    }

    .styled-table th {
        background-color:  #123C69;
        color: white;
    }

    .styled-table td .totalResult{
       background-color: #f9f9f9;
    }

    
    .panel-body {
        margin-top: 20px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }
	 
	  .styled-table .totalResult {
               background-color:  #123C69;
        color: white;
    }
	
</style>

<datalist id="branchList"> 
    <?php 
        $branches = mysqli_query($con,"SELECT branchId,branchName FROM branch where status=1");
        while($branchList = mysqli_fetch_array($branches)){
		?>
		<option value="<?php echo $branchList['branchId']; ?>" label="<?php echo $branchList['branchName']; ?>"></option>
	<?php } ?>
</datalist>

<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">z
            <div class="hpanel">
                <div class="col-xs-6"><br>
                    <h3> <span class="fa_Icon fa fa-edit"></span> &nbsp;NEW PLEDGE FORM </h3>
                </div>
                <div style="clear:both"></div>
                <div class="panel-body"
                    style="border-radius:15px;box-shadow: rgb(60 64 67 / 30%) 0px 1px 2px 0px, rgb(60 64 67 / 15%) 0px 2px 6px 2px;background-color:#f5f5f5;margin-left:2%;margin-right:2%;">
                    <h4 class="text-success"> &nbsp; <i class="fa_Icon fa fa-user-plus"></i><b> Add Customer details
                        </b></h4>
                    <br>
                    <form autocomplete="off" id="bill-form1" method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" value="<?php echo $_GET['id']; ?>" class="form-control" name="id">

                    <div class="form-group row">
                    <?php
                        $result = mysqli_query($con, "SELECT id FROM pledge_bill ORDER BY id DESC LIMIT 1");

                        if ($result && $row = mysqli_fetch_assoc($result)) {
                            $lastId = $row['id'];
                        } else {
                            $lastId = 0; // Default value if no records are found
                        }
                
                        // Replace with your actual value
                        $billno = 11000;
                    
                        $billNumber = $billno . ($lastId+1);
                                  
                    ?>
                        <input type="hidden" name="billId" id="billId" placeholder="ENTER BILL ID" class="form-control" value="<?php echo $billNumber; ?>" readonly
                            style="padding:0px 5px;color:#ff0000;"  >
                            <div class="col-xs-3">
                        <label class="text-success">Pledge Number</label>
                        <div class="input-group">
                            <span class="input-group-addon"><span class="fa_Icon fa fa-snowflake-o" style="color:#990000"></span></span>
                            <?php
                                // Fetch the maximum numeric part from the existing invoice numbers
                                $result = mysqli_query($con, "SELECT MAX(CAST(SUBSTRING(invoiceid, 3) AS SIGNED)) AS maxNumericPart FROM pledge_bill");

                                if (!$result) {
                                    die('Error: ' . mysqli_error($con));
                                }

                                $row = mysqli_fetch_assoc($result);
                                $maxNumericPart = $row['maxNumericPart'];

                                // Increment the maximum numeric part for the new invoiceId
                                $newNumericPart = $maxNumericPart + 1;

                                // Construct the new invoiceId
                                $invoiceNumber = 'HO' . $newNumericPart;

                                // Update the lastInvoiceId in the session
                                $_SESSION['invoiceNumber'] = $invoiceNumber;
                            ?>
                            <input type="text" name="invoiceId" id="invoiceId" class="form-control"
                                style="padding:0px 5px; color:#ff0000;"
                                value="<?php echo $_SESSION['invoiceNumber']; ?>"
                                readonly>
                        </div>
                    </div>
                    <div class="col-xs-3">
                                <label class="text-success">Customer Name</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
                                    <input type="text" name="name" id="name" placeholder="ENTER NAME" class="form-control"
                                        style="padding:0px 5px" >
                                </div>
                            </div>
                           
                            <div class="col-xs-3">
                                <label class="text-success">Customer Contact</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><span style="color:#990000" class="fa fa-phone"></span></span>
                                    <input type="text" name="contact" id="contact" placeholder="ENTER CONTACT" class="form-control"
                                        style="padding:0px 5px" >
                                </div>
                            </div>
                           
                            <div class="col-xs-3">
                                <label class="text-success">Address</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><span style="color:#990000" class="fa fa-drivers-license"></span></span>
                                    <textarea  rows="2" cols="40"name="address" id="address" placeholder="ENTER ADDRESS"class="form-control"
                                        style="padding:0px 5px" ></textarea>
                                </div>
                            </div>
                            <label class="col-sm-12"><br></label>
                            <div class="col-sm-3">
                                <label class="text-success">City</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><span style="color:#990000" class=" fa fa-home"></span></span>
                                    <input type="text" name="city" id="city" class="form-control"
                                        style="padding:0px 5px" autocomplete="off" placeholder="City">
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <label class="text-success">Pincode</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><span style="color:#990000" class=" fa fa-map"></span></span>
                                    <input type="text" name="pin" id="pin"  class="form-control"
                                        autocomplete="off" placeholder="Pincode">
                                </div>
                            </div>
                                              
                        <div class="col-md-3">
                            <label class="text-success">Select Branch</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span style="color:#990000" class="fa fa-institution"></span></span>
                                <input list="branchList" name="branchId" id="branchId" placeholder="SELECT BRANCH"  class="form-control">
                            </div>
                        </div>
                        
                     
                            <label class="col-sm-12"><br></label> 
                            <div class="col-lg-12">
                                <form method="POST" autocomplete="off" id="bill-form2" action="" enctype="multipart/form-data">
                                <input type="hidden" value="<?php echo $_GET['id']; ?>" class="form-control" name="id">
                                <input type="hidden" value="<?php echo $rowres['branch']; ?>" class="form-control" name="branchid">	
                                    <fieldset class="ornament-total">
                                        <h4 class="text-success"> &nbsp; <i class="trans_Icon fa fa-diamond" style="padding-top:1px"></i>
                                            <b> ORNAMENT TOTAL WEIGHT</b>
                                        </h4>
                                        <div style="background-color:#fffafa;border-radius:5px;border:none;" class="panel-body">
                                            <div class="col-sm-2">
                                                <label class="text-success">Gross Weight</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><span class="fa_Icon fa fa-balance-scale"></span></span>
                                                    <input type="text" name="grossW" readonly id="totalGrossW" class="form-control"
                                                        placeholder="Gross Weight" value="">
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <label class="text-success">Net Weight</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><span class="fa_Icon fa fa-balance-scale"></span></span>
                                                    <input type="text" name="netW" readonly id="totalNetW" class="form-control"
                                                        placeholder="Net Weight" value="">
                                                </div><br>
                                            </div>
                                            <div class="col-sm-2">
                                                <label class="text-success">Amount Payable</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><span class="fa_Icon fa fa-rupee" ></span></span>
                                                    <input type="text" name="payableA" id="payableA" class="form-control">
                                                </div><br>
                                            </div>
                                            <div class="col-sm-2">
                                                <label class="text-success">Intrest</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><span class="fa_Icon fa fa-percent" ></span></span>
                                                    <input type="text" name="intrest" id="intrest" value="1.5" readonly class="form-control">
                                                </div><br>
                                            </div>
                                            <div class="col-sm-2">
                                                <label class="text-success">Intrest Amount </label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><span class="fa_Icon fa fa-rupee" ></span></span>
                                                    <input type="text" name="intrestrate" id="intrestrate" class="form-control" readonly>
                                                </div>
                                            </div>
                                </form>
                            
                            </div>
                            </fieldset>
                        </div>
                        <div class="col-sm-2" style="left:500px;">
                            <br>
                            <button type="insertDataBtn" class="btn btn-success btn-block" name="insertDataBtn" id="insertDataBtn"
                                style="margin-left:1%;margin-top:2%;background:#008000; ">
                                <span style="color:white;font-size:15px;" class="fa fa-plus"> Submit</span>
                            </button>
                            <br>
                        </div>

                    </form>
                </div>
                <div style="clear:both"></div>
                <form autocomplete="off" id="ornament-form" method="POST" action="" enctype="multipart/form-data">
                    <div class="accordion js-accordion col-sm-12">
                        <div style="clear:both"></div>
                        <div class="accordion__item js-accordion-item active ornament-details">
                            <div class="accordion-header js-accordion-header "><i
                                    style="padding-top:10px;color:#ffcf40;" class="trans_Icon fa fa-diamond">
                                </i> ADD ORNAMENT DETAILS</div>
                            <div class="accordion-body js-accordion-body">
                                <div class="accordion-body__contents">
                                    <div class="transaction-body">
                                        <span class="ornament-detail-error"></span>
                                        <div class="col-sm-3">
                                            <label class="text-success">Ornament Type</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><span class="fa_Icon fa fa-snowflake-o"></span> </span>
                                                <select id="type" style="padding:0px 3px" name="type"class="form-control" >
                                                    <option selected="true" disabled="disabled" value="">SELECTORNAMENT</option>
                                                    <option Value="22 carot Biscuit (91.6">22 carot Biscuit (91.6)</option>
                                                    <option Value="24 carot Biscuit(99.9)">24 carot Biscuit (99.9)</option>
                                                    <option Value="22 carot Coin (91.6">22 carot Coin (91.6)</option>
                                                    <option Value="24 carot Coin (99.9)">24 carot Coin (99.9)</option>
                                                    <option Value="Anklets">Anklets</option>
                                                    <option Value="Armlets">Armlets</option>
                                                    <option Value="Baby Bangles">Baby Bangles</option>
                                                    <option Value="Bangles">Bangles</option>
                                                    <option Value="Bracelet">Bracelet</option>
                                                    <option Value="Broad Bangles">Broad Bangles</option>
                                                    <option Value="Chain">Chain</option>
                                                    <option Value="Chain with Locket">Chain with Locket</option>
                                                    <option Value="Chain with Black Beads">Chain with Black Beads </option>
                                                    <option Value="Drops">Drops</option>
                                                    <option Value="Ear Rings">Ear Rings</option>
                                                    <option Value="Gold Bar">Gold Bar</option>
                                                    <option Value="Locket">Locket</option>
                                                    <option Value="Head Locket">Head Locket</option>
                                                    <option Value="Matti">Matti</option>
                                                    <option Value="Necklace">Necklace</option>
                                                    <option Value="Ring">Ring</option>
                                                    <option Value="Small Gold Piece">Small Gold Piece</option>
                                                    <option Value="Studs">Studs</option>
                                                    <option Value="Studs And Drops">Studs And Drops</option>
                                                    <option Value="Thala/Mangalya Chain">Thali Chain</option>
                                                    <option Value="Thala/Mangalya Chain with Black Beads">Thali Chainwith Black Beads</option>
                                                    <option Value="Waist Belt/Chain">Waist Belt/Chain</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <label class="text-success">No of Pieces</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><span
                                                        class="fa_Icon fa fa-balance-scale"></span> </span>
                                                <input type="text" name="count" id="count" placeholder="No of pieces"
                                                    class="form-control" >
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <label class="text-success">Gross Weight</label>
                                            <div class="input-group ornament-weight">
                                                <span class="input-group-addon"><span
                                                        class="fa_Icon fa fa-balance-scale"></span></span>
                                                <input type="text" name="grossW" id="grossW" placeholder="Gross Weight"
                                                    class="form-control"  oninput="calculateNetWeight()">
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <label class="text-success">Stone Weight</label>
                                            <div class="input-group ornament-sweight">
                                                <span class="input-group-addon"><span
                                                        class="fa_Icon fa fa-balance-scale"></span></span>
                                                <input type="text" name="stoneW"  id="stoneW"
                                                    placeholder="Stone weight" class="form-control" 
                                                    oninput="calculateNetWeight()">
                                            </div> <br>
                                        </div>
                                        <div class="col-sm-3">
                                            <label class="text-success">Net Weight</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><span
                                                        class="fa_Icon fa fa-balance-scale"></span></span>
                                                <input type="text" placeholder="Net Weight" readonly
                                                    name="reading" id="reading" class="form-control"
                                                    oninput="calculateNetWeight()">
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <label class="text-success">Purity</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><span
                                                        class="fa_Icon fa fa-percent"></span></span>
                                                <input type="text" name="pur" id="pur"  class="form-control"
                                                    autocomplete="off" placeholder="Purity">
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <label class="text-success">Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><span
                                                        class="fa_Icon fa fa-rupee"></span></span>
                                                <input type="text" name="amount" id="amount" 
                                                    class="form-control" autocomplete="off" placeholder="Amount">
                                                 
                                                    
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <br>
                                            <button type="submit" class="btn btn-success btn-block btn-transaction"
                                                name="submit" id="submit" style="margin-top:2%;padding-top:5px;">
                                                <span style="color:#ffcf40" class="fa fa-plus"></span> ADD
                                                ORNAMENT
                                            </button>
                                        </div>
                                        <div style="clear:both"></div><br>
                                    </div>
                                </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <fieldset class="ornamentDetailsTable">
            <h4 class="text-success"> &nbsp; <i class="trans_Icon fa fa-diamond"></i><b> ORNAMENT DETAILS LIST</b></h4>
            <div style="background-color:#fffafa;border-radius:15px;border:none;" class="panel-body">
                <div class="table-responsive" style="margin-top: -30px;">
                    <table cellpadding="1" cellspacing="1" class="table styled-table">
                        <thead>
                            <tr class="text-success">
                                <th class="text-center"><i style="color:white;"
                                        class="fa_Icon fa fa-sort-numeric-asc"></i></th>
                                <th>ORNAMENT TYPE</th>
                                <th>GROSS WEIGHT</th>
                                <th>STONE</th>
                                <th>NET WEIGHT</th>
                                <th>PURITY</th>
                                <th>AMOUNT</th>
                            
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </fieldset>
    </div>

</div>
</div>
</div>
</div> <br><br>
<div style="clear:both"></div>
<?php include("footer-transaction.php"); ?>

<script>
    function calculateNetWeight() {
        var grossW = parseFloat(document.getElementById('grossW').value) || 0;
        var stoneW = parseFloat(document.getElementById('stoneW').value) || 0;
        var netWeight = grossW - stoneW;
        document.getElementById('reading').value = netWeight.toFixed(2);
    }
    function calculateInterestRate() {
        var payable = parseFloat(document.getElementById('payableA').value) || 0;
        var interest = parseFloat(document.getElementById('intrest').value) || 0;
        var intrestrate = (payable * interest) / 100;
        document.getElementById('intrestrate').value = intrestrate.toFixed(2);
    }
    document.getElementById('payableA').addEventListener('input', calculateInterestRate);

    $('#submit').on('click', function (event) {
        event.preventDefault();

        var ornamentType = $('#type').val();
        var count = $('#count').val();
        var grossW = parseFloat($('#grossW').val()) || 0;
        var stoneW = parseFloat($('#stoneW').val()) || 0;
        var reading = parseFloat($('#reading').val()) || 0;
        var pur = parseFloat($('#pur').val()) || 0;
        var amount = parseFloat($('#amount').val()) || 0;
        if (ornamentType === '' || count === '' || grossW === '' || stoneW === '' || pur === '' || amount === '') {
            alert('Please fill all required fields');
            return;
        }

        var amountMin, amountMax;
        if (pur >= 18 && pur <= 90) {
            amountMin = 1;
            amountMax = 3500;
        } else if (pur === 91.6) {
            amountMin = 1;
            amountMax = 4100;
        } else if (pur >= 92 && pur <= 99.9) {
            amountMin = 1;
            amountMax = 5000;
        } else {
            alert('Please fill the amount within the range');
            return;
        }
        if (amount < amountMin || amount > amountMax) {
            alert('Please fill the amount within the range of ' + amountMin + ' to ' + amountMax);
            return;
        }

        $.ajax({
            type: 'POST',
            url: 'xaddPledgeData.php',
            data: {
                ornamentType: ornamentType,
                count: count,
                grossW: grossW,
                stoneW: stoneW,
                reading: reading,
                pur: pur,
                amount: amount
            },
            success: function (response) {
                var rowCount = $('.styled-table tbody tr').length;
                var serialNumber = rowCount + 1;
                var newRow = '<tr>' +
                    '<td class="text-center">' + serialNumber + '</td>' +
                    '<td>' + ornamentType + ' (' + count + ')</td>' +
                    '<td>' + grossW + '</td>' +
                    '<td>' + stoneW + '</td>' +
                    '<td>' + (grossW - stoneW) + '</td>' +
                    '<td>' + pur + '</td>' +
                    '<td>' + amount + '</td>' +
                    //'<td><b><a class="text-danger" title="Delete Record"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a></b></td>' +
                    '</tr>';
                $('.styled-table tbody').append(newRow);

                calculateGrandTotal();

                $('#type').val('');
                $('#count').val('');
                $('#grossW').val('');
                $('#stoneW').val('');
                $('#reading').val('');
                $('#pur').val('');
                $('#amount').val('');
            },
            error: function (error) {
                console.error('Error during insertion:', error);
            }
        });
    });
    function calculateGrandTotal() {
        var totalGrossW = 0;
        var totalStoneW = 0;
        var totalNetW = 0;
        var totalAmount = 0;

        $('.styled-table tbody tr').each(function () {
            var rowGrossW = parseFloat($(this).find('td').eq(2).text()) || 0;
            var rowStoneW = parseFloat($(this).find('td').eq(3).text()) || 0;
            var rowAmount = parseFloat($(this).find('td').eq(6).text()) || 0;

            totalGrossW += rowGrossW;
            totalStoneW += rowStoneW;
            totalNetW += rowGrossW - rowStoneW;
            totalAmount += rowAmount;
        });
        $('#totalGrossW').val(totalGrossW.toFixed(2));
        $('#totalNetW').val(totalNetW.toFixed(2));
        $('#totalAmount').val(totalAmount.toFixed(2));

        $('.grand-total-row').remove();
        var grandTotalRow = '<tr class="grand-total-row" style="background-color: #123C69;color: white;">' +
            '<td colspan="2"><strong>GRAND TOTAL</strong></td>' +
            '<td><strong>' + totalGrossW.toFixed(2) + '</strong></td>' +
            '<td><strong>' + totalStoneW.toFixed(2) + '</strong></td>' +
            '<td><strong>' + totalNetW.toFixed(2) + '</strong></td>' +
            '<td><strong>' + '</strong></td>' +
            '<td><strong>' + totalAmount.toFixed(2) + '</strong></td>' +
            '</tr>';

        $('.styled-table tbody').after(grandTotalRow);
    }

    function updateSerialNumbers() {
        $('.styled-table tbody tr').each(function (index) {
            $(this).find('td:first').text(index + 1);
        });
    }

    $(document).ready(function () {
        $('#insertDataBtn').click(function () {

            var isForm1Valid = validateForm('#bill-form1');
            if (!isForm1Valid) {
                event.preventDefault();
                alert('Please fill all fields in Form');
                return;
            }

            var isForm2Valid = validateForm('#bill-form2');
            if (!isForm2Valid) {
                event.preventDefault();
                alert('Please fill all fields in Form');
                return;
            }
            var formData = new FormData();
            var formData1 = $('#bill-form1').serializeArray();
            var formData2 = $('#bill-form2').serializeArray();
            var combinedData = $.merge(formData1, formData2);

            $.each(combinedData, function () {
                formData.append(this.name, this.value);
            });
            var file1 = $('#kyc1')[0].files[0];
            var file2 = $('#kyc2')[0].files[0];

            formData.append('kyc1', file1);
            formData.append('kyc2', file2);

            $.ajax({
                url: 'insertPledge.php',
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,

          success: function (response) {
                try {
                    var responseObject = JSON.parse(response);

                    if (responseObject.error) {
                        alert(responseObject.message);
						 window.location.href = 'pledgeStatus.php';
                    } else {
                       
                        window.location.href = 'pledgeStatus.php';
                    }
                } catch (error) {
                    console.error('Error parsing JSON response:', error);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error inserting data:', error);
            }
			
        });
    });
});

function validateForm(formId) {
    var isValid = true;
    $(formId + ' input[required]').each(function () {
        if ($(this).val().trim() === '') {
            isValid = false;
            return false;
        }
    });
    return isValid;
}
$(document).ready(function() {
    $('#empId').on('change', function() {
        var selectedEmpId = $(this).val();

        $.ajax({
            url: 'getEmpName.php', // Update with the correct path
            type: 'POST',
            data: { empId: selectedEmpId },
            success: function(response) {
                $('#empName').val(response);
            },
            error: function() {
                console.log('Error fetching employee name');
            }
        });
    });
});
</script>



