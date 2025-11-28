<footer class="footer">
	<b><span style="color:#990000" class="pull-right">
	ISO 9001:2015 Certified Company</span>
	<span style="color:#990000" class="pull-left">
	Attica Gold Pvt Ltd</span></b>
</footer>
</div>
<!-- Vendor scripts -->
<script src="../vendor/jquery/dist/jquery.min.js"></script>
<script src="../vendor/jquery-ui/jquery-ui.min.js"></script>
<script src="../vendor/slimScroll/jquery.slimscroll.min.js"></script>
<script src="../vendor/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="../vendor/metisMenu/dist/metisMenu.min.js"></script>
<script src="../vendor/iCheck/icheck.min.js"></script>
<script src="../vendor/sparkline/index.js"></script>
<script src="../vendor/datatables/media/js/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script src="../vendor/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="../vendor/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="../vendor/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="../vendor/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
<script src="../scripts/homer.js"></script>
<script>
	$(document).ready(function(){
		$(".nav li.disabled a").click(function(){
			return false;
		});
	});
</script>
<script>	
$(function ()
{		
	$('#example1').dataTable( {
		"ajax": '',
		dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
		"lengthMenu": [ [10, 25, 50,100,250, -1], [10, 25, 50,100,250, "All"] ],
		buttons: [
		{extend: 'copy',className: 'btn-sm'},
		{extend: 'csv',title: 'ExportReport', className: 'btn-sm'},
		{extend: 'pdf', title: 'ExportReport', className: 'btn-sm'},
		{extend: 'print',className: 'btn-sm'}
		]
	});
	
	$('#example2').dataTable( {
		"ajax": '',
		dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
		"lengthMenu": [ [10, 25, 50,100,250, -1], [10, 25, 50,100,250, "All"] ],
		buttons: [
		{extend: 'copy',className: 'btn-sm'},
		{extend: 'csv',title: 'ExportReport', className: 'btn-sm'},
		{extend: 'pdf', title: 'ExportReport', className: 'btn-sm'},
		{extend: 'print',className: 'btn-sm'}
		]
	});
	
	$('#example3').dataTable( {
		"ajax": '',
		dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
		"lengthMenu": [ [10, 25, 50,100,250, -1], [10, 25, 50,100,250, "All"] ],
		buttons: [
		{extend: 'copy',className: 'btn-sm'},
		{extend: 'csv',title: 'ExportReport', className: 'btn-sm'},
		{extend: 'pdf', title: 'ExportReport', className: 'btn-sm'},
		{extend: 'print',className: 'btn-sm'}
		]
	});
	
	$('#example4').dataTable( {
		"ajax": '',
		dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
		"lengthMenu": [ [10, 25, 50,100,250, -1], [10, 25, 50,100,250, "All"] ],
		buttons: [
		{extend: 'copy',className: 'btn-sm'},
		{extend: 'csv',title: 'ExportReport', className: 'btn-sm'},
		{extend: 'pdf', title: 'ExportReport', className: 'btn-sm'},
		{extend: 'print',className: 'btn-sm'}
		]
	});
	$('#example5').dataTable();
	$('#example6').dataTable();
	$('#example7').dataTable();
	$('#example8').dataTable();
	$('#example9').dataTable();
	$('#example10').dataTable();
	$('#example11').dataTable();
});	
</script>
</body>
</html>