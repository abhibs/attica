<?php
	session_start();
	$type=$_SESSION['usertype'];
	if($type=='HR'){
		include("header.php");
		include("menuhr.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	
	function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array(), $data) {	
		$first_of_month = gmmktime(0, 0, 0, $month, 1, $year);
		// remember that mktime will automatically correct if invalid dates are entered
		// for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
		// this provides a built in "rounding" feature to generate_calendar()
		
		$day_names = array(); //generate all the day names according to the current locale
		for ($n = 0, $t = (3 + $first_day) * 86400; $n < 7; $n++, $t+=86400) //January 4, 1970 was a Sunday
		$day_names[$n] = ucfirst(gmstrftime('%A', $t)); //%A means full textual day name
		
		@list($month, $year, $month_name, $weekday) = explode(',', gmstrftime('%m, %Y, %B, %w', $first_of_month));
		$weekday = ($weekday + 7 - $first_day) % 7; //adjust for $first_day
		$title   = htmlentities(ucfirst($month_name)) . $year;  //note that some locales don't capitalize month and day names
		
		//Begin calendar .  Uses a real <caption> .  See http://diveintomark.org/archives/2002/07/03
		@list($p, $pl) = key($pn); 
		@list($n, $nl) = key($pn); //previous and next links, if applicable
		if($p) $p = '<span class="calendar-prev">' . ($pl ? '<a href="' . htmlspecialchars($pl) . '">' . $p . '</a>' : $p) . '</span>&nbsp;';
		if($n) $n = '&nbsp;<span class="calendar-next">' . ($nl ? '<a href="' . htmlspecialchars($nl) . '">' . $n . '</a>' : $n) . '</span>';
		
		$calendar = "<table class='emp-calendar-table table table-bordered'>" . "\n" .
		'<caption class="calendar-month">' . $p . ($month_href ? '<a href="' . htmlspecialchars($month_href) . '">' . $title . '</a>' : $title) . $n . "</caption>\n<tr style='background-color: #eaeaea;'>";
		
		if($day_name_length){   
			//if the day names should be shown ($day_name_length > 0)
			//if day_name_length is >3, the full name of the day will be printed
			foreach($day_names as $d)
			$calendar  .= '<th class="text-success" abbr="' . htmlentities($d) . '">' . htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d) . '</th>';
			$calendar  .= "</tr>\n<tr>";
		}
		
		if($weekday > 0){
			for ($i = 0; $i < $weekday; $i++){
				$calendar  .= '<td>&nbsp;</td>'; //initial 'empty' days
			}
		}
		
		for($day = 1, $days_in_month = gmdate('t',$first_of_month); $day <= $days_in_month; $day++, $weekday++){
			if($weekday == 7){
				$weekday   = 0; //start a new week
				$calendar  .= "</tr>\n<tr>";
			}
			
			if(isset($days[$day]) and is_array($days[$day])){
				@list($link, $classes, $content) = $days[$day];
				if(is_null($content))  $content  = $day;
				$calendar  .= '<td' . ($classes ? ' class="' . htmlspecialchars($classes) . '">' : '>') .
				($link ? '<a href="' . htmlspecialchars($link) . '">' . $content . '</a>' : $content) . '</td>';
			}
			else{
				$filtered = array_filter($data, function($v) use($day){
					return (int)($v['day']) == $day;
				});
				$maped = implode('', array_map("getData", $filtered));
				
				$calendar .= "<td>$day".$maped."</td>";
			}
		}
		if($weekday != 7) $calendar  .= '<td id="emptydays" colspan="' . (7-$weekday) . '">&nbsp;</td>'; //remaining "empty" days
		
		return $calendar . "</tr>\n</table>";
	}
	
	function getData($v){
		$content = "<div class='col-sm-12 emp-tags'>";
		$content .= "<div class='col-sm-9'>".$v['branch']." <br> <small>" .$v['time']. "</small></div>";
		$content .= "<div class='col-sm-3 text-success'><img class='small-image' src='AttendanceImage/".$v['photo']."'></div>";
		$content .= "</div>";
		return $content;
	}
	
	
	$time = time();
	$result = [];
	
	$year = date('Y', $time);
	$month = date('n', $time);
	$days = array();
	
	if(isset($_GET['empReport'])){
		$employeeId = $_GET['empId'];
		$getdate = $_GET['empMonth'];
		
		$splitDate = explode("-",$getdate,2);
		$year = $splitDate[0];
		$month = $splitDate[1];
		
		$sql = mysqli_query($con, "SELECT branch, DATE_FORMAT(date, '%d') as day, time, photo, status
		FROM attendance
		WHERE DATE_FORMAT(date,'%Y-%m')='$getdate' AND empId='$employeeId'");
		$result = mysqli_fetch_all($sql, MYSQLI_ASSOC);
		
		$empDetails = mysqli_fetch_assoc( mysqli_query($con, "SELECT name, contact, designation 
		FROM employee 
		WHERE empId='$employeeId'"));
	}
	
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 18px;
	color:#123C69;
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
	.fa_Icon {
	color:#ffd700;
	}
	.fa_icon{
	color:#990000;
	}
	.hpanel .panel-body {
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 20px;
	background-color: #f5f5f5;
	}
	thead, tfoot {
	background: transparent;
	}
	
	.emp-calendar-table td{
	width: 14%;
	height: 150px;
	color: #6A6C6F;
	font-weight: 600;
	}
	.calendar-month{
	font-size: 15px;
	font-weight: 600;
	text-align: center;
	color: #990000;
	margin-bottom: 5px;
	}
	.small-image{
	width: 20px;
	height: 20px;
	margin-top: 4px;
	border-radius: 3px;
	}
	.small-image:hover{
	transform : scale(5); 
	transition : 0.5s ease;
	}
	.emp-tags{
	color: #ffffff;
	background-color: #34495e;
	margin: 3px;
	border-radius: 3px;
	font-size: 10px;
	padding: 2px;
	}
	.emp-tags:hover{
	transform : scale(2); 
	transition : 0.5s ease;
	z-index: 1;
	}
	.emp-details{
	background-color: #eaeaea;
	margin-right: 10px;
	border-radius: 3px;
	border: 1px solid #ccc;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<div class="col-lg-5">
						<h3 style="margin-top:30px"><i class="fa_icon fa fa-address-book-o"></i> Monthly Employee Report</h3>
					</div>
					<form action="" method="GET">
						<div class="col-lg-3">
							<label class="text-success">Employee ID</label>
							<input type="text" class="form-control" name="empId" placeholder="Employee ID" required autocomplete="off">
						</div>
						<div class="col-lg-3">
							<label class="text-success">Month</label> 
							<input type="month" class="form-control" name="empMonth" required>
						</div>
						<div class="col-lg-1">
							<button style="margin-top:23px" type="submit" class="btn btn-success btn-block" name="empReport"><span class="fa_Icon fa fa-search"></span> Search</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-body" style="margin-top: 20px;">
					<?php if(isset($_GET['empReport'])){ ?>
						<div class="col-lg-2 emp-details">
							<p class="text-left">Emp ID<br/><b><?php echo $employeeId; ?></b></p>
						</div>
						<div class="col-lg-3 emp-details">
							<p class="text-left">Name<br/><b><?php echo $empDetails['name']; ?></b></p>
						</div>
						<div class="col-lg-2 emp-details">
							<p class="text-left">Contact<br/><b><?php echo $empDetails['contact']; ?></b></p>
						</div>
						<div class="col-lg-2 emp-details">
							<p class="text-left">Designation<br/><b><?php echo $empDetails['designation']; ?></b></p>
						</div>
					<?php } ?>
				</div>
				<div class="panel-body">
					<?php
						echo generate_calendar($year, $month, $days, 3, null, 0, array(), $result);
					?>
				</div>
			</div>
		</div>
	</div>
<?php include("footer.php"); ?>