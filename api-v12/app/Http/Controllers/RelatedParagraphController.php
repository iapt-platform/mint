<?php
/*
 *查询相关联的书
 *mula->attakhata->tika
 *算法：
 *在原始的html 文件里 如 s0404m1.mul.htm 有 <a name="para2_an8"></a>
 * 在 so404a.att.htm 里也有 </a><a name="para2_an8"></a>
 * 这说明这两个段落是关联段落，para2是段落编号 an8是书名只要书名一样，段落编号一样。
 * 两个就是关联段落
 *
 * 表名：cs6_para
 * 所以数据库结构是
 * book 书号 1-217
 * para 段落号
 * bookid
 * cspara 上述段落号
 * book_name 上述书名
 *
 * 输入 book para
 * 查询书名和段落号
 * 输入这个书名和段落号
 * 查询有多少段落有一样的书名和段落号
 * 有些book 里面有两本书。所以又加了一个bookid
 * 每个bookid代表一本真正的书。所以bookid 要比 book 多
 * bookid 是为了输出书名用的。不是为了查询相关段落
 *
 * 数据要求：
 * 制作时包含全部段落。做好后把没有相关段落的段落删掉？？
 *
 */
namespace App\Http\Controllers;

use App\Models\RelatedParagraph;
use Illuminate\Http\Request;
use App\Http\Resources\RelatedParagraphResource;

class RelatedParagraphController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $first = RelatedParagraph::where('book',$request->get('book'))
                                    ->where('para',$request->get('para'))
                                    ->where('cs_para','>',0)
                                    ->first();
        $result = RelatedParagraph::where('book_name',$first->book_name)
                                    ->where('cs_para',$first->cs_para)
                                    ->orderBy('book_id')
                                    ->orderBy('para')
                                    ->get();
        $books=[];
        foreach ($result as $value) {
            # 把段落整合成书。有几本书就有几条输出纪录
            if(!isset($books[$value->book_id])){
                $books[$value->book_id]['book'] = $value->book;
                $books[$value->book_id]['book_id'] = $value->book_id;
                $books[$value->book_id]['cs6_para'] = $value->cs_para;
            }
            $books[$value->book_id]['para'][]=$value->para;
        }
        return $this->ok(["rows"=>RelatedParagraphResource::collection($books),"count"=>count($books)]);
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
     * @param  \App\Models\RelatedParagraph  $relatedParagraph
     * @return \Illuminate\Http\Response
     */
    public function show(RelatedParagraph $relatedParagraph)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RelatedParagraph  $relatedParagraph
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RelatedParagraph $relatedParagraph)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RelatedParagraph  $relatedParagraph
     * @return \Illuminate\Http\Response
     */
    public function destroy(RelatedParagraph $relatedParagraph)
    {
        //
    }
}
