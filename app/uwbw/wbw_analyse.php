<?php
    require_once "../path.php";
    require_once "../public/_pdo.php";
    require_once '../public/load_lang.php';
    require_once '../public/function.php';

    global $PDO;
    PDO_Connect("sqlite:"._FILE_DB_USER_WBW_);


    $query = "SELECT * from wbw where  1";
						
    $sth = $PDO->prepare($query);
    $sth->execute();
    // owner, word,book,para,wid,type,text
    $i=0;
    while($result = $sth->fetch(PDO::FETCH_ASSOC))
    {
        try {
            $xmlString =  "<root>".$result["data"]."</root>";
            echo  $xmlString."<br>";
            $xmlWord = simplexml_load_string($xmlString);
            $wordsList = $xmlWord->xpath('//word');
            foreach ($wordsList as $word) {
                $pali = $word->real->__toString();
                foreach ($word as $key => $value) {
                    $strValue = $value->__toString();
                    if($strValue !== "?" && $strValue !== "" && $strValue !== ".ctl."){
                        switch ($key) {
                            case 'type':
                            case 'gramma':
                            case 'mean':
                            case 'org':
                            case 'om':
                                var_dump( array($result["owner"],$pali,$result["book"],$result["paragraph"],$result["wid"],$key,$strValue));
                                break;    
                            
                        }
                    }
                }
            }

        } catch ( Throwable $e) {
            echo "Captured Throwable: " . $e->getMessage();
        }

        
        if($i>100){
        break;
        }
        else{
            $i++;
        }
    }

?>