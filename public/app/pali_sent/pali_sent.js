
var pali_sent_get_word_callback=null;

function pali_sent_get_word(strWord,callback){
	pali_sent_get_word_callback=callback;
  	$.get("../pali_sent/pali_sent.php",
	{
		op:"get",
		word:strWord,
		format:"json"
	},
	function(data,status){
		let html="";
		if(status=="success"){
			try{
				pali_sent_get_word_callback(data);
			}
			catch(e){
				console.error(e.message);
			}
		}
	});
}