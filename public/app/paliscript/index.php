<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title>Paḷi Code Transfer</title>
	<script language="javascript" src="js/vn.js"></script>
	<script language="javascript" src="js/xml.js"></script>
	<script language="javascript" src="js/filenew.js"></script>
	<script language="javascript" src="js/editor.js"></script>
	<script language="javascript" src="../public/charcode/sinhala.js"></script>
	<script language="javascript" src="../public/charcode/unicode.js"></script>
	<script language="javascript" src="../public/charcode/myanmar.js"></script>
	<script language="javascript">
function sangayana2unicode(inStr,inM,inM2){
	var output=inStr;
	output=output.replace(/ïk/g,inM + "k");		
	output=output.replace(/ïg/g,inM + "g");		
	output=output.replace(/ü/g,inM);
	output=output.replace(/§/g,inM);
	output=output.replace(/ṃ/g,inM);
	output=output.replace(/ðK/g,inM2 + "K");		
	output=output.replace(/ðG/g,inM2 + "G");		
	output=output.replace(/ý/g,inM2);
	for(unicode_i in char_sanga_to_unicode){
		eval("output=output.replace(/"+char_sanga_to_unicode[unicode_i].id+"/g,char_sanga_to_unicode[unicode_i].value);");
	}

	return(output);
}

function s2u(){
	mstr=document.getElementById("m_str").value;
	mlower=mstr.split(",")[0];
	mupper=mstr.split(",")[1];
	txt=document.getElementById("txtInput").value;
	document.getElementById("txtOutput").value=sangayana2unicode(txt,mlower,mupper);
}
function unicode2sangayana(inStr,inM,inM2){
	var output=inStr;
	for(unicode_i in char_unicode_to_sanga){
		eval("output=output.replace(/"+char_unicode_to_sanga[unicode_i].id+"/g,char_unicode_to_sanga[unicode_i].value);");
	}
	return(output);
}

function u2s(){
		mstr=document.getElementById("m_str").value;
		mlower=mstr.split(",")[0];
		mupper=mstr.split(",")[1];
		txt=" "+document.getElementById("txtInput").value;
		document.getElementById("txtOutput").value=unicode2sangayana(txt,mlower,mupper);
}
		

		
function sinhala2pali(){
	var txt=document.getElementById("txtInput").value;
	output="";
	
	try{
		for(u2s_i in char_si_to_unicode){
			eval("txt=txt.replace(/"+char_si_to_unicode[u2s_i].id+"/g,char_si_to_unicode[u2s_i].value);");
		}
	}

	catch(err)
	{
	  //error
	  alert(err.message);
	}
	document.getElementById("txtOutput").value=document.getElementById("txtInput").value;	
	document.getElementById("txtInput").value=txt;	
	txt=document.getElementById("txtOutput").value;
	u2u()
	document.getElementById("txtInput").value=txt;	
	
}
		
function u2sinhala1(){
	var txt=document.getElementById("txtInput").value;
	output="";
	
	try{
		for(u2s_i in char_unicode_to_si_c){
			eval("txt=txt.replace(/"+char_unicode_to_si_c[u2s_i].id+"/g,char_unicode_to_si_c[u2s_i].value);");
		}
	}
	catch(err)
	  {
	  //error
	  alert(err.message);
	  }
	document.getElementById("txtOutput").value=txt;	
	
}
function u2sinhala2(){
	var txt=document.getElementById("txtInput").value;
	output="";
	
	try{
		for(u2s_i in char_unicode_to_si_n){
			eval("txt=txt.replace(/"+char_unicode_to_si_n[u2s_i].id+"/g,char_unicode_to_si_n[u2s_i].value);");
		}
	}
	catch(err)
	  {
	  //error
	  alert(err.message);
	  }
	document.getElementById("txtOutput").value=txt;	
	
}

		
function unicode2unicode(inStr,inM,inM2){
			var output=inStr;
			output=output.replace(/ṅk/g,inM + "k");		
			output=output.replace(/ṅg/g,inM + "g");		
			output=output.replace(/ŋk/g,inM + "k");		
			output=output.replace(/ŋg/g,inM + "g");		
			output=output.replace(/ŋ/g,inM);
			output=output.replace(/ṁ/g,inM);
			output=output.replace(/ṃ/g,inM);
			output=output.replace(/ṃk/g,"ṅk");
			output=output.replace(/ṁk/g,"ṅk");
			output=output.replace(/ṃg/g,"ṅg");
			output=output.replace(/ṁg/g,"ṅg");
			
			
			output=output.replace(/ṄK/g,inM2 + "K");		
			output=output.replace(/ṄG/g,inM2 + "G");		
			output=output.replace(/ŊK/g,inM2 + "K");		
			output=output.replace(/ŊG/g,inM2 + "G");		
			output=output.replace(/Ŋ/g,inM2);
			output=output.replace(/Ṁ/g,inM2);
			output=output.replace(/Ṃ/g,inM2);
			output=output.replace(/ṂK/g,"ṄG");
			output=output.replace(/ṀK/g,"ṄG");
			output=output.replace(/ṂG/g,"ṄG");
			output=output.replace(/ṀG/g,"ṄG");
			return(output);
		}

function u2u(){
	mstr=document.getElementById("m_str").value;
	mlower=mstr.split(",")[0];
	mupper=mstr.split(",")[1];
	txt=document.getElementById("txtInput").value;
	document.getElementById("txtOutput").value=unicode2unicode(txt,mlower,mupper);
}


function b2s(){
	mstr=document.getElementById("m_str").value;
	mlower=mstr.split(",")[0];
	mupper=mstr.split(",")[1];
	txt=document.getElementById("txtInput").value;
	document.getElementById("txtOutput").value=txt.toLowerCase();

}
		
function u2lkcd(){//兰卡详尽版
			var txt=document.getElementById("txtInput").value;
			output="";
			
	try{
		for(u2s_i in char_chinese_pronounce_1){
			eval("txt=txt.replace(/"+char_chinese_pronounce_1[u2s_i].id+"/g,char_chinese_pronounce_1[u2s_i].value);");
		}

	}



	catch(err)
	  {
	  //error
	  alert(err.message);
	  }
		document.getElementById("txtOutput").value=document.getElementById("txtInput").value;	
		document.getElementById("txtInput").value=txt;	
		txt=document.getElementById("txtOutput").value;
		u2u()
		document.getElementById("txtInput").value=txt;	
}
function myanmar_to_roman_1(){
	var txt=document.getElementById("txtInput").value;
	output="";
	
	try{
		for(m_to_r_i in char_myn_to_roman_1){
			eval("txt=txt.replace(/"+char_myn_to_roman_1[m_to_r_i].id+"/g,char_myn_to_roman_1[m_to_r_i].value);");
		}
	}

	catch(err)
	{
	  //error
	  alert(err.message);
	}
	document.getElementById("txtOutput").value=document.getElementById("txtInput").value;	
	document.getElementById("txtInput").value=txt;	
	txt=document.getElementById("txtOutput").value;
	u2u()
	document.getElementById("txtInput").value=txt;	
	
}

function myanmar_to_roman_2(){
	var txt=document.getElementById("txtInput").value;
	output="";
	
	try{
		for(m_to_r_i in char_myn_to_roman_2){
			eval("txt=txt.replace(/"+char_myn_to_roman_2[m_to_r_i].id+"/g,char_myn_to_roman_2[m_to_r_i].value);");
		}
	}

	catch(err)
	{
	  //error
	  alert(err.message);
	}
	document.getElementById("txtOutput").value=document.getElementById("txtInput").value;	
	document.getElementById("txtInput").value=txt;	
	txt=document.getElementById("txtOutput").value;
	u2u()
	document.getElementById("txtInput").value=txt;	
	
}


function roman_to_myanmar(){
	var txt=document.getElementById("txtInput").value;
	output="";
	
	try{
		for(r_to_m_i in char_roman_to_myn){
			eval("txt=txt.replace(/"+char_roman_to_myn[r_to_m_i].id+"/g,char_roman_to_myn[r_to_m_i].value);");
		}
	}

	catch(err)
	{
	  //error
	  alert(err.message);
	}
	document.getElementById("txtOutput").value=document.getElementById("txtInput").value;	
	document.getElementById("txtInput").value=txt;	
	txt=document.getElementById("txtOutput").value;
	u2u()
	document.getElementById("txtInput").value=txt;	
	
}
		
function u2lkcs(){//兰卡简易版
	var txt=document.getElementById("txtInput").value;
	output="";
			
	try{
		for(u2s_i in char_chinese_pronounce_2){
			eval("txt=txt.replace(/"+char_chinese_pronounce_2[u2s_i].id+"/g,char_chinese_pronounce_2[u2s_i].value);");
		}
	}

	catch(err)
	  {
	  //error
	  alert(err.message);
	  }
				document.getElementById("txtOutput").value=document.getElementById("txtInput").value;	
				document.getElementById("txtInput").value=txt;	
				txt=document.getElementById("txtOutput").value;
				u2u()
				document.getElementById("txtInput").value=txt;	
				
}

function u2cnnandiya(){
			var txt=document.getElementById("txtInput").value;
			output="";
			
	try
	{
		for(u2s_i in char_chinese_pronounce_CN_Nadiya){
			eval("txt=txt.replace(/"+char_chinese_pronounce_CN_Nadiya[u2s_i].id+"/g,char_chinese_pronounce_CN_Nadiya[u2s_i].value);");
		}
	}

	catch(err)
	  {
	  //error
	  alert(err.message);
	  }
	document.getElementById("txtOutput").value=document.getElementById("txtInput").value;	
	document.getElementById("txtInput").value=txt;	
	txt=document.getElementById("txtOutput").value;
	u2u()
	document.getElementById("txtInput").value=txt;	
}

function u2lkpy(){//兰卡拼音版
	var txt=" "+document.getElementById("txtInput").value;
	output="";
	try
	{
		for(u2s_i in char_chinese_pronounce_pinyin){
			eval("txt=txt.replace(/"+char_chinese_pronounce_pinyin[u2s_i].id+"/g,char_chinese_pronounce_pinyin[u2s_i].value);");
		}
	}
	catch(err)
	{
	//error
	alert(err.message);
	}
	document.getElementById("txtOutput").value=document.getElementById("txtInput").value;	
	document.getElementById("txtInput").value=txt.toLowerCase();	
	txt=document.getElementById("txtOutput").value;
	u2u()
	document.getElementById("txtInput").value=txt;	
}
function un2telugu(){
	var txt=document.getElementById("txtInput").value;
	output="";
			
	try{
		for(u2s_i in char_unicode_to_telugu){
			eval("txt=txt.replace(/"+char_unicode_to_telugu[u2s_i].id+"/g,char_unicode_to_telugu[u2s_i].value);");
		}
	}
	catch(err)
	{
	//error
	alert(err.message);
	}
	document.getElementById("txtOutput").value=document.getElementById("txtInput").value;	
	document.getElementById("txtInput").value=txt;	
	txt=document.getElementById("txtOutput").value;
	u2u()
	document.getElementById("txtInput").value=txt;	
}
		
		
function run(){
	var input_opt=document.getElementById("input_str").value
	var output_opt=document.getElementById("output_str").value
	switch(input_opt){
		case "1":	
			s2u()
		break;
		case "2":	
			u2u()
		break;
		case "3":
			sinhala2pali()
		break;
		case "4":
			myanmar_to_roman_1()
		break;
		case "5":
			myanmar_to_roman_2()
		break;
	}
	txt0=document.getElementById("txtInput").value
	document.getElementById("txtInput").value=document.getElementById("txtOutput").value
	switch(output_opt){
		case "1":	
			u2s()
		break;
		case "2":	
			u2u()
		break;
		case "3":
			b2s()
			document.getElementById("txtInput").value=document.getElementById("txtOutput").value
			u2sinhala1()
		break;
		case "4":
			b2s()
			document.getElementById("txtInput").value=document.getElementById("txtOutput").value
			u2sinhala2()
		break;
		case "4":
			b2s()
			document.getElementById("txtInput").value=document.getElementById("txtOutput").value
			u2lkcd()
		break;
		case "6":
			b2s()
			document.getElementById("txtInput").value=document.getElementById("txtOutput").value
			u2lkcs()
		break;
		case "7":
			b2s()
			document.getElementById("txtInput").value=document.getElementById("txtOutput").value
			u2cnnandiya()
		break;
		case "8":
			b2s()
			document.getElementById("txtInput").value=document.getElementById("txtOutput").value
			u2lkpy()
		break;
		case "9":
			b2s()
			document.getElementById("txtInput").value=document.getElementById("txtOutput").value
			un2telugu()
		break;
		case "10":
			b2s()
			document.getElementById("txtInput").value=document.getElementById("txtOutput").value
			roman_to_myanmar()
		break;
	
	}
	
	document.getElementById("txtInput").value=txt0
}
	</script>
	<style>
	.bg0{
		background-color:#FF9;
	}
	.bg1{
		background-color:#AAA;
	}

	</style>
</head>
<body class="mainbody" id="mbody" style="font-family: 'Noto Sans','Noto Sans CJK TC', 'Noto Sans CJK SC', 'Noto Sans TC', 'Noto Sans SC', 'Noto Sans CJK', Verdana, sans-serif; font-size:18px;">
		<!-- tool bar begin-->

		<div class='toolbar' >

			<script language="javascript">
			if(!g_is_mobile){
				document.write("<button id=\"B_Navi\" onclick=\"setNaviVisibility()\" type=\"button\">≡</button> ");
			}

			</script>
				<script language="javascript">
				</script>
				<span id="debug"></span>

		</div>	
		<!--tool bar end -->

	<div class="main" style="width:100%;height:100%;text-align:center; ">

				<div style="text-align:center; float:left;width:40%;height:80%;">
				My Text Is In<select id="input_str" style="align:center; float:down; font-size: 100%;margin:20px;">
						<option value="1">Sangayana</option>
						<option value="2">Unicode</option>
						<option value="3">Sinhala</option>
						<option value="4">Myanmar1</option>
						<option value="5">Myanmar2</option>
				</select></br>
				<textarea id="txtInput" rows="30" cols="" style="float:left; font-family: 'Noto Sans','Noto Sans CJK TC', 'Noto Sans CJK SC', 'Noto Sans TC', 'Noto Sans SC', 'Noto Sans CJK', Verdana, sans-serif; font-size:16px; width:100%;height:100%;"></textarea>
				</div>
				<div style="text-align:center; float:left;width:15%;">
				</br></br></br></br></br></br></br></br></br></br></br></br>
				ṃ/ṁ/ŋ become
				<select id="m_str" style="align:center; font-size: 120%">
						<option value="ṃ,Ṃ">ṃ</option>
						<option value="ṁ,Ṁ">ṁ</option>
						<option value="ŋ,Ŋ">ŋ</option>
				</select>
				</br>
				</br>
				<button id="transfer" style="align:center; font-size: 200%; width:70px" type="button"  onclick="run()">→</button>
				</div>
				<div style="text-align:center; float:left;width:40%;height:80%;">
				I Need the Text In<select id="output_str" style="align:center; float:down; font-size: 100%;margin:20px;">
						<option value="1">Sangayana</option>
						<option value="2">Unicode</option>
						<option value="3">Sinhala1</option>
						<option value="4">Sinhala2</option>
						<option value="5">锡兰注音[详]</option>
						<option value="6">锡兰注音[简]</option>
						<option value="7">Nadiya注音[简]</option>
						<option value="8">汉语拼音[锡]</option>
						<option value="9">Telugu</option>
						<option value="10">Myanmar</option>
				</select></br>
				<textarea id="txtOutput" rows="30" cols="" style="font-family: 'Noto Sans','Noto Sans CJK TC', 'Noto Sans CJK SC', 'Noto Sans TC', 'Noto Sans SC', 'Noto Sans CJK', Verdana, sans-serif; font-size:16px; width:100%;height:100%"></textarea>
				</div>
				


			<div class="debugMsg" id="xmldata"><!--调试信息-->
	
			</div>

		<!--right side end-->
	</div>

<div id="modifywin">

</div>
</body>
</html>

