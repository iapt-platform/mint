var part_max_deep=10;
var part_curr_deep=0;
var part_word_array=null;

function part_create_node(){
	let newWord=new Object();
	newWord.id="";
	newWord.spell="";
	newWord.real="";
	newWord.type="";
	newWord.gramma="";
	newWord.case="";
	newWord.mean="";
	newWord.note="";
	newWord.sparent="";
	newWord.parent_id="";
	newWord.part="";
	newWord.partmean="";
	newWord.bmc=0;
	newWord.bmt="";
	newWord.lock=false;
	newWord.rela="";
	newWord.parent_visible=true;
	newWord.child_visible=false;
	newWord.valid=true;
	newWord.firstChild=null;
	newWord.nextNode=null;	
	
	newWord.setpart=_part_setpart;

	part_word_array.push(newWord);
	return(newWord);
}


function _part_setpart(value){
	if(value==null){
		return(this.part);
	}
	else{
		if(this.part!=value){
			let arrPart = value.split("+");
			let newParts=new Array();
			for(let x in arrPart){
				let isExist=false;
				let nextNode=this.firstChild;
				while(nextNode)
				{
					if(arrPart[x]==nextNode.real){
						newParts.push(nextNode.clone());
						isExist=true;
						break;
					}
					nextNode = nextNode.nextNode;
				}
				if(isExist==false){
					let newPart = part_create_node();
					newPart.id=this.id+"-"+x;
					newPart.spell = arrPart[x];
					newPart.real = arrPart[x];
					newPart.sparent=this.real;
					newPart.parent_id=this.id;
					newParts.push(newPart);
				}
			}
			let nextNode=this.firstChild;
			while(nextNode)
			{
				nextNode.valid=false;
				nextNode = nextNode.nextNode;
			}
			this.firstChild = newParts[0];
			for(let x =0 ; x<newParts.length-1;x++){
				newParts[x].nextNode = newParts[x+1];
			}
			newParts[newParts.length-1].nextNode = null;
		}
	}
}

function _part_clone(){
	let newNode = part_create_node();
	newNode.id=this.id;
	newNode.spell=this.spell;
	newNode.real=this.real;
	newNode.type=this.type;
	newNode.gramma=this.gramma;
	newNode.case=this.case;
	newNode.mean=this.mean;
	newNode.note=this.note;
	newNode.sparent=this.sparent;
	newNode.parent_id=this.parent_id;
	newNode.part=this.part;
	newNode.partmean=this.partmean;
	newNode.bmc=this.bmc;
	newNode.bmt=this.bmt;
	newNode.lock=this.lock;
	newNode.rela=this.rela;
	newNode.parent_visible=this.parent_visible;
	newNode.child_visible=this.child_visible;
	newWord.valid=this.valid;
	newWord.firstChild=this.firstChild;
	return(newNode);
}
function part_render_children(wordnode){
	
}


function word_part(strSelector){
	
	{
		var sWordId=strSelector.substr(1);
		var xAllWord = gXmlBookDataBody.getElementsByTagName("word");	
		var wid=getWordIndex(sWordId);
		if(xAllWord[wid]){
			var wordobj=new Object();
			wordobj.wordid=strSelector;
			wordobj.element=xAllWord[wid];
			wordobj.val=_word_value;
			return(wordobj);
		}
		else{
			var wordobj=new Object();
			wordobj.wordid=strSelector;
			wordobj.element=null;
			wordobj.val=_word_value;
			return(wordobj);
		}
	}

}
function _word_value(key,value=null){
	if(this.element){
		if(value){
			setNodeText(this.element,key,value);
		}
		else{
			var output = getNodeText(this.element,key);
			return(output);
		}
	}
	else{
		if(!value){
			return("");
		}
	}
}