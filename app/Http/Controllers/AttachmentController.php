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
    public function index()
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
                $table = Attachment::where('studio_id', $user["user_uid"]);
                break;
            default:
                return $this->error("error view",[],200);
            break;
        }
        if($request->has('search')){
            $table = $table->where('title', 'like', $request->get('search')."%");
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
            return $this->error(__('auth.failed'),[],401);
        }

        $request->validate([
            'file' => 'required',
        ]);
        $file = $request->file('file');

       //Move Uploaded File
        $bucket = config('mint.attachments.bucket_name.permanent');
        $fileId = Str::uuid();
        $ext = $file->getClientOriginalExtension();
        $name = $fileId.'.'.$ext;
        $filename = $file->storeAs($bucket,$name);
        $attachment = new Attachment;
        $attachment->id = $fileId;
        $attachment->user_uid = $user['user_uid'];
        $attachment->bucket = $bucket;
        $attachment->name = $name;
        $attachment->title = $file->getClientOriginalName();
        $attachment->size = $file->getSize();
        $attachment->content_type = $file->getMimeType();
        $attachment->status = 'public';
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
        $json = array(
            'name' => $filename,
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
            'url' => Storage::url($bucket.'/'.$name),
            'uid' => $attachment->id,
            );
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Attachment  $attachment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attachment $attachment)
    {
        //
    }
}
