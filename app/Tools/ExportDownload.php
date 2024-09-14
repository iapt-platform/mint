<?php
namespace App\Tools;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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

        $_format = 'md';
        $tplFile = resource_path("mustache/".$type.'/'.$_format."/main.".$_format);
        $tpl = file_get_contents($tplFile);
        $texContent = $m->render($tpl,$bookMeta);
        $tex[] = [
            'name' => 'main.'.$_format,
            'content' => $texContent
            ];
        foreach ($sections as $key => $section) {
            $tplFile = resource_path("mustache/".$type.'/'.$_format."/section.".$_format);
            $tpl = file_get_contents($tplFile);
            $texContent = $m->render($tpl,$section['body']);
            $tex[] = [
                'name'=>$section['name'],
                'content'=>$texContent
                ];
        }

        Log::debug('footnote start');
        //footnote
        $tplFile = resource_path("mustache/".$_format."/footnote.".$_format);
        if(isset($GLOBALS['note']) &&
            is_array($GLOBALS['note']) &&
            count($GLOBALS['note'])>0 &&
            file_exists($tplFile)){
            $tpl = file_get_contents($tplFile);
            $texContent = $m->render($tpl,['footnote'=>$GLOBALS['note']]);
            $tex[] = [
                'name'=>'footnote.'.$_format,
                'content'=>$texContent
                ];
        }
        Log::debug('footnote finished');

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
            default:
                $file = array();
                foreach ($tex as $key => $section) {
                    $file[] = $section['content'];
                }
                $fileDate = implode('',$file);
                break;
        }


        $dir = "tmp/export/{$type}/".$this->format."/";
        $mdFilename = $dir.$outputFilename.'.md';
        Storage::disk('local')->put($mdFilename, $fileDate);
        Log::debug('markdown saved',['filename'=>$mdFilename]);
        if($this->format === 'markdown'){
            $filename = $mdFilename;
        }else{
            $filename = $dir.$outputFilename.'.'.$this->format;

            Log::debug('tmp saved',['filename'=>$filename]);
            $absoluteMdPath = Storage::disk('local')->path($mdFilename);
            $absoluteOutputPath = Storage::disk('local')->path($filename);
            //$command = "pandoc pandoc1.md --reference-doc tpl.docx -o pandoc1.docx";
            $command = ['pandoc', $absoluteMdPath, '-o', $absoluteOutputPath];
            if($this->format === 'docx'){
                 $tplFile = resource_path("template/docx/paper.docx");
                 array_push($command,'--reference-doc');
                 array_push($command,$tplFile);
            }
            Log::debug('pandoc start',['command'=>$command,'format'=>$this->format]);
            $process = new Process($command);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            echo $process->getOutput();
            Log::debug('pandoc end',['command'=>$command]);
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
        $fileContent = Storage::disk('local')->get($filename);
        $zipOk = \App\Tools\Tools::zip($zipFile,[$realFilename=>$fileContent]);
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
        Log::debug('export chapter done, upload',['filename'=>$tmpFile,'url'=>$s3Link] );
        unlink($zipFile);
        return true;
    }
}
