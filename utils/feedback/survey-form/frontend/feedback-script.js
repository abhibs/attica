class Feedback{
	constructor(){
		this.modal_open_button = document.getElementById("feedbackModalBtn");

		this.feedback_element = document.getElementById("feedback");
		this.modal_background = null;
		this.modal_body = null;

		this.branchId = this.feedback_element.dataset.branchid;
		this.empId = this.feedback_element.dataset.empid;

		this.modal_btn_event();

		this.questions = null;
		this.index = 0;
		this.quiz_result = [];

	}
	modal_btn_event(){
		this.modal_open_button.addEventListener("click", (event)=>{
			this.open_modal();
		});
	}
	open_modal(){
		this.generate_fixed_background();
		this.generate_modal_content();
		this.ask_consent();
	}
	generate_fixed_background(){
		this.modal_background = document.createElement("div");
		this.modal_background.classList.add("feedback-modal");

		this.feedback_element.append(this.modal_background);
	}
	generate_modal_content(){
		const div = document.createElement("div");
		div.classList.add("feedback-modal-content");

		const h1 = document.createElement("h1");
		h1.classList.add("header");
		h1.textContent = "Feedback Survey";

		this.modal_body = document.createElement("div");
		this.modal_body.classList.add("feedback-body");

		div.append(h1);
		div.append(this.modal_body);
		this.modal_background.append(div);	
	}
	ask_consent(){
		this.modal_body.innerHTML = "";
		const takeBtn = document.createElement("button");
		takeBtn.classList.add("primary-btn");
		takeBtn.textContent = "Take Survey";
		takeBtn.addEventListener("click", (e)=>{
			this.start_survey();
		})

		const laterBtn = document.createElement("button");
		laterBtn.textContent = "Ask Later";
		laterBtn.classList.add("cancel-btn");
		laterBtn.addEventListener("click", ()=>{
			this.close_modal();
		});	
		
		this.modal_body.append(takeBtn);
		this.modal_body.append(laterBtn);
	}
	async start_survey(){
		this.loading_screen();
		const response = await this.get_questions();
		if(response){
			if(response['status'] == "questions"){
				this.questions = {
					main: Object.values(response['questions'].main),
					sub: response['questions'].sub
				} 
				// console.log(this.questions);

				this.index = 0;
				this.generate_question_content(this.questions['main'][this.index]);
			}
			else if(response['status'] == "sms"){
				this.qr_code_screen(response['url']);
			}
		}
		else{
			this.close_modal();
		}
	}
	async get_questions(){
		try{
			const response = await fetch(`utils/feedback/survey-form/backend/getQuestions.php?branchId=${this.branchId}`);
			const result = await response.json(); 
			// console.log(result);
			return result;
		}
		catch(error){
			console.log(error);
			return null
		}		
	}
	generate_question_content(question){
		this.modal_body.innerHTML = null;

		// QUESTION
		const question_div = document.createElement("p");
		question_div.classList.add("question");
		question_div.textContent = `${question['question']}`;
		this.modal_body.append(question_div);

		// IMAGE
		if(question['image'] != ''){
			const questionImage = document.createElement("img");
			questionImage.src = `utils/feedback/survey-form/frontend/images/${question['image']}`;
			questionImage.classList.add("img-style");
			this.modal_body.append(questionImage);
		}

		// OPTIONS
		const answer_div = document.createElement("div");
		answer_div.classList.add("answer-content");
		this.modal_body.append(answer_div);

		for(let option in question['options']){
			const btn = document.createElement('button');
			btn.textContent = option;			
			if(option == "Yes"){
				btn.classList.add("answer-yes-button");			
			}
			else{
				btn.classList.add("answer-no-button");			
			}
			btn.addEventListener("click", (event)=>{
				this.quiz_result.push({
					id: question['id'],
					topic: question['topic'],
					question: question['question'],
					answer: option,
					sms: question['sms']
				});
				if(question['options'][option] != null){
					this.generate_question_content(this.questions.sub[question['options'][option]]);
				}
				else{
					this.index++;
					if(this.index < this.questions.main.length){
						this.generate_question_content(this.questions['main'][this.index]);
					}
					else{
						this.final_submit();
					}
				}
				// console.log(this.quiz_result); 
			});
			answer_div.append(btn);
		}
	}	
	final_submit(){
		this.modal_body.innerHTML = null;

		const p = document.createElement("p");
		p.classList.add("final-message");
		p.textContent = "Thank You for taking the survey";

		const submit = document.createElement("button");
		submit.classList.add("primary-btn");
		submit.textContent = "Submit your Answers";
		submit.addEventListener("click", ()=>{
			submit.setAttribute("disabled", true);
			this.upload_result();
		})

		this.modal_body.append(p);
		this.modal_body.append(submit);
	}	
	async upload_result(){
		this.loading_screen();

		const requestData = {
			branchId: this.feedback_element.dataset.branchid,
			empId: this.feedback_element.dataset.empid,
			data: this.quiz_result
		}
		// console.log(requestData);

		const response = await fetch("utils/feedback/survey-form/backend/insertQuizAnswers.php", {
			method: "POST",
			headers: {
				"Content-Type": "application/json"
			},
			body: JSON.stringify(requestData)
		});
		const result = await response.json();
		console.log(result);
		if(result['message'] == "Success"){	
			this.qr_code_screen(result['url']);
		}
		else{
			this.ask_consent();
		}
		
	}
	qr_code_screen(url){
		this.modal_body.innerHTML = null;

		const title = document.createElement("p");
		title.textContent = "Please Scan the QR code, go the link and upload the photos";
		title.classList.add("qr-code-message");
		this.modal_body.append(title);

		const div = document.createElement("div");
		div.setAttribute("id", "qrcode");
		this.modal_body.append(div);

		const qrcode = new QRCode("qrcode", {
			text: url,
			width: 256,
			height: 256,
			colorDark : "#ffffff",
			colorLight : "#000000",
			correctLevel : QRCode.CorrectLevel.H
		});

		const link = document.createElement("a");
		link.setAttribute("href", url);
		link.setAttribute("target", "_BLANK");
		link.textContent = "Or click here to go to the LINK";
		link.style.color = "red";
		this.modal_body.append(link);

		const closeBtn = document.createElement("button");
		closeBtn.textContent = "Close";
		closeBtn.classList.add("cancel-btn");
		closeBtn.addEventListener("click", ()=>{
			this.close_modal();
		});
		this.modal_body.append(closeBtn);
	}
	loading_screen(){
		this.modal_body.innerHTML = null;

		const img = document.createElement("img");
		img.src = "utils/feedback/survey-form/frontend/images/ajax-loading-gif-1.gif";

		this.modal_body.append(img);
	}
	close_modal(){
		this.modal_background.remove();
	}
}

new Feedback();