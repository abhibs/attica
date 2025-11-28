    var options = {
		shutter_ogg_url: "jpeg_camera/shutter.ogg",
		shutter_mp3_url: "jpeg_camera/shutter.mp3",
		swf_url: "jpeg_camera/jpeg_camera.swf",
	};
	
    var camera = new JpegCamera("#camera", options);
	
	$('#snapshots').click(function()
	{
		var snapshot = camera.capture();
		snapshot.show();
		snapshot.upload({api_url: "ac.php"}).done(function(response)
		{
			$('#image').prepend("<tr><td><img src='"+response+"' width='80px' height='74px'></td></tr>");
			this.discard();
			//alert("Marked Attendance");
		});
	});
	
	
	function changeTex(submitId)
	{
        var submit = document.getElementById(submitId);
        submit.value = 'Marked Attendance';
        return false;
	};

    var options = {
		shutter_ogg_url: "jpeg_camera/shutter.ogg",
		shutter_mp3_url: "jpeg_camera/shutter.mp3",
		swf_url: "jpeg_camera/jpeg_camera.swf",
	};
	
    var camera = new JpegCamera("#camera", options);
	
	$('#take').click(function()
	{
		var snapshot = camera.capture();
		snapshot.show();
		snapshot.upload({api_url: "act.php"}).done(function(response)
		{
			$('#imagelist').prepend("<tr><td><img src='"+response+"' width='80px' height='74px'></td></tr>");
			this.discard();
			//alert("Marked Attendance");
		});
	});
	
	
	function changeTex(submitId)
	{
        var submit = document.getElementById(submitId);
        submit.value = 'Recapture';
        return false;
	};
	
	
	var options = {
		shutter_ogg_url: "jpeg_camera/shutter.ogg",
		shutter_mp3_url: "jpeg_camera/shutter.mp3",
		swf_url: "jpeg_camera/jpeg_camera.swf",
	};
	
    var camera = new JpegCamera("#camera", options);
	var clickCount = 0;
	$('#take_snapshots').click(function()
	{
		clickCount++;
		if (clickCount == 1)
		{
			var snapshot = camera.capture();
			snapshot.show();
			snapshot.upload({api_url: "action.php"}).done(function(response)
			{
				$('#imagelist').prepend("<tr><td><img src='"+response+"' width='80px' height='74px'></td></tr>");
				this.discard();
				
			}).fail(function(response)
			{
				alert("Upload failed with status " + response);
			});
			
		}
		else if (clickCount >= 2)
		{
			var element = $("#imagelist").remove();
			window.location.href="action1.php";
			
		}
		
	});
	
	function done()
	{
		$('#snapshots').html("uploaded");
	}
	function changeText(submitId)
	{
        var submit = document.getElementById(submitId);
        submit.value = 'Recapture Image';
        return false;
	};
	
	if(!window.JpegCamera) {
		alert('Camera access is not available in your browser');
	} 
	else{
		var options = {
			shutter_ogg_url: "jpeg_camera/shutter.ogg",
			shutter_mp3_url: "jpeg_camera/shutter.mp3",
			swf_url: "jpeg_camera/jpeg_camera.swf",
		};
		var cameras = new JpegCamera("#camera", options);
		var clickCount = 0;
		$('#take_snapshot').click(function(){
			clickCount++;
			if (clickCount == 1) {
				var snapshot1 = cameras.capture();
				snapshot1.show();
				snapshot1.upload({api_url: "action3.php"}).done(function(response) {
					$('#imagelist1').prepend("<tr><td><img src='"+response+"' width='80px' height='74px'></td></tr>");
					this.discard();
					
					}).fail(function(response) {
					alert("Upload failed with status " + response);
				});
			}
			else if (clickCount >= 2) {
				var element = $("#imagelist1").remove();
				window.location.href="action2.php";
				
			}
		})
	}
	function done(){
		$('#snapshot1').html("uploaded");
	}
	function changeText(submitId){
		var submit = document.getElementById(submitId);
		submit.value = 'Recapture ';
		return false;
	};

	var options = {
		shutter_ogg_url: "jpeg_camera/shutter.ogg",
		shutter_mp3_url: "jpeg_camera/shutter.mp3",
		swf_url: "jpeg_camera/jpeg_camera.swf",
	};
	var camera = new JpegCamera("#camera", options);
	var clickCount = 0;
	$('#take_snap').click(function(){
		clickCount++;
		var data=$('#dat3').val();
		alert(data);
		if (clickCount == 1) {
			var snapshot = camera.capture();
			snapshot.show();
			snapshot.upload({api_url: "act.php"}).done(function(response) {
				$('#imagelist').prepend("<tr><td><img src='"+response+"' width='80px' height='74px'></td></tr>");
				this.discard();
				
				}).fail(function(response) {
				alert("Upload failed with status " + response);
			});
		}
		else if (clickCount >= 2) {
			var element = $("#imagelist").remove();
			window.location.href="action1.php";
		}
	})
	
