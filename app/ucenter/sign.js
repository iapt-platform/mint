function submit(){
	if($("#password").val()!==$("#repassword").val()){
		$("#error_password").text("两次密码输入不一致");
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
			nickname:$("#nickname").val(),
			lang:$("#lang").val()
		}),
		dataType:"json"
		}).done(function (data) {
			
			if(data.ok){
				$("#form_div").hide();
				$("#message").removeClass("form_error");
				$("#message").html("注册成功。<a href='index.php?op=login'>登录</a>");

			}else{
				$("#message").addClass("form_error");
				$("#message").text(data.message);
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