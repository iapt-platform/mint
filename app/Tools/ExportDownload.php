<?php
namespace App\Tools;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;

use App\Tools\RedisClusters;
use App\Tools\Export;

class ExportDownload
{
    protected $statusKey = 'export/status';
    protected $statusExpiry = 3600;
    protected $currStatusKey = '';

    protected $queryId = 'id';
    protected $realFilename = 'index';//压缩包里的文件名
    protected $zipFilename = 'file.zip';
    protected $downloadUrl = null;

    protected $format = 'tex';
    protected $debug = false;

    protected $logs = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($options=[])
    {
        if(isset($options['format'])){
            $this->format = $options['format'];
        }
        if(isset($options['debug'])){
            $this->debug = $options['debug'];
        }
        if(isset($options['filename'])){
            $this->realFilename = $options['filename'];
        }
        $this->queryId = $options['queryId'];
        $this->zipFilename = $this->queryId.'.zip';

        $this->currStatusKey = $this->statusKey . '/' . $this->queryId;
    }

    /**
     * progress: 0-1, error -1
     * message: string
     */
    public function setStatus($progress,$message=''){
        $this->logs[] = $message;
        $data = [
                    'progress'=>$progress,
                    'message'=>$message,
                    'log'=>$this->logs,
                    'content-type'=>'application/zip',
                ];
        if($this->downloadUrl){
            $data['url'] = $this->downloadUrl;
        }
        RedisClusters::put($this->currStatusKey,
                           $data,
                           $this->statusExpiry);
        $percent = (int)($progress * 100);
        return "[{$percent}%]".$message;
    }


    public function getStatus(){
        return RedisClusters::get($this->currStatusKey);
    }

    public function upload(string $type,$sections,$bookMeta){
        $outputFilename = Str::uuid();

        $m = new \Mustache_Engine(array('entity_flags'=>ENT_QUOTES,
                    'delimiters' => '[[ ]]',
                    'escape'=>function ($value){
                        return $value;
                    }));

        $tex = array();

        $tplFile = resource_path("mustache/".$type.'/'.$this->format."/main.".$this->format);
        $tpl = file_get_contents($tplFile);
        $texContent = $m->render($tpl,$bookMeta);
        $tex[] = [
            'name' => 'main.'.$this->format,
            'content' => $texContent
            ];
        foreach ($sections as $key => $section) {
            $tplFile = resource_path("mustache/".$type.'/'.$this->format."/section.".$this->format);
            $tpl = file_get_contents($tplFile);
            $texContent = $m->render($tpl,$section['body']);
            $tex[] = [
                'name'=>$section['name'],
                'content'=>$texContent
                ];
        }

        Log::debug('footnote start');
        //footnote
        $tplFile = resource_path("mustache/".$this->format."/footnote.".$this->format);
        if(isset($GLOBALS['note']) &&
            is_array($GLOBALS['note']) &&
            count($GLOBALS['note'])>0 &&
            file_exists($tplFile)){
            $tpl = file_get_contents($tplFile);
            $texContent = $m->render($tpl,['footnote'=>$GLOBALS['note']]);
            $tex[] = [
                'name'=>'footnote.'.$this->format,
                'content'=>$texContent
                ];
        }
        Log::debug('footnote finished');

        if($this->debug){
            $dir = "export/{$type}/".$this->format."/".$this->zipFilename."/";
            $filename = $dir.$outputFilename.'.html';
            Log::debug('save',['filename'=>$filename]);
            foreach ($tex as $key => $section) {
                Storage::disk('local')->append($filename, $section['content']);
            }
        }


        $this->setStatus(0.95,'export content done. tex count='.count($tex));
        Log::debug('export content done.',['tex_count'=>count($tex)]);

        //upload
        $fileDate = '';
        switch ($this->format) {
            case 'tex':
                $data = Export::ToPdf($tex);
                if($data['ok']){
                    $this->info($data['content-type']);
                    $fileDate = $data['data'];
                }else{
                    $this->error($data['code'].'-'.$data['message']);
                }
                break;
            case 'html':
                $file = array();
                foreach ($tex as $key => $section) {
                    $file[] = $section['content'];
                }
                $fileDate = implode('',$file);
                break;
        }


        $zipDir = storage_path('app/export/zip');
        if(!is_dir($zipDir)){
            $res = mkdir($zipDir,0755,true);
            if(!$res){
                Log::error('mkdir fail path='.$zipDir);
                return 1;
            }
        }

        $zipFile = $zipDir.'/'. $outputFilename .'.zip';

        Log::debug('export chapter start zip  file='.$zipFile);
        //zip压缩包里面的文件名
        $realFilename = $this->realFilename.".".$this->format;

        $zipOk = \App\Tools\Tools::zip($zipFile,[$realFilename=>$fileDate]);
        if(!$zipOk){
            Log::error('export chapter zip fail zip file='.$zipFile);
            $this->setStatus(0.99,'export chapter zip fail');
            $this->error('export chapter zip fail zip file='.$zipFile);
            //TODO 给客户端返回错误状态
            return 1;
        }
        $this->setStatus(0.96,'export chapter zip success');

        $bucket = config('mint.attachments.bucket_name.temporary');
        $tmpFile =  $bucket.'/'. $this->zipFilename ;
        Log::debug('upload start filename='.$tmpFile);
        $this->setStatus(0.97,'upload start ');
        $zipData = file_get_contents($zipFile);
        Storage::put($tmpFile, $zipData);

        if (App::environment('local')) {
            $s3Link = Storage::url($tmpFile);
        }else{
            try{
                $s3Link = Storage::temporaryUrl($tmpFile, now()->addDays(7));
            }catch(\Exception $e){
                Log::error('export {Exception}',['exception'=>$e]);
                return false;
            }
        }
        $this->downloadUrl = $s3Link;
        $this->setStatus(1,'export chapter done');
        Log::debug('export chapter done, upload filename='.$tmpFile);
        unlink($zipFile);
        return true;
    }
}
