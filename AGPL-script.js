

$(function () {

	// Initialize summernote plugin
	$('.summernote').summernote({
		toolbar: [
			['headline', ['style']],
			['style', ['bold', 'italic', 'underline', 'superscript', 'subscript', 'strikethrough', 'clear']],
			['textsize', ['fontsize']],
			['alignment', ['ul', 'ol', 'paragraph', 'lineheight']],
		]
	});

});


$(document).ready(function () {
	$(".nav li.disabled a").click(function () {
		return false;
	});
});


$(document).ready(function () {
	$("#brana").change(function () {
		var data = $('#brana').val();
		var req = $.ajax({
			url: "setAddress.php",
			type: "POST",
			data: { data },
		});
		req.done(function (msg) {
			$("#add").val(msg);
		});
	});
});


$(document).ready(function () {
	$("#one").hide();
	$("#two").hide();
	$("#three").hide();
	$("#four").hide();
	$("#type2").change(function () {
		var text = $('select#type2').val();
		if (text == "By Cash") {
			$("#one").hide();
			$("#four").hide();
			$("#three").hide();
			$("#two").hide();
		}
		else if (text == "By Cheque") {
			$("#one").hide();
			$("#four").hide();
			$("#three").show();
			$("#two").hide();
		}
		else if (text == "By Bank Transfer") {
			$("#one").show();
			$("#four").hide();
			$("#two").show();
			$("#three").hide();
		}
		else if (text == "To NBFC") {
			$("#one").show();
			$("#four").show();
			$("#three").hide();
			$("#two").hide();
		}
		else if (text == "repledge" || text == "recovery" || text == "others") {
			$("#one").hide();
			$("#three").hide();
			$("#four").show();
			$("#two").hide();
		}
	});
});


$(function () {

	// Initialize Example 1
	$('#example1').dataTable({
		"ajax": '',
		dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
		"lengthMenu": [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, "All"]],
		buttons: [
			{ extend: 'copy', className: 'btn-sm' },
			{ extend: 'csv', title: 'ExportReport', className: 'btn-sm' },
			{ extend: 'pdf', title: 'ExportReport', className: 'btn-sm' },
			{ extend: 'print', className: 'btn-sm' }
		]
	});

	// Initialize Example 2
	$('#example2').dataTable({
		"ajax": '',
		dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
		"lengthMenu": [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, "All"]],
		buttons: [
			{ extend: 'copy', className: 'btn-sm' },
			{ extend: 'csv', title: 'ExportReport', className: 'btn-sm' },
			{ extend: 'pdf', title: 'ExportReport', className: 'btn-sm' },
			{ extend: 'print', className: 'btn-sm' }
		]
	});

	$('#example3').dataTable({
		"ajax": '',
		dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
		"lengthMenu": [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, "All"]],
		buttons: [
			{ extend: 'copy', className: 'btn-sm' },
			{ extend: 'csv', title: 'ExportReport', className: 'btn-sm' },
			{ extend: 'pdf', title: 'ExportReport', className: 'btn-sm' },
			{ extend: 'print', className: 'btn-sm' }
		]
	});



	$('#example4').dataTable({
		"ajax": '',
		dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
		"lengthMenu": [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, "All"]],
		buttons: [
			{ extend: 'copy', className: 'btn-sm' },
			{ extend: 'csv', title: 'ExportReport', className: 'btn-sm' },
			{ extend: 'pdf', title: 'ExportReport', className: 'btn-sm' },
			{ extend: 'print', className: 'btn-sm' }
		]
	});
	$('#example5').dataTable();
	$('#example6').dataTable();
	$('#example7').dataTable();
	$('#example8').dataTable();
	$('#example9').dataTable();
});