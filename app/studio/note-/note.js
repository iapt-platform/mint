/*
{{203-1654-23-45@11@en@*}}
<note>203-1654-23-45@11@en@*</note>
<note id=guid book=203 para=1654 begin=23 end=45 author=11 lang=en tag=*></note>

<note  id=guid book=203 para=1654 begin=23 end=45 author=11 lang=en tag=*>
	<div class=text>
	pali text
	</div>
	<tran>
	</tran>
	<ref>
	</ref>
</note>
*/

/*
解析百科字符串
{{203-1654-23-45@11@en@*}}
<note id=12345 info="203-1654-23-45@11@en@*"><note>
<note id="guid" book=203 para=1654 begin=23 end=45 author=11 lang=en tag=*></note>

*/
function note_init(input){
	let output="<div>";
	let arrInput = input.split("\n");
	for(x in arrInput){
		if(arrInput[x].slice(0,2)=="==" && arrInput[x].slice(-2)=="=="){
			output += "</div>";
			output += "<div class=\"submenu\">";
			output += "<p class=\"submenu_title\" onclick=\"submenu_show_detail(this)\">";
			output += arrInput[x].slice(2,-2);
			output += "<svg class=\"icon\" style=\"transform: rotate(45deg);\">";
			output += "<use xlink:href=\"svg/icon.svg#ic_add\"></use>";
			output += "</svg>";
			output += "</p>";
			output += "<div class=\"submenu_details\" >";
			
		}
		else{
			let row=arrInput[x];
			row = row.replace(/\{\{/g,"<note info=\"");
			row = row.replace(/\}\}/g,"\"></note>");
			if(row.match("{") && row.match("}")){
				row=row.replace("{","<strong>");
				row=row.replace("}","</strong>");
			}
			output+=row;
		}
	}
	output += "</div>";
	return(output);
}

//
function note_refresh_new(){
	$("note").each(function(index,element){
		let html=$(this).html();
		let id=$(this).attr("id");
		if(id==null || id==""){

			id=com_guid();
			$(this).attr("id",id);

			let info=$(this).attr("info");
			if(info && info!=""){
				$.get("./note/note.php",
					{
						id:id,
						info:info,
					},
					function(data,status){
						try{
							let arrData=JSON.parse(data);
							let id=arrData.id;
							let strHtml = note_json_html(arrData);
							$("#"+id).html(strHtml);
							term_updata_translation();
						}
						catch(e){
							console.error(e);
						}
					}
				);				
			}

		}
	});		
	
}
/*
id
palitext
tran
ref
*/
function note_json_html(in_json){
	let output="";
	output += "<div class='palitext'>"+in_json.palitext+"</div>";
	output += "<div class='tran'>"+term_std_str_to_tran(in_json.tran)+"</div>";
	output += "<div class='ref'>"+in_json.ref+"</div>";
	return(output);
}