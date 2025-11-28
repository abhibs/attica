
"use strict";

localStorage.clear();


// ----------------------------- ADD ROWID TO LOCAL-STORAGE & NAME TO THE QUOTATION ---------------------------------------- //
$('#quotationModal').on('show.bs.modal', function (event) {
	const button = $(event.relatedTarget);
	$(this).find('.qCustomerName').text(button.data().rowname);
	localStorage.setItem("quotationRowId", button.data().rowid);
	localStorage.setItem("quotationContact", button.data().contact);
	localStorage.setItem("quotationTime", button.data().ctime);
});
$('#quotationModal').on('hidden.bs.modal', function (event) {
	localStorage.clear();
});


// ----------------------------- PHYSICAL ---------------------------------------- // 
const goto_billing = document.querySelectorAll(".q-goto-billing");
goto_billing.forEach((btn, i)=>{
	btn.addEventListener("click", (e)=>{
		const rowId = localStorage.getItem("quotationRowId");
		const rowContact = localStorage.getItem("quotationContact");
		const rowTime = localStorage.getItem("quotationTime");
		
		if(rowId !== "" && rowContact !== "" && rowTime !== ""){
			const url = `xcheckCustomer.php?contact=${rowContact}&Id=${rowId}&encTime=${rowTime}`;
			localStorage.clear();
			window.location.href = url;
		}		
	});
});


// ----------------------------- PHYSICAL ---------------------------------------- //
class Physical{
	constructor(html2canvas){
		this.html2canvas = html2canvas;
		this.physicalSubmit = document.getElementById('sendPhysicalQuotButton');
		this.physicalFullDiv = document.getElementById('physicalGold');
		
		this.physical_input = document.querySelectorAll("#qPhysicalData input, select");
		this.rate = this.physical_input[0];
		this.ornament = this.physical_input[1];
		this.grossW = this.physical_input[2];
		this.stoneW = this.physical_input[3];
		this.netW = this.physical_input[4];
		this.code = this.physical_input[5];
		this.system_purity = this.physical_input[6];
		this.given_purity = this.physical_input[7];
		this.button = this.physical_input[8];
		
		this.system_purity_text = document.querySelector(".system_purity_text");
		this.given_purity_text = document.querySelector(".given_purity_text");
		
		this.physicalTableBody = document.getElementById('qPhysicalTableBody');
		this.physicalTableFoot = document.getElementById('qPhysicalTableFoot');
		
		this.total_GrossA = document.getElementById('qGrossAPhysical');
		this.total_MarginPerc = document.getElementById('qMarginPercPhysical');
		this.total_MarginA = document.getElementById('qMarginAPhysical');
		this.total_NetA = document.getElementById('qNetAPhysical');
		
		this.remarks = document.getElementById('qPhysicalRemarks');
		this.having = document.getElementById('qPhysicalHaving');
		
		this.code_value = {
			"1001" : {"system": [92, 99.9], "given": [25, 99.9] },
			"1002" : {"system": [91.6, 91.6], "given": [25, 91.6]},
			"1003" : {"system": [85, 91], "given": [25, 87]},
			"1004" : {"system": [75, 84], "given": [25, 79]},
			"1005" : {"system": [0, 74], "given": [0, 70]},
			"Silver": {"system": [0, 100], "given": [0, 100]}
		};
		
		this.calculate_netW();
		this.purity_display_range();
		this.add_ornament();
		this.total_margin_change();
		this.submitEnquiry();
	}
	calculate_netW(){
		this.stoneW.addEventListener('change', (event)=>{
			if(this.grossW.value !== '' && this.stoneW.value !== 0){
				this.netW.value = (+this.grossW.value - +this.stoneW.value).toFixed(2);
			}
			else{
				alert("Gross Weight & Stone Weight must have value");
			}
		});
	}
	purity_display_range(){
		this.code.addEventListener("change", (event)=>{
			if(this.code.value !== "Silver"){
				this.system_purity_text.style.display = "block";
				this.system_purity_text.innerHTML = `${this.code_value[this.code.value]["system"][0]}% to ${this.code_value[this.code.value]["system"][1]}%`;
				this.given_purity_text.style.display = "block";
				this.given_purity_text.innerHTML = `${this.code_value[this.code.value]["given"][0]}% to ${this.code_value[this.code.value]["given"][1]}%`;
			}
			else{
				this.system_purity_text.style.display = "none";
				this.given_purity_text.style.display = "none";
			}
		});
	}
	purity(code, system, given){
		if((system >= this.code_value[code]["system"][0] && system <= this.code_value[code]["system"][1]) && 
			(given >= this.code_value[code]["given"][0] && given <= this.code_value[code]["given"][1])){
			return true;
		}
		return false;
	}
	calculateColumn(){
		const tr = Array.from(this.physicalTableBody.children);
		const tfoot = Array.from(this.physicalTableFoot.children);
		const index = [1, 2, 3, 6, 8];
		const sum = [0, 0, 0, 0, 0];
		
		let percTd;
		
		index.forEach((colNum, i) => {
			tr.forEach((item, j) => {
				sum[i] += +item.childNodes[colNum].textContent;
			});
			
			if(colNum == 6){
				percTd = tfoot[0].children[colNum];
			}
			else{
				tfoot[0].children[colNum].textContent = sum[i].toFixed(2);
			}
			
		});
		this.total_GrossA.value = sum[4].toFixed();
		this.total_MarginPerc.value = 3;
		this.total_MarginA.value = (+sum[4] * (3/100)).toFixed();
		this.total_NetA.value = (sum[4] - (+sum[4] * (3/100))).toFixed();
		
		percTd.textContent = ((+this.total_GrossA.value / +sum[2] / +this.rate.value) * 100).toFixed(2);
	}
	total_margin_change(){
		this.total_MarginPerc.addEventListener("change", (event)=>{
			if(this.total_GrossA.value !== ''){
				let grossA = this.total_GrossA.value;
				let marginPerc = this.total_MarginPerc.value;
				this.total_MarginA.value = (+grossA * (marginPerc/100)).toFixed();
				this.total_NetA.value = (+grossA - (+grossA * (marginPerc/100))).toFixed();
			}
		});
	}
	add_ornament(){
		this.button.addEventListener("click", (event)=>{
			const data = {
				ornament : this.ornament.value,	
				grossW : this.grossW.value,
				stoneW : this.stoneW.value,
				netW : this.netW.value,
				code : this.code.value,
				system : this.system_purity.value,
				given : this.given_purity.value,
				rate : this.rate.value
			};
			if( data.ornament !== '' && 
				data.rate !== '' && 
				data.grossW !== '' && 
				data.stoneW !== '' && 
				data.netW !== '' && 
				data.code !== '' && 
				data.system !== '' && 
				data.given !== ''){
				if(this.purity(data.code, data.system, data.given)){
					
					// RESET THE INPUT VALUE
					for(let i=1; i<8; i++){
						this.physical_input[i].value = '';
					}
					this.system_purity_text.style.display = "none";
					this.given_purity_text.style.display = "none";
					
					// ADD ROW TO THE TABLE
					const tr = document.createElement("tr");
					for (var item in data) {
						const td = document.createElement('td');
						td.textContent = data[item];
						tr.appendChild(td);
					}
					const grossA = document.createElement('td');
					grossA.textContent = (+data.netW * +data.rate * (+data.given / 100)).toFixed();
					tr.appendChild(grossA);
					
					// ADD DELETE BUTTON FOR THE ROW
					const delButtontd = document.createElement('td');
					const delButton = document.createElement('input');
					delButton.value = 'X';
					delButton.type = 'button';
					delButton.classList.add('btn', 'btn-danger', 'btn-xs');
					delButtontd.appendChild(delButton);
					delButton.addEventListener('click',(event)=>{
						delButton.parentNode.parentNode.remove();
						this.calculateColumn();
					});
					tr.appendChild(delButtontd);
					
					this.physicalTableBody.appendChild(tr);
					this.calculateColumn();
				}
				else{
					alert("Purity Range is Mismatched");
				}
			}
			else{
				alert("Please Fill All The Data");
			}
		});
	}
	submitEnquiry(){
		this.physicalSubmit.addEventListener("click", async (e)=>{
			if(this.total_GrossA.value != "" && this.remarks.value != "" && this.having.value != ""){
				const tfoot = Array.from(this.physicalTableFoot.children);
				this.disableSubmit();
				
				let formData = new FormData();
				formData.append("quotationAndEnquiry", true);
				formData.append("rowId", localStorage.getItem("quotationRowId"));
				formData.append("gold", "physical");
				formData.append("metal", (this.rate.value > 500) ? "Gold" : "Silver");
				formData.append("grossW", tfoot[0].children[1].textContent);
				formData.append("netW", tfoot[0].children[3].textContent);
				formData.append("purity", tfoot[0].children[6].textContent);
				formData.append("remarks", this.remarks.value);
				formData.append("having", this.having.value);
				formData.append("rate", this.rate.value);
				
				const canvas = await this.html2canvas(this.physicalFullDiv);
				formData.append("quotationImage", canvas.toDataURL());
				
				const response = await fetch("quotationImageUpload.php", {
					method: "POST",
					body: formData
				})
				const result = await response.json();
				if(result.status == "successful"){
					localStorage.clear();
					window.location.reload();
				}
				else{
					console.log(result);
					this.enableSubmit();
					alert("Something went wrong please try again later");
				}
			}
			else{
				alert("Don't Submit Empty Quotation & fill all fields");
			}
		})
	}	
	disableSubmit(){
		this.physicalSubmit.disabled = true;
		this.physicalSubmit.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
	}
	enableSubmit(){
		this.physicalSubmit.removeAttribute('disabled');
		this.physicalSubmit.innerHTML = 'Login';
	}
}
new Physical(html2canvas);


// ----------------------------- RELEASE ---------------------------------------- //

class Release{
	constructor(html2canvas){
		this.html2canvas = html2canvas;
		this.releaseSubmit = document.getElementById('sendReleaseQuotButton');
		this.releaseFullDiv = document.getElementById('releaseGold');
		
		this.rate = document.getElementById('qReleaseRate');
		this.amount = document.getElementById('qReleaseA');
		this.grossW = document.getElementById('qReleaseGrossW');
		this.netW = document.getElementById('qReleaseNetW');
		this.purity = document.getElementById('qReleasePurity');
		this.row = document.getElementById('qRelease91');
		this.button = document.getElementById('quotation-button-release');
		
		this.remarks = document.getElementById('qReleaseRemarks');
		
		this.add_release_data();
		this.submitEnquiry();
	}
	add_release_data(){
		this.button.addEventListener("click", (event)=>{
			let rate = this.rate.value;
			let releaseA = this.amount.value;
			let grossW = this.grossW.value;
			let netW = this.netW.value;
			
			if(releaseA!=='' &&  grossW!=='' && netW!=='' && rate!==''){
				let xAmount = netW * rate;
				let tdRow = Array.from(this.row.children);
				tdRow[1].textContent = rate;
				tdRow[2].textContent = (xAmount * 0.91).toFixed();
				tdRow[3].textContent = ((xAmount * 0.91 * 0.98)-releaseA).toFixed();
				tdRow[4].textContent = ((xAmount * 0.91 * 0.97)-releaseA).toFixed();
				
				let purity = (((releaseA / netW) / rate) * 100).toFixed(2);
				this.purity.value = purity;
				if(purity <= 70){
					this.purity.style.backgroundColor = 'green';
					this.purity.style.color = 'white';
				}
				else if(purity > 70 && purity <= 80){
					this.purity.style.backgroundColor = 'yellow';
					this.purity.style.color = 'black';
				}
				else if(purity > 80 && purity <= 85){
					this.purity.style.backgroundColor = 'red';
					this.purity.style.color = 'white';
				}
				else{
					this.purity.style.backgroundColor = 'DarkRed';
					this.purity.style.color = 'white';
				}
			}
			else{
				alert("Please Fill All The Data");
			}
		});
	}
	submitEnquiry(){
		this.releaseSubmit.addEventListener("click", async (e)=>{
			if(this.purity.value != "" && this.remarks.value != ""){
				this.disableSubmit()
				
				let formData = new FormData();
				formData.append("quotationAndEnquiry", true);
				formData.append("rowId", localStorage.getItem("quotationRowId"));
				formData.append("gold", "release");
				formData.append("metal", (this.rate.value > 500) ? "Gold" : "Silver");
				formData.append("grossW", this.grossW.value);
				formData.append("netW", this.netW.value);
				formData.append("purity", this.purity.value);
				formData.append("remarks", this.remarks.value);
				formData.append("having", "without");
				formData.append("rate", this.rate.value);
				formData.append("releaseAmount", this.amount.value);
				
				const canvas = await this.html2canvas(this.releaseFullDiv);
				formData.append("quotationImage", canvas.toDataURL());
				
				const response = await fetch("quotationImageUpload.php", {
					method: "POST",
					body: formData
				})
				const result = await response.json();
				if(result.status == "successful"){
					localStorage.clear();
					window.location.reload();
				}
				else{
					console.log(result);
					this.enableSubmit();
					alert("Something went wrong please try again later");
				}
			}
			else{
				alert("Don't Submit Empty Quotation & fill all fields");
			}
		})
	}
	disableSubmit(){
		this.releaseSubmit.disabled = true;
		this.releaseSubmit.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
	}
	enableSubmit(){
		this.releaseSubmit.removeAttribute('disabled');
		this.releaseSubmit.innerHTML = 'Login';
	}
}
new Release(html2canvas);
