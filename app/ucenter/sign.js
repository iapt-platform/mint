function submit(){
	let hasError = false;
	if($("#password").val()!==$("#repassword").val()){
		$("#error_password").text("两次密码输入不一致");
		hasError = true;
	}
	let patt1=new RegExp(/\s|\//);
	if(patt1.test($("#password").val())){
		$("#error_password").text("密码包含无效字符。  / 空格 ");
		hasError = true;
	}


	let username = $("#username").val();
	let patt2=new RegExp(/@|\s|\//);
	if(patt2.test(username)){
		$("#error_username").text("用户名包含无效字符。@  / 空格 ");
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
				$("#message").html("注册成功。<a href='index.php?op=login'>"+gLocal.gui.login+"</a>");

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