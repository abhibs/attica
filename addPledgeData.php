<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];
if ($type == 'Branch') {
include("header.php");
include("menu.php");
} else {
include("logout.php");
}
include("dbConnection.php");
$branchId = $_SESSION['branchCode'];
$employeeId = $_SESSION['employeeId'];

$date = date('Y-m-d');

if (isset($_GET['id']) && $_GET['id'] != '') {
$rowres = mysqli_fetch_assoc(mysqli_query($con, "SELECT branch, customer, contact 
    FROM everycustomer 
    WHERE id='$_GET[id]'"));
}


if (!isset($_SESSION['invoiceNumber'])) {
$randomNumber = str_pad(mt_rand(1, 999), 4, '0', STR_PAD_LEFT);
$invoiceNumber = $branchId . "/" . $randomNumber . "/" . date("Y-m-d");
$_SESSION['invoiceNumber'] = $invoiceNumber;
}

?>

<style>
.ornament-detail-box {
    margin-top: 20px;
    margin-bottom: 20px;
    box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
}

.boxShadow {
    margin-top: 20px;
    box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
}

tr {
    border: 1px solid transparent;
    z-index: 7;
}

td {
    transition: all 0.2s ease-in-out;
}

tr:hover {
    box-shadow: 0px 9px 4px -6px grey;

}

tr:hover td {
    cursor: pointer;
    border-top: 1px solid grey;
    border-bottom: 1px solid grey;
}

#wrapper .btn-success {
    background-color: #123C69;
    border-color: #123C69;
    margin-left: 0px;
    font-size: 12px;
}

input[type="text"]:focus,
select:focus {
    border-color: #13637e #13637e #13637e #13637e;
    outline: none;
    -moz-box-shadow: 0 0 10px #13637e;
    -webkit-box-shadow: 0 0 10px #13637e;
    box-shadow: 0 0 10px #13637e;
}

.ornament-details .input-group,
#IMPSdetails .input-group,
#ornament-total .input-group,
#add-ornament .input-group {
    box-shadow: 0 0 2px #13637e;
    border-radius: 5px;
}

#wrapper {
    /*	background: #f5f5f5;*/
    background-image: linear-gradient(to right, #F5F7FA, #B8C6DB);

}

#wrapper h3 {
    color: #123C69;
    text-transform: uppercase;
    font-weight: 600;
    font-size: 20px;
}

#wrapper h4 {
    color: #123C69;
    text-transform: uppercase;
    font-weight: 600;
    font-size: 16px;
}

.form-control[disabled],
.form-control[readonly],
fieldset[disabled] .form-control {
    background-color: #fffafa;
}

.text-success {
    color: #123C69;
    text-transform: uppercase;
    font-weight: 600;
    font-size: 12px;
}

.btn-primary {
    background-color: #123C69;
}

.btn-info {
    background-color: #123C69;
    border-color: #123C69;
    font-size: 12px;
}

.btn-info:hover,
.btn-info:focus,
.btn-info:active,
.btn-info.active {
    background-color: #123C69;
    border-color: #123C69;
}

fieldset {
    margin-top: 1.8rem;
    margin-bottom: 1.5rem;
    border: none;
    /*box-shadow: 0px 0px 2px rgba(0,0,0,1);*/
    box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
    background-color: #fffafa;
    border-radius: 3px;
}

legend,
button {
    margin-left: 8px;
    width: 300px;
    background-color: #123C69;
    padding: 5px 15px;
    line-height: 30px;
    font-size: 14px;
    color: white;
    text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);
    transform: translateX(-1.1rem);
    box-shadow: -1px 1px 1px rgba(0, 0, 0, 0.8);
    margin-bottom: 0px;
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
    width: 0;
    background-color: transparent;
    border-top: 0.0rem solid transparent;
    border-right: 0.35rem solid black;
    border-bottom: 0.45rem solid transparent;
    border-left: 0.0rem solid transparent;
    position: absolute;
    left: -0.075rem;
    bottom: -0.45rem;
}

.trans_Icon {
    color: #990000;
}

.fa_Icon {
    color: #990000;
}

.transaction-body {
    background: #fffafa;
    padding: 20px;
    position: relative;
    border-radius: 10px;
    padding: 20px;
    border: none;

}

#available {
    text-transform: uppercase;
    text-align: center;
    font-weight: 600;
    font-size: 12px;
    color: #fffafa;
    background-color: #123C69;
}

.accordion {
    font-size: 1rem;
    width: 100%;
    margin: 0 auto;
    border-radius: 5px;
    margin-top: 15px;
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

.accordion:first-child>.accordion__item>.accordion-header {
    border-bottom: 1px solid transparent;
}

.accordion__item>.accordion-header:after {
    content: "^";
    font-family: IonIcons;
    font-size: 1.5em;
    float: right;
    position: relative;
    top: 10px;
    transition: .3s all;
    transform: rotate(0deg);
}

.accordion__item.active>.accordion-header:after {
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

.btn-danger {
    background: #EE2935;
}

.btn-transaction {
    display: inline-block;
    padding: 0.7em 0.2em;
    margin: 0 0.3em 0.3em 0;
    border-radius: 0.15em;
    box-sizing: border-box;
    text-decoration: none;
    font-family: 'Roboto', sans-serif;
    text-transform: uppercase;
    font-weight: 400;
    font-size: 12px;
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
    padding: 0px 16px;
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
    background-color: #123C69;
    color: white;
}

.styled-table td .totalResult {
    background-color: #f9f9f9;
}


.panel-body {
    margin-top: 20px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
}

.styled-table .totalResult {
    background-color: #123C69;
    color: white;
}
</style>
<div id="wrapper">
<div class="row content">
    <div class="col-lg-12">
        <div class="hpanel">
            <div class="col-xs-6"><br>
                <h3> <span class="fa_Icon fa fa-edit"></span> &nbsp;NEW PLEDGE FORM </h3>
            </div>
             <div style="clear:both"></div>
            <div class="panel-body"
                style="border-radius:15px;box-shadow: rgb(60 64 67 / 30%) 0px 1px 2px 0px, rgb(60 64 67 / 15%) 0px 2px 6px 2px;background-color:#f5f5f5;margin-left:2%;margin-right:2%;">
                
					  <br> 
					  
					    <form autocomplete="off" id="bill-form1" method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" value="<?php echo $_GET['id']; ?>" class="form-control" name="id">
                    <input type="hidden" value="<?php echo $rowres['branch']; ?>" class="form-control"
                        name="branchid">

                    <input type="hidden" name="invoiceId" id="invoiceId" class="form-control"
                        style="padding:0px 5px"
                        value="<?php echo isset($_SESSION['invoiceNumber']) ? $_SESSION['invoiceNumber'] : ''; ?>"
                        readonly>
						
					  	<div class="col-lg-6">
			<div class="hpanel">
				<div class="panel-heading hbuilt" style="background-color:#123C69;">
					<div class="panel-tools">
						<a class="showhide"><i class="fa fa-chevron-up"></i></a>
						
					</div>
					<h6 style="font-size:14px;color:#f3f4f7;"><i
                                style="color:#ffcf40;" class="trans_Icon fa fa-user">
                            </i>&nbsp; ADD PLEDGE CUSTOMER DATA</h6>
				</div> 
				<div class="panel-body" style="background-color:#fffafa;margin-top:0%;">
				 <div class="form-group row"> <br>                  
                        <div class="form-group col-xs-6">
                            <label class="text-success">Customer Name</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="fa_Icon fa fa-user"></span></span>
                                <input type="text" name="name" id="name" class="form-control"
                                    value="<?php echo $rowres['customer']; ?>">
                            </div>
                        </div>
                        <div class="form-group col-xs-6">
                            <label class="text-success">Customer Contact</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="fa_Icon fa fa-phone"></span></span>
                                <input type="text" name="contact" id="contact" style="padding:0px 5px" readonly
                                    class="form-control" value="<?php echo $rowres['contact']; ?>">
                            </div>
                        </div>
						</div>
						<br>
						 <div class="form-group row">
                        <div class="form-group col-xs-6">
                            <label class="text-success">Address</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="fa_Icon fa fa-map"></span></span>
                                <input type="text" name="adres" id="adres" class="form-control"
                                    style="padding:0px 5px" autocomplete="off" placeholder="Address">
                            </div>
                        </div>
                       <div class="form-group col-xs-6">
                            <label class="text-success">City</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="fa_Icon fa fa-home"></span></span>
                                <input type="text" name="city" id="city" class="form-control"
                                    style="padding:0px 5px" autocomplete="off" placeholder="City">
                            </div>
                        </div>
					</div>
					<br>
						 <div class="form-group row">
					 <div class="form-group col-xs-6">
                            <label class="text-success">Pincode</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="fa_Icon fa fa-map"></span></span>
                                <input type="text" name="pin" id="pin" required class="form-control"
                                    autocomplete="off" placeholder="Pincode">
                            </div>
                        </div>
                      </div>
                    <br>				  
				</div>
			</div>
		</div>
		<div class="col-lg-6">
			<div class="hpanel">
				<div class="panel-heading hbuilt"  style="background-color:#123C69;">
					<div class="panel-tools">
						<a class="showhide"><i class="fa fa-chevron-up"></i></a>
						
					</div>
					<h6 style="font-size:14px;color:#f3f4f7;"><i
                                style="color:#ffcf40;" class="trans_Icon fa fa-file-text-o">
                            </i> &nbsp; ADD CUSTOMER DOCUMENTS</h6>
				</div>
				<div class="panel-body" style="background-color:#fffafa;margin-top:0%;">
					 <div class="form-group row"><br>
       
        <div class="form-group col-xs-6">
            <label class="text-success">Capture Customer Photo</label>
            <div id="results" style="position:absolute;"></div>
            <a onClick="take_snapshot()">
                <div id="my_camera"></div>
                <i style="position:absolute; top:40%; left:20%; font-size:15px; font-weight:900; color:#900">CLICK HERE</i>
            </a>
            <input type="text" name="image" class="image-tag" required style="opacity:0; width:0; float:left;"><br>
        </div>
       
        <div class="form-group col-xs-6">
            <label class="text-success">Capture Ornaments Photo</label>
            <div id="results1" style="position:absolute;"></div>
            <a onClick="take_snapshot1()">
                <div id="my_camera1"></div>
                <i style="position:absolute; top:40%; left:20%; font-size:15px; font-weight:900; color:#900">CLICK HERE</i>
            </a>
            <input type="text" name="image1" class="image-tag1" required style="opacity:0; width:0; float:left;"><br>
        </div>
    </div>		
 <div class="form-group row">
               <div class="form-group col-xs-6">
                            <label class="text-success">KYC 1</label>
                            <div class="input-group">
                                <span style="background:#ffcf40" class="input-group-addon"><span
                                        style="color:#990000" class="fa fa-file-text-o"></span></span>
                                <input type="file" style="background:#ffcf40" class="form-control"
                                    style="padding:5px 10px" name="kyc1" id="kyc1" required>
                            </div>
                        </div>
                        <div class="form-group col-xs-6">
                            <label class="text-success">KYC 2 (optional)</label>
                            <div class="input-group">
                                <span style="background:#ffcf40" class="input-group-addon"><span
                                        style="color:#990000" class="fa fa-file-text-o"></span></span>
                                <input type="file" style="background:#ffcf40" class="form-control"
                                    style="padding:5px 10px" name="kyc2" id="kyc2">
                            </div>
                        </div>	
					</div>
				</div>
			</div>
		</div>	  
	</form>		  
 <div style="clear:both"></div>
            <form autocomplete="off" id="ornament-form" method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" value="<?php echo $_GET['id']; ?>" class="form-control" name="id" id="id">
                <input type="hidden" name="invoiceNumber" id="invoiceNumber" class="form-control"
                value="<?php echo isset($_SESSION['invoiceNumber']) ? $_SESSION['invoiceNumber'] : ''; ?>">

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
                                            <span class="input-group-addon"><span
                                                    class="fa_Icon fa fa-snowflake-o"></span> </span>
                                            <select id="type" style="padding:0px 3px" name="type"
                                                class="form-control" required>
                                                <option selected="true" disabled="disabled" value="">SELECT
                                                    ORNAMENT</option>
                                                <option Value="22 carot Biscuit (91.6">22 carot Biscuit (91.6)
                                                </option>
                                                <option Value="24 carot Biscuit(99.9)">24 carot Biscuit (99.9)
                                                </option>
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
                                                <option Value="Chain with Black Beads">Chain with Black Beads
                                                </option>
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
                                                <option Value="Thala/Mangalya Chain with Black Beads">Thali Chain
                                                    with Black Beads</option>
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
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <label class="text-success">Gross Weight</label>
                                        <div class="input-group ornament-weight">
                                            <span class="input-group-addon"><span
                                                    class="fa_Icon fa fa-balance-scale"></span></span>
                                            <input type="text" name="grossW" id="grossW" placeholder="Gross Weight"
                                                class="form-control" required oninput="calculateNetWeight()">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <label class="text-success">Stone Weight</label>
                                        <div class="input-group ornament-sweight">
                                            <span class="input-group-addon"><span
                                                    class="fa_Icon fa fa-balance-scale"></span></span>
                                            <input type="text" name="stoneW" required id="stoneW"
                                                placeholder="Stone weight" class="form-control" required
                                                oninput="calculateNetWeight()">
                                        </div> <br>
                                    </div>
                                    <div class="col-sm-3">
                                        <label class="text-success">Net Weight</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><span
                                                    class="fa_Icon fa fa-balance-scale"></span></span>
                                            <input type="text" placeholder="Net Weight" readonly required
                                                name="reading" id="reading" class="form-control"
                                                oninput="calculateNetWeight()">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <label class="text-success">Purity</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><span
                                                    class="fa_Icon fa fa-percent"></span></span>
                                            <input type="text" name="pur" id="pur" required class="form-control"
                                                autocomplete="off" placeholder="Purity">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <label class="text-success">Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><span
                                                    class="fa_Icon fa fa-rupee"></span></span>
                                            <input type="text" name="amount" id="amount" required
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
        <h4 class="text-success"> &nbsp;&nbsp;&nbsp;&nbsp; <i class="trans_Icon fa fa-diamond"></i><b> ORNAMENT
                DETAILS LIST</b></h4>
        <div style="background-color:#fffafa;border-radius:15px;border:none;" class="panel-body">
            <div class="table-responsive" style="margin-top: -30px;">
                <table cellpadding="1" cellspacing="1" class="table styled-table">
                    <thead>
                        <tr class="text-success">
                            <th>ORNAMENT TYPE</th>
                            <th>GROSS WEIGHT</th>
                            <th>STONE</th>
                            <th>NET WEIGHT</th>
                            <th>PURITY</th>
                            <th>AMOUNT</th>
                            <th>ACTION</th>

                        </tr>
                    </thead>
                    <tbody id="ornamentTableBody">

                    </tbody>
                </table>
            </div>
        </div>
    </fieldset>
</div>
<div class="col-lg-4">
    <form method="POST" autocomplete="off" id="bill-form2" action="" enctype="multipart/form-data">

        <input type="hidden" value="<?php echo $_GET['id']; ?>" class="form-control" name="id">
        <input type="hidden" value="<?php echo $rowres['branch']; ?>" class="form-control" name="branchid">

        <fieldset class="ornament-total">
            <h4 class="text-success"> &nbsp; <i class="trans_Icon fa fa-calculator" style="padding-top:1px"></i>
                <b> ORNAMENT TOTAL</b>
            </h4>
            <div style="background-color:#fffafa;border-radius:5px;border:none;" class="panel-body">
                <div class="col-sm-6">
                    <label class="text-success">Gross Weight</label>
                    <div class="input-group">
                        <span class="input-group-addon"><span class="fa_Icon fa fa-balance-scale"></span></span>
                        <input type="text" name="totalGrossW" readonly id="totalGrossW" class="form-control"
                            placeholder="Gross Weight" value="">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="text-success">Net Weight</label>
                    <div class="input-group">
                        <span class="input-group-addon"><span class="fa_Icon fa fa-balance-scale"></span></span>
                        <input type="text" name="totalNetW" readonly id="totalNetW" class="form-control"
                            placeholder="Net Weight" value="">
                    </div><br>
                </div>

                <div class="col-sm-6">
                    <label class="text-success">Amount Payable</label>
                    <div class="input-group">
                        <span class="input-group-addon"><span class="fa_Icon fa fa-rupee" required></span></span>
                        <input type="text" name="payableA" id="payableA" class="form-control">
                    </div><br>
                </div>
                <div class="col-sm-6">
                    <label class="text-success">Intrest</label>
                    <div class="input-group">
                        <span class="input-group-addon"><span class="fa_Icon fa fa-percent" required></span></span>
                        <input type="text" name="intrest" id="intrest" required class="form-control">
                    </div><br>
                </div>
                <div class="col-sm-6">
                    <label class="text-success">Intrest Amount </label>
                    <div class="input-group">
                        <span class="input-group-addon"><span class="fa_Icon fa fa-rupee" required></span></span>
                        <input type="text" name="intrestrate" id="intrestrate" class="form-control" readonly>
                    </div>
                </div>
    </form>
    <div class="col-sm-6">
        <br>
        <button type="insertDataBtn" class="btn btn-success btn-block" name="insertDataBtn" id="insertDataBtn"
            style="margin-left:1%;margin-top:2%;">
            <span style="color:#ffcf40" class="fa fa-plus"></span> Submit
        </button>
        <br>
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




<script src="scripts/webcam.min.js"></script>

<script>
                    Webcam.set({
					width: 210,
					height: 160,
					image_format: 'jpeg',
					jpeg_quality: 100
				});
				Webcam.attach('#my_camera');
				function take_snapshot() {
					Webcam.snap(function(data_uri) {
						$(".image-tag").val(data_uri);
						document.getElementById('results').innerHTML = '<img src="' + data_uri + '"/>';
					});
				}
				
				
					 Webcam.set({
					 width: 210,
					 height: 160,
					 image_format: 'jpeg',
					 jpeg_quality: 100
				 });
				 Webcam.attach('#my_camera1');
				 function take_snapshot1() {
					 Webcam.snap(function(data_uri) {
						 $(".image-tag1").val(data_uri);
						 document.getElementById('results1').innerHTML = '<img src="' + data_uri + '"/>';
					 });
			 }



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
document.getElementById('intrest').addEventListener('input', calculateInterestRate);


$('#submit').on('click', function (event) {
     event.preventDefault();
     
    var invoiceNumber = $('#invoiceNumber').val();
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

    // var amountMin, amountMax;
    // if (pur >= 18 && pur <= 90) {
    //     amountMin = 1;
    //     amountMax = 3500;
    // } else if (pur === 91.6) {
    //     amountMin = 1;
    //     amountMax = 4100;
    // } else if (pur >= 92 && pur <= 99.9) {
    //     amountMin = 1;
    //     amountMax = 5000;
    // } else {
    //     alert('Please fill the amount within the range');
    //     return;
    // }
    // if (amount < amountMin || amount > amountMax) {
    //     alert('Please fill the amount within the range of ' + amountMin + ' to ' + amountMax);
    //     return;
    // }
    $.ajax({
        type: 'POST',
        url: 'xaddPledgeData.php',
        data: {
            invoiceNumber:invoiceNumber,
            ornamentType: ornamentType,
            count: count,
            grossW: grossW,
            stoneW: stoneW,
            reading: reading,
            pur: pur,
            amount: amount
        },
        success: function (response) {
            console.log(response);

            $('#ornamentTableBody').append(response);
            calculateGrandTotal();

            $('#type').val('');
            $('#count').val('');
            $('#grossW').val('');
            $('#stoneW').val('');
            $('#reading').val('');
            $('#pur').val('');
            $('#amount').val('');
        },
        error: function (xhr, status, error) {
            console.log(xhr.responseText);
            alert('An error occurred ');
        }
    });
});


function delete_ornament(id) {
    if (confirm("Are you sure you want to delete this record?")) {
        $.ajax({
            type: 'GET',
            url: 'xdeletePledgeData.php',
            data: { id: id },
            success: function (response) {
                var data = JSON.parse(response);
                if (data.success) {
                    $('#ornamentTableBody tr[data-id="' + id + '"]').remove();
                } else {
                    alert(data.error);
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr.responseText);
                alert('An error occurred ');
            }
        });
    }
}

function calculateGrandTotal() {
    var totalGrossW = 0;
    var totalStoneW = 0;
    var totalNetW = 0;
    var totalAmount = 0;

    $('.styled-table tbody tr').each(function () {
        var rowGrossW = parseFloat($(this).find('td').eq(1).text()) || 0;
        var rowStoneW = parseFloat($(this).find('td').eq(2).text()) || 0;
        var rowAmount = parseFloat($(this).find('td').eq(5).text()) || 0;

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
        '<td><strong>GRAND TOTAL</strong></td>' +
        '<td><strong>' + totalGrossW.toFixed(2) + '</strong></td>' +
        '<td><strong>' + totalStoneW.toFixed(2) + '</strong></td>' +
        '<td><strong>' + totalNetW.toFixed(2) + '</strong></td>' +
        '<td><strong>' + '</strong></td>' +
        '<td colspan="2"><strong>' + totalAmount.toFixed(2) + '</strong></td>' +
        '</tr>';

    $('.styled-table tbody').after(grandTotalRow);
}

$('#ornamentTableBody').on('click', '.text-danger', function (event) {
    event.preventDefault();

    $(this).closest('tr').remove();
    calculateGrandTotal();
});

$('#insertDataBtn').on('click', function (event) {
    event.preventDefault(); 

    var isForm1Valid = validateForm('#bill-form1');
    if (!isForm1Valid) {
        alert('Please fill all fields');
        return;
    }

    var isForm2Valid = validateForm('#bill-form2');
    if (!isForm2Valid) {
        alert('Please fill all fields');
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

    if (confirm("Successfully inserted.")) {
        $.ajax({
            url: 'insertPledge.php',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                var data = JSON.parse(response);
                if (data.success) {
                    window.location.href = 'pledgeStatus.php';
                } else {
                    alert(data.error);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert('AJAX request failed: ' + textStatus + ', ' + errorThrown);
            }
        });
    }
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
</script>