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
