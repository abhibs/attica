	/*
	$(document).ready(function(){
	   $(document).bind("contextmenu",function(e){
		  return false;
	   });
	});

	document.addEventListener("keydown", function(e){
	  // USE THIS TO DISABLE CONTROL AND ALL FUNCTION KEYS
	  // if (e.ctrlKey || (e.keyCode>=112 && e.keyCode<=123)) {
	  // THIS WILL ONLY DISABLE CONTROL AND F12
	  if (e.ctrlKey || e.keyCode==123) {
		e.stopPropagation();
		e.preventDefault();
	  }
	});*/

	$(document).ready(function(){
		// ON METAL TYPE CHANGE
		$("#gspd").change(function(){
			var gtype = $('#gspd').val();
			$("#metal").val(gtype);
		});
		
		$("#rel").change(function(){
			var comm = $('#rel').val();
			var data=comm;
			var req=$.ajax({
					url:"setMargin.php",
					type: "POST",
					data:{data},
				}); 
				req.done(function(msg){
					$("#payable").val(msg);
				});
			
		});	
		// ON NET AMOUNT CHANGE
		$("#net1").change(function(){
			var data = $('#net1').val();
			var req=$.ajax({
						url:"setAmount.php",
						type: "POST",
						dataType: 'JSON',
						data:{data},
						success: function(response){
							var len = response.length;
							for(var i=0; i<len; i++){
								var margin = response[i].margin;
								var payable = response[i].payable;
								var type1 = response[i].ctype;
								//var margin1 = response[i].amountP;
								$("#margin").val(Math.round(margin));
								//$("#margin").val(margin);
								$("#cType").val(type1.toFixed(2));
								$("#payable").val(Math.round(payable));
							}
						}
					});
		});	

		// ON CHANGE OF ORNAMENT WEIGHT
		$("#weight").change(function(){
			var weight = $('#weight').val();
			var sweight = $('#sweight').val();
			if (weight.indexOf(",") !== -1) {
				$(".ornament-weight").css("border", "1px solid #990000");
				$(".weight-error").html("Comma is not allowed");
			}else{
				$(".ornament-weight").css("border", "");
				$(".weight-error").html("");
			}

			var Totals = +weight - +sweight;
			$("#reading").val(Totals.toFixed(3));		
		});		
		// ON CHANGE OF STONE WEIGHT
		$("#sweight").change(function(){
			var weight = $('#weight').val();
			var sweight = $('#sweight').val();

			if (sweight.indexOf(",") !== -1) {
				$(".ornament-sweight").css("border", "1px solid #990000");
				$(".sweight-error").html("Comma is not allowed");
			}else{
				$(".ornament-sweight").css("border", "");
				$(".sweight-error").html("");
			}
			var Totals = +weight - +sweight;
			$("#reading").val(Totals.toFixed(3));
		});
		
		
		// ON PURITY PERCENTAGE CHANGE
		$("#purity").change(function(){
			var e = $("#nine").val();
			var p = $("#purity").val();
			console.log(e);
			$(".purity-range").css("border", "");
			$(".purity-error,.pValue-error").html("");
			
			if(e == "" || e== null){
				$("#purity").val("");
				$(".purity-range").css("border", "1px solid #990000");
				$(".purity-error").html("Please select purity range");
			
			}
			if(e == "24Karat"){
				if( p > 99.9 || p < 92){
					$("#purity").val("");
					$(".pValue-error").html("Value must be within selected purity range");
				}else{
					$(".pValue-error").html("");
				}
			}
			else if(e == "916"){
				if(p > 91.6 || p < 88){
					$("#purity").val("");
					$(".pValue-error").html("Value must be within selected purity range");
				}else{
					$(".pValue-error").html("");
				}
			}
			else if(e == "22KNON916"){
				if(  p > 87 || p < 80){
					$("#purity").val("");
					$(".pValue-error").html("Value must be within selected purity range");
				}else{
					$(".pValue-error").html("");
				}
			}
			else if(e == "22CT"){
				if( p > 79 || p < 35){
					$("#purity").val("");
					$(".pValue-error").html("Value must be within selected purity range");
				}else{
					$(".pValue-error").html("");
				}
			}
			else if(e == "OT"){
				if( p > 79 || p < 35){
					$("#purity").val("");
					$(".pValue-error").html("Value must be within selected purity range");
				}else{
					$(".pValue-error").html("");
				}
			}
        });
		
	});
	

	var accordion = (function(){

	var $accordion = $('.js-accordion');
	var $accordion_header = $accordion.find('.js-accordion-header');
	var $accordion_item = $('.js-accordion-item');

	// default settings 
	var settings = {
		// animation speed
		speed: 400,

		// close all other accordion items if true
		//oneOpen: false
		oneOpen: true
	};

	return {
			// pass configurable object literal
			init: function($settings) {
			$accordion_header.on('click', function() {
			accordion.toggle($(this));
			});

			$.extend(settings, $settings); 

			// ensure only one accordion is active if oneOpen is true
			if(settings.oneOpen && $('.js-accordion-item.active').length > 1) {
			$('.js-accordion-item.active:not(:first)').removeClass('active');
			}

			// reveal the active accordion bodies
			$('.js-accordion-item.active').find('> .js-accordion-body').show();
			},
			toggle: function($this) {

			/* if(settings.oneOpen && $this[0] != $this.closest('.js-accordion').find('> .js-accordion-item.active > .js-accordion-header')[0]) {
			$this.closest('.js-accordion')
			.find('> .js-accordion-item') 
			.removeClass('active')
			.find('.js-accordion-body')
			.slideUp()
			} */

			// show/hide the clicked accordion item
			$this.closest('.js-accordion-item').toggleClass('active');
			$this.next().stop().slideToggle(settings.speed);
			}
		}
	})();

	$(document).ready(function(){
		accordion.init({ speed: 300, oneOpen: true });
	});
