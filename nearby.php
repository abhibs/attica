<?php
	$con= mysqli_connect('localhost','atticagold_new','AGPL2o2o','atticagold_new');
	if(!$con)
	{
	 die('could not connect;'.mysqli_connect_error());
	}
	error_reporting(E_ERROR | E_PARSE);
	$search1='';
	$search2='';
	if(isset($_GET['aaa']))
	{
		$search1=$_REQUEST['latitudeFrom'];
		$search2=$_REQUEST['longitudeFrom'];
	}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<!-- jQuery library -->

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script type='text/javascript' src='https://code.jquery.com/jquery-1.12.4.min.js'></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyDuUy9IdIwA_Yu4AAdqUJeXtl2gBdHl0m8"></script>
<!-- //ckbs -->
<script type="text/javascript">
	var source, destination;
	google.maps.event.addDomListener(window, 'load', function ()
	{
		var searchBox = new google.maps.places.SearchBox(document.getElementById('txtSource'));
		//new google.maps.places.SearchBox(document.getElementById('txtSource'));
		//new google.maps.places.SearchBox(document.getElementById('txtDestination'));
		//directionsDisplay = new google.maps.DirectionsRenderer({ 'draggable': true });
		
		var markers = [];
		// Listen for the event fired when the user selects a prediction and retrieve
		// more details for that place.
		searchBox.addListener('places_changed', function()
		{
			//alert('Hi');
			var places = searchBox.getPlaces();		
			if (places.length == 0)
			{
				return;
			}
			
			//alert( places.join("\",\"")); 
			console.log("my object: %o", places)
			
			// For each place, get the icon, name and location.
			var bounds = new google.maps.LatLngBounds();
			places.forEach(function(place)
			{
				if (!place.geometry)
				{
					console.log("Returned place contains no geometry");
					return;
				}
				if (place.geometry)
				{ 
					var location = place.geometry.location; 
					var latitudeckb = location.lat(); 
					var longitudeckb  = location.lng();	
					$('#latitudeFrom').val( Number.parseFloat( latitudeckb).toFixed(6));	
					$('#longitudeFrom').val( Number.parseFloat( longitudeckb).toFixed(7));			
				}
			});
		});
	});
	$(function()
	{
		$('#get-coordinate').on('click', function()
		{
			if(navigator.geolocation)
			navigator.geolocation.getCurrentPosition(success, error);
		});
		function success( po)
		{
			var crd = pos.coords;	
			console.log('Your current position is:');
			console.log(`Latitude : `);
			console.log(`Longitude: `);
			console.log(`More or less  meters.`);
			$('#latitudeFrom').val( Number.parseFloat( pos.coords.latitude).toFixed(6));	
			$('#longitudeFrom').val( Number.parseFloat( pos.coords.longitude).toFixed(7));
		}
		function error(err)
		{
			console.warn(err);
			alert(err.message)
		}
	});
</script>

<script type="text/javascript">
	var ajaxoutputdata;
	jQuery(document).ready(function()
	{
		
		jQuery(".getnearestbranches").click(function(e)
		{
			user_latitudeFrom = $("#latitudeFrom").val();
			user_longitudeFrom = $("#longitudeFrom").val();
			user_radius = 1000;
		    jQuery.post("https://atticagoldcompany.com/atticagold/nearby.php",
		    {
				action: "get_branches",
				_ajax_nonce: "24042a7851",
				lat: user_latitudeFrom,
				long: user_longitudeFrom,
				radius: user_radius
			},
			function(data)
			{
				ajaxoutputdata = JSON.parse(data);
				ckbfilltable(ajaxoutputdata);
			});
		});
	});
</script>
<div id="wrapper">
            <div class="hpanel">
                <h3 align="center" class="text-success"><b><i style="color:#990000" class="fa fa-map-marker"></i> Search Nearby Branch</b></h3>
                <div class="panel-body" style="box-shadow:10px 15px 15px #999;">
					<table class="table table-striped">
						<thead>
							<tr>
								<td colspan="2" align="right"><label class="text-success">Place looking for:</label></td>
								<td>
									<div class="input-group">
										<span class="input-group-addon"><a href="javascript:;" id="get-coordinate" style="color:#990000" class="fa fa-location-arrow"></a></span>
										<input type="text" class="form-control" name="from" id="txtSource" placeholder="Type the place you want to sell Gold" size="44" autocomplete="off">
									</div>
								</td>
								<form action="" method="GET">
									<td colspan="4">
										<div hidden>
											<label class="text-success">Latitude:</label> 
											<input type="text" id="latitudeFrom" name="latitudeFrom" value="" class="form-control">
											<label class="text-success">Longitude:</label>
											<input type="text" id="longitudeFrom" name="longitudeFrom" value="" class="form-control">
										</div>
										<button class="btn btn-success" name="aaa" id="aaa"><span style="color:#ffcf40" class="fa fa-search"></span> Find Nearby Branch</button>
									</td>
								</form>
							</tr>
							<tr class="text-success">
								<th>S_NO</th>
								<th>branchName</th>
								<th>Address</th>
								<th>area</th>
								<th>city</th>
								<th>state</th>
								<th>distance</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$date=date('Y-m-d');
								$query=mysqli_query($con,"SELECT `branchId`, `addressline`,  `area`,`city`, `state`, `branchName`, `url`, 
								( 6371 * acos( 
								cos( radians(".$search1 .") ) * 
								cos( radians( `latitude` ) ) * 
								cos( radians( `longitude` ) - radians(". $search2 .") ) + 
								sin( radians(". $search1 .") ) * 
								sin( radians( `latitude` ) ) ) 
								) AS distance FROM `wp_branches_database` HAVING distance <=200 ORDER BY distance ASC LIMIT 10");
								$count=mysqli_num_rows($query);
								for($i=1;$i<=$count;$i++)
								{
									if($count>0)
									{
										$row=mysqli_fetch_array($query);
										echo "<tr><td>" . $i .  "</td>";
										echo "<td>" . $row['branchName'] . "</td>";
										echo "<td><a target='_blank' href=". $row['url'] .">" . $row['addressline'] . "</a></td>";
										echo "<td>" . $row['area'] . "</td>";
										echo "<td>" . $row['city'] . "</td>";
										echo "<td>" . $row['state'] . "</td>";
										echo "<td>" . round($row['distance']) . " km</td></tr>";
									}
								}
							?>
						</tbody>
					</table>
				<form method="post" id="latlongform">
					<input type="hidden" id="latitudeFrom" name="latitudeFrom">
					<input type="hidden" id="longitudeFrom" name="longitudeFrom">
					<input type="hidden" id="radius" name="radius">
				</form>
				</div>
			</div>
</div>	