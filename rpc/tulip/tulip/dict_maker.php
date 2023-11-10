<?php
require dirname(__FILE__) . '/vendor/autoload.php';
require dirname(__FILE__) . '/config.php';
require dirname(__FILE__) . '/logger.php';


$dir = dirname(__FILE__) . '/storage/dict';
if(!is_dir($dir)){
    $res = mkdir($dir,0700,true);
    if(!$res){
        echo "error: mkdir fail path=".$dir;
        return 0;
    }
}

//删除目录下所有文件
logger('debug','delete all of file');
$files = scandir($dir);
foreach ($files as $key => $file) {
    if(is_file($dir.'/'.$file)){
        unlink($dir.'/'.$file);
    }
}

$stopFile = $dir.'/.stop';
$stop = file_put_contents ($stopFile,'stop');
if($stop === false){
    logger('error',"create stop file fail ");
    return 0;
}

$filename = $dir.'/pali-'.date("Y-m-d-h-i-sa").'.syn';
$fp = fopen($filename,'a');
if(!$fp){
    logger('error',"open file fail filename=".$filename);
    return 0;
}
$client = new GuzzleHttp\Client();
$currPage = 1;

    $urlBase = Config['api_server'] . '/v2/pg-pali-dict-download';
    logger('debug','url='.$urlBase);
    do {
        $goNext = false;
        $url = $urlBase . "?page={$currPage}";
        logger('debug','url='.$url);
        $res = $client->request('GET', $url);
        $status = $res->getStatusCode();
        if($status === 200){
            $json = json_decode($res->getBody());
            if($json->ok){
                $content = $json->data;
                logger('debug','data size='.strlen($content));
                fwrite($fp,$content."\n");
                logger('debug','write to file success');
                $goNext = true;
            }else{
                logger('debug', 'all done');
            }
        }else{
            logger('error', 'status='.$status);
        }
        $currPage++;
        sleep(1);
    } while ($goNext);
    
fclose($fp);
logger('debug','all done filename='.$filename) ;

unlink($stopFile);

