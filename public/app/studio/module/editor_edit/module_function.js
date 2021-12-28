

/*
 * Modle Init.
 * public
 * @param param1 (type) 
 *
 * Example usage:
 * @code
 * @endcode

 */
function editor_edit_init(){

}

function editor_edit_revision_mode(obj){
	if(obj.checked){
		var mWordNode = gXmlBookDataBody.getElementsByTagName("word");
		/*遍历所有单词*/
		for(k=0;k<mWordNode.length;k++)
		{
			modifyWordDetailByWordId(k);
		}
		
		xBlock=gXmlBookDataBody.getElementsByTagName("block");
		for(var iBlock=0;iBlock<xBlock.length;iBlock++){
			xmlParInfo = xBlock[iBlock].getElementsByTagName("info")[0];
			xmlParData = xBlock[iBlock].getElementsByTagName("data")[0];
			bookId=getNodeText(xmlParInfo,"book")
			paragraph=getNodeText(xmlParInfo,"paragraph")
			type=getNodeText(xmlParInfo,"type")
			revision=getNodeText(xmlParInfo,"revision")
			if(type=="wbw"){
				newBlock = xBlock[iBlock].cloneNode(true)
				xmlNewInfo = newBlock.getElementsByTagName("info")[0];
				xmlNewData = newBlock.getElementsByTagName("data")[0];
				setNodeText(xmlNewInfo,"revision",newWbwRevision)
				newWbwBlockList.push(newBlock)
			}
		}
	}
}

