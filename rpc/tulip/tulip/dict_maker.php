<?php
require dirname(__FILE__) . '/vendor/autoload.php';
require dirname(__FILE__) . '/config.php';
require dirname(__FILE__) . '/console.php';


$dir = dirname(__FILE__) . '/storage/dict';
if(!is_dir($dir)){
    $res = mkdir($dir,0700,true);
    if(!$res){
        echo "error: mkdir fail path=".$dir;
        return 0;
    }
}

//删除目录下所有文件
console('debug','delete all of file');
$files = scandir($dir);
foreach ($files as $key => $file) {
    if(is_file($dir.'/'.$file)){
        unlink($dir.'/'.$file);
    }
}

$stopFile = $dir.'/.stop';
$stop = file_put_contents ($stopFile,'stop');
if($stop === false){
    console('error',"create stop file fail ");
    return 0;
}

$filename = $dir.'/pali-'.date("Y-m-d-h-i-sa").'.syn';
$fp = fopen($filename,'a');
if(!$fp){
    console('error',"open file fail filename=".$filename);
    return 0;
}
$client = new GuzzleHttp\Client();
$currPage = 1;

    $urlBase = Config['api_server'] . '/v2/pg-pali-dict-download';
    console('debug','url='.$urlBase);
    do {
        $goNext = false;
        $url = $urlBase . "?page={$currPage}";
        console('debug','url='.$url);
        $res = $client->request('GET', $url);
        $status = $res->getStatusCode();
        if($status === 200){
            $json = json_decode($res->getBody());
            if($json->ok){
                $content = $json->data;
                console('debug','data size='.strlen($content));
                fwrite($fp,$content."\n");
                console('debug','write to file success');
                $goNext = true;
            }else{
                console('debug', 'all done');
            }
        }else{
            console('error', 'status='.$status);
        }
        $currPage++;
        sleep(1);
    } while ($goNext);
    
fclose($fp);
console('debug','all done filename='.$filename) ;

unlink($stopFile);

