var objCurrMouseOverPaliMean=null;

		function getWordMeanMenu(pali){
			var mean_menu="";
				if(bh[pali]){
					var arrMean=bh[pali].split("$");
					if(arrMean.length>0){
						for(var i in arrMean){
							mean_menu+="<a>"+arrMean[i]+"</a>";
						}
					}
				}
				else if(sys_r[pali]){
					var word_parent=sys_r[pali];
					if(bh[word_parent]){
						var arrMean=bh[word_parent].split("$");
						if(arrMean.length>0){
						for(var i in arrMean){
							mean_menu+="<a onclick=set_mean('"+arrMean[i]+"')>"+arrMean[i]+"</a>";
						}
						}
					}
				}
			return(mean_menu);
		}
		
		function set_mean(str){
			if(objCurrMouseOverPaliMean){
				objCurrMouseOverPaliMean.innerHTML=str;
			}
		}