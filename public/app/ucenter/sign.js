function isValidPassword(str){
	let patt=new RegExp(/\s|\//);
	if(patt.test(str)){
		return false;
	}else{
		return true;
	}
}
function isValidUserName(str){
	let patt=new RegExp(/@|\s|\/|[A-Z]/);
	if(patt.test(str)){
		return false;
	}else{
		return true;
	}
}
function submit(){
	let hasError = false;
	if($("#password").val()!==$("#repassword").val()){
		$("#error_password").text("password and repassword is not match");
		hasError = true;
	}
	if(isValidPassword($("#password").val())==false){
		$("#error_password").text(gLocal.gui.password_invaild_symbol);
		hasError = true;
	}
	if($("#password").val().length < 6){
		$("#error_password").text('Password is too short');
		hasError = true;
	}
    if($("#password").val().length > 31){
		$("#error_password").text('Password is too long');
		hasError = true;
	}
	if(isValidUserName($("#username").val())==false){
		$("#error_username").text(gLocal.gui.username_invaild_symbol);
		hasError = true;
	}

	let nickname = $("#nickname").val();	
	if( nickname ==""){
		nickname = $("#username").val();
	}

	let lang = $("#lang").val();
	if(lang=="zh-cn"){
		lang = "zh-hans";
	}
	if(lang == "zh-tw"){
		lang = "zh-hant";
	}
	if(hasError){
		return;
	}
	$.ajax({
		type: 'POST',
		url:"../api/user.php?_method=create",
		contentType:"application/json; charset=utf-8",
		data:JSON.stringify({
			invite:$("#invite").val(),
			username:$("#username").val(),
			password:$("#password").val(),
			email:$("#email").val(),
			nickname:nickname,
			lang:$("#lang").val()
		}),
		dataType:"json"
		}).done(function (data) {
			if(data.ok){
				$("#form_div").hide();
				$("#message").removeClass("form_error");
				$("#message").html(gLocal.gui.successful+" <a href='index.php?op=login'>"+gLocal.gui.login+"</a>");
			}else{
				$("#message").addClass("form_error");
				$("#message").text(ConvertServerMsgToLocalString(data.message));
			}
	}).fail(function(jqXHR, textStatus, errorThrown){
		$("#message").removeClass("form_error");
		$("#message").text(textStatus);				
		switch (textStatus) {
	
			case "timeout":
				break;
			case "error":
				switch (jqXHR.status) {
					case 404:
						break;
					case 500:
						break;				
					default:
						break;
				}
				break;
			case "abort":
				break;
			case "parsererror":			
				console.log("delete-parsererror",jqXHR.responseText);
				break;
			default:
				break;
		}
		
	});
}

function ConvertServerMsgToLocalString(str){
	if(str.slice(0,2)=="::"){
		let msg = str.slice(2);
		if(gLocal.gui.hasOwnProperty(msg)){
			return gLocal.gui[msg];
		}
	}
	return str;
}