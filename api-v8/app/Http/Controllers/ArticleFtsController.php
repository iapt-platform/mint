<?php
/**
 * 文章全文搜索
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ArticleCollection;
use App\Models\Article;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class ArticleFtsController extends Controller
{
    /**
     * Display a listing of the resource.
     * http://127.0.0.1:8000/api/v2/article-fts?id=df6c6609-6fc1-42d0-9ef1-535ef3e702c9&anthology=697c9169-cb9d-4a60-8848-92745e467bab&channesl=7fea264d-7a26-40f8-bef7-bc95102760fb
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $pageSize = 10;
        $pageCurrent = $request->get('from',0);

        $articlesId = [];
        if(!empty($request->get('anthology'))){
            //子节点
            $node = ArticleCollection::where('article_id',$request->get('id'))
                        ->where('collect_id',$request->get('anthology'))->first();
            if($node){
                $nodeList = ArticleCollection::where('collect_id',$request->get('anthology'))
                                ->where('id','>=',(int)$node->id)
                                ->orderBy('id')
                                ->skip($request->get('from',0))
                                ->get();
                $result = [];
                $count = 0;
                foreach ($nodeList as $curr) {
                    if($count>0 && $curr->level <= $node->level){
                        break;
                    }
                    $result[] = $curr;
                }
                foreach ($result as $key => $value) {
                    $articlesId[] = $value->article_id;
                }
            }
        }else{
            $articlesId[] = $request->get('id');
        }
        $total = count($articlesId);
        $channels = explode(',',$request->get('channels'));
        $output = [];
        for ($i=$pageCurrent; $i <$pageCurrent+$pageSize ; $i++) {
            if($i>=$total){
                break;
            }
            $curr = $articlesId[$i];
            foreach ($channels as $channel) {
                # code...
                $article = $this->fetch($curr,$channel);
                if ($article === false) {
                    Log::error('fetch fail');
                }else{
                    # code...
                    $content = $article['html'];
                    if(!empty($request->get('key'))){
                        if(strpos($content,$request->get('key')) !== false){
                            $output[] = $article;
                        }
                    }
                }
            }
        }

        return $this->ok(['rows'=>$output,
            'page'=>[
                'size' => $pageSize,
                'current' => $pageCurrent,
                'total' => $total
            ],]);
    }

    private function fetch($articleId,$channel,$token=null){
        try {
            $api = config('mint.server.api.bamboo');
            $basicUrl = $api . '/v2/article/';
            $url =  $basicUrl . $articleId;;

            $urlParam = [
                    'mode' => 'read',
                    'format' => 'text',
                    'channel' => $channel,
            ];
            Log::debug('http request',['url'=>$url,'param'=>$urlParam]);
            if($token){
                $response = Http::withToken($this->option('token'))->get($url,$urlParam);
            }else{
                $response = Http::get($url,$urlParam);
            }

            if($response->failed()){
                Log::error('http request error'.$response->json('message'));
                return false;
            }
            if(!$response->json('ok')){
                return false;
            }
            $article = $response->json('data');
            return $article;
        }catch (\Throwable $th) {
            // 处理请求过程中抛出的异常
            Log::error('fetch',['error'=>$th]);
            return false;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
