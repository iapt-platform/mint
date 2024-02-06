<?php

namespace App\Http\Controllers;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use App\Models\Attachment;
use Illuminate\Http\Request;
use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;
use App\Http\Resources\AttachmentResource;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;
use FFMpeg\FFMpeg;


class AttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
		switch ($request->get('view')) {
            case 'studio':
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                //判断当前用户是否有指定的studio的权限
                if($user['user_uid'] !== StudioApi::getIdByName($request->get('studio'))){
                    return $this->error(__('auth.failed'));
                }
                $table = Attachment::where('owner_uid', $user["user_uid"]);
                break;
            default:
                return $this->error("error view",[],200);
            break;
        }
        if($request->has('search')){
            $table = $table->where('title', 'like', $request->get('search')."%");
        }
        if($request->has('content_type')){
            $table = $table->where('content_type', 'like', $request->get('content_type')."%");
        }
        $count = $table->count();
        $table = $table->orderBy($request->get('order','updated_at'),
                                 $request->get('dir','desc'));

        $table = $table->skip($request->get('offset',0))
                       ->take($request->get('limit',1000));

        $result = $table->get();

        return $this->ok(["rows"=>AttachmentResource::collection($result),"count"=>$count]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),401,401);
        }

        $request->validate([
            'file' => 'required',
        ]);

        $file = $request->file('file');
        $bucket = config('mint.attachments.bucket_name.permanent');
        $fileId = Str::uuid();
        $ext = $file->getClientOriginalExtension();


        if($request->get('type') === 'avatar'){
            $resize = Image::make($file)->fit(512);
            Storage::put($bucket.'/'.$fileId.'.jpg',$resize->stream());
            $resize = Image::make($file)->fit(256);
            Storage::put($bucket.'/'.$fileId.'_m.jpg',$resize->stream());
            $resize = Image::make($file)->fit(128);
            Storage::put($bucket.'/'.$fileId.'_s.jpg',$resize->stream());
            $name = $fileId.'.jpg';
        }else{
            //Move Uploaded File
            $name = $fileId.'.'.$ext;
            $filename = $file->storeAs($bucket,$name);
        }

        $attachment = new Attachment;
        $attachment->id = $fileId;
        $attachment->user_uid = $user['user_uid'];
        $attachment->bucket = $bucket;
        $attachment->name = $name;
        $attachment->filename = $file->getClientOriginalName();
        $path_parts = pathinfo($file->getClientOriginalName());
        $attachment->title = $path_parts['filename'];
        $attachment->size = $file->getSize();
        $attachment->content_type = $file->getMimeType();
        $attachment->status = 'public';
        if($request->has('studio')){
            $owner_uid = StudioApi::getIdByName($request->get('studio'));
        }else{
            $owner_uid = $user['user_uid'];
        }
        if($owner_uid){
            $attachment->owner_uid = $owner_uid;
        }
        $attachment->save();

        $type = explode('/',$file->getMimeType());
        switch ($type[0]) {
            case 'image':
                /*
                $resize = Image::make($file)->fit(128);
                Storage::disk('public')->put($bucket.'/'.$fileId.'_s.jpg',$resize->stream());
                $resize = Image::make($file)->fit(256);
                Storage::disk('public')->put($bucket.'/'.$fileId.'_m.jpg',$resize->stream());
                $resize = Image::make($file)->fit(512);
                Storage::disk('public')->put($bucket.'/'.$fileId.'_l.jpg',$resize->stream());
                */
                break;
            case 'video':
                //$path = public_path($filename);
                //$ffmpeg = FFMpeg::create();
                //$video = $ffmpeg->open(public_path($filename));
                //$frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(1));
                //$frame->save('image.jpg');
                break;
            default:
                # code...
                break;
        }
        /*
        $json = array(
            'name' => $filename,
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
            'url' => Storage::url($bucket.'/'.$name),
            'uid' => $attachment->id,
            );*/
        return $this->ok(new AttachmentResource($attachment));

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attachment  $attachment
     * @return \Illuminate\Http\Response
     */
    public function show(Attachment $attachment)
    {
        //
        return $this->ok(new AttachmentResource($attachment));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Attachment  $attachment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Attachment $attachment)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),401,401);
        }

        $attachment->title = $request->get('title');
        $attachment->save();
        return $this->ok(new AttachmentResource($attachment));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,string $id)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),401,401);
        }
        if(Str::isUuid($id)){
            $res = Attachment::where('id',$id)->first();
        }else{
            /**
             * 从文件名获取bucket和name
             */
            $pos = mb_strrpos($request->get('name'),'/',0,"UTF-8");
            if($pos === false){
                return $this->error('无效的文件名',500,500);
            }
            $bucket = mb_substr($request->get('name'),0,$pos,'UTF-8');
            $name = mb_substr($request->get('name'),$pos+1,NULL,'UTF-8');
            $res = Attachment::where('bucket',$bucket)
                            ->where('name',$name)
                            ->first();
        }
        if(!$res){
            return $this->error('no res');
        }
        if($user['user_uid'] !== $res->user_uid){
            return $this->error(__('auth.failed'),403,403);
        }

        //删除文件
        $filename = $res->bucket . '/' . $res->name;
        $path_parts = pathinfo($res->name);
        Storage::delete($filename);
        Storage::delete($res->bucket.'/'.$path_parts['filename'].'_m.jpg');
        Storage::delete($res->bucket.'/'.$path_parts['filename'].'_s.jpg');

        $del = $res->delete();
        return $this->ok($del);
    }
}
