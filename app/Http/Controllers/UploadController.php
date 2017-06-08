<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use Storage;
use Uuid;

class UploadController extends Controller
{
    /**
     * 七牛云上传图片接口
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $fileContents = $request->file('file');
        $folder = $request->folder;
//        $filename = $fileContents->getClientOriginalName();
//        $mimetype = $fileContents->getMimeType();
        $extension = $fileContents->getClientOriginalExtension();
        $_uuid = Uuid::generate(1);
        $filename = $_uuid->string.'.'.$extension;
        $disk = Storage::disk('qiniu');
        $status = $disk->put($folder.'/'.$filename, file_get_contents($fileContents));
        if($status){
            return array('status'=>1,'imgurl'=>"http://upload.8dsun.com/Equipment/$filename");
        }else{
            return array('status'=>0);
        }
    }

}
