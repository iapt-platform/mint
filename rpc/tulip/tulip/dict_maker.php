<?php
require dirname(__FILE__) . '/vendor/autoload.php';
require dirname(__FILE__) . '/config.php';

$dir = dirname(__FILE__) . '/storage';
if(!is_dir($dir)){
    $res = mkdir($dir,0700,true);
    if(!$res){
        echo "error: mkdir fail path=".$dir;
        return 0;
    }
}
$filename = $dir.'/pali-'.date("Y-m-d-h-i-sa").'.syn';
$fp = fopen($filename,'a');
if(!$fp){
    echo "open file fail filename=".$filename;
    return 0;
}
$client = new GuzzleHttp\Client();
$currPage = 1;

    $urlBase = Config['api_server'] . '/v2/pg-pali-dict-download';
    echo $urlBase.PHP_EOL;
    do {
        $goNext = false;
        $url = $urlBase . "?page={$currPage}";
        echo $url.PHP_EOL;
        $res = $client->request('GET', $url);
        $status = $res->getStatusCode();
        if($status === 200){
            $json = json_decode($res->getBody());
            if($json->ok){
                $content = $json->data;
                echo strlen($content).PHP_EOL;
                fwrite($fp,$content."\n");
                $goNext = true;
            }else{
                echo 'all done';
            }
        }else{
            echo 'error:'.$status;
        }
        $currPage++;
    } while ($goNext);
    
fclose($fp);
echo 'all done filename='.$filename;


