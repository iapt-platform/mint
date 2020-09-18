var _my_channal = null;

$.post(
      "../channal/get.php",
      {

      },
      function(data){
          try{
              _my_channal = JSON.parse(data);
          }
          catch(e){
              console.error(e);
          }
      }
    );

function channal_getById(id) {
    for (const iterator of _my_channal) {
        if(iterator.id==id){
            return iterator;
        }
    }
    return false;
}