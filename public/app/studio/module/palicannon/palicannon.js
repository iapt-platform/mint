var strC1;
var strC2;
var strC3;
var strC4;
var strBookTitle;
var strBookFolder;

$( '.sidebar' ).fixedsticky();

function palicannon_init(){
	document.getElementById("id_palicannon_index_c2").style.display="none";
	document.getElementById("id_palicannon_index_c3").style.display="none";
	document.getElementById("id_palicannon_index_c4").style.display="none";
	document.getElementById("id_palicannon_index_book").style.display="none";
	document.getElementById('id_palicannon_index_filelist').innerHTML="";
	
	var objC1 = document.getElementById("id_palicannon_index_c1");
	objC1.innerHTML="";
	var currStr="";
	var list= new Array();
	for(index in local_palicannon_index){
		pc_pushNewToList(list,local_palicannon_index[index].c1);
	}
	for(index in list){
		objC1.innerHTML+="<option value='"+list[index]+"'>"+list[index]+"</option>";
	}
}

function palicannon_index_changed_c1(obj){
	document.getElementById("id_palicannon_index_c2").style.display="none";
	document.getElementById("id_palicannon_index_c3").style.display="none";
	document.getElementById("id_palicannon_index_c4").style.display="none";
	document.getElementById("id_palicannon_index_book").style.display="none";
	document.getElementById('id_palicannon_index_filelist').innerHTML="";
	
	var objC2 = document.getElementById("id_palicannon_index_c2");
	strC1=obj.value;
	objC2.innerHTML="";
	var currStr="";
	var list= new Array();
	for(index in local_palicannon_index){
		if(local_palicannon_index[index].c1==strC1){
			pc_pushNewToList(list,local_palicannon_index[index].c2);
		}
	}
	for(index in list){
		objC2.innerHTML+="<option value='"+list[index]+"'>"+list[index]+"</option>";
	}
	objC2.style.display="block";
}

function palicannon_index_changed_c2(obj){
	document.getElementById("id_palicannon_index_c3").style.display="none";
	document.getElementById("id_palicannon_index_c4").style.display="none";
	document.getElementById("id_palicannon_index_book").style.display="none";
	document.getElementById('id_palicannon_index_filelist').innerHTML="";
	
	var objC3 = document.getElementById("id_palicannon_index_c3");
	strC2=obj.value;
	objC3.innerHTML="";
	var currStr="";
	var list= new Array();
	var bookTitle= new Array();
	var bookFolder= new Array();
	for(index in local_palicannon_index){
		if(local_palicannon_index[index].c1==strC1 && local_palicannon_index[index].c2==strC2){
			if(local_palicannon_index[index].c3!=""){
				pc_pushNewToList(list,local_palicannon_index[index].c3);
			}
			pc_pushNewToList(bookTitle,local_palicannon_index[index].title);
			pc_pushNewToList(bookFolder,local_palicannon_index[index].folder);
		}
	}
	if(list.length==0){
		var objBook = document.getElementById("id_palicannon_index_book");
		objBook.innerHTML="";
		for(index in bookTitle){
			objBook.innerHTML+="<option value='"+bookFolder[index]+"'>"+bookTitle[index]+"</option>";
		}
		document.getElementById("id_palicannon_index_book").style.display="block";
	}
	else{
		for(index in list){
			objC3.innerHTML+="<option value='"+list[index]+"'>"+list[index]+"</option>";
		}
		objC3.style.display="block";
	}
}

function palicannon_index_changed_c3(obj){
	document.getElementById("id_palicannon_index_c4").style.display="none";
	document.getElementById("id_palicannon_index_book").style.display="none";
	document.getElementById('id_palicannon_index_filelist').innerHTML="";
	
	var objC4 = document.getElementById("id_palicannon_index_c4");
	strC3=obj.value;
	objC4.innerHTML="";
	var currStr="";
	var list= new Array();
	var bookTitle= new Array();
	var bookFolder= new Array();
	for(index in local_palicannon_index){
		if(local_palicannon_index[index].c1==strC1 && local_palicannon_index[index].c2==strC2 && local_palicannon_index[index].c3==strC3){
			if(local_palicannon_index[index].c4!=""){
				pc_pushNewToList(list,local_palicannon_index[index].c4);
			}
			pc_pushNewToList(bookTitle,local_palicannon_index[index].title);
			pc_pushNewToList(bookFolder,local_palicannon_index[index].folder);
		}
	}
	if(list.length==0){
		var objBook = document.getElementById("id_palicannon_index_book");
		objBook.innerHTML="";
		for(index in bookTitle){
			objBook.innerHTML+="<option value='"+bookFolder[index]+"'>"+bookTitle[index]+"</option>";
		}
		document.getElementById("id_palicannon_index_book").style.display="block";
	}
	else{
		for(index in list){
			objC4.innerHTML+="<option value='"+list[index]+"'>"+list[index]+"</option>";
		}
	}
}

function palicannon_index_changed_book(obj){
	strBookFolder = obj.value;
	palicannon_show_filelist(strBookFolder);
}

function pc_pushNewToList(inArray,strNew){
	//var isExist=false;
	for(x in inArray){
		if(inArray[x]==strNew){
			return;
		}
	}
	inArray.push(strNew);
}


var palicannon_xmlhttp;
function palicannon_show_filelist(strFolder){
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		palicannon_xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		palicannon_xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	var d=new Date();
	palicannon_xmlhttp.onreadystatechange=palicannon_serverResponse;
	palicannon_xmlhttp.open("GET","./palicannonfilelist.php?t="+d.getTime()+"&folder="+strFolder,true);
	palicannon_xmlhttp.send();
}

function palicannon_serverResponse(){
	if (palicannon_xmlhttp.readyState==4)// 4 = "loaded"
	{
		if (palicannon_xmlhttp.status==200)
		{// 200 = "OK"
			var arrFileList = palicannon_xmlhttp.responseText.split(",");
			var fileList="";
			for (x in arrFileList)
			{
				var dir_myPaliCannon="../user/My Pali Canon/";
				fileList = fileList + "<ul>"
				fileList = fileList + "<li><a href=\"./editor.php?filename="+dir_myPaliCannon+strBookFolder+"/"+arrFileList[x]+"&device="+g_device+"&language=zh\">"+arrFileList[x]+"</a></li>"
				fileList = fileList + "</ul>"
			}
			document.getElementById('id_palicannon_index_filelist').innerHTML=fileList;
		}
		else
		{
			document.getElementById('id_palicannon_index_filelist')="Problem retrieving data:" + xmlhttp.statusText;
		}
	}
}