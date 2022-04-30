<?php

namespace app\v1\file\controller;


use app\v1\file\model\AttachmentModel;
use app\v1\project\model\ProjectModel;
use SendFile\SendFile;
use think\facade\Validate;
use think\Request;

class index
{

    public $token;

    public function __construct()
    {
        header('Access-Control-Allow-Origin:*');
        $this->token = input('get.token');
        if (!$this->token) {
            \Ret::fail('token');
        }
    }

    public function index()
    {
        dump(config('aliyun.'));
    }

    public function upload_file(Request $request, $full = 0, $ue = 0)
    {
        $token = $this->token;
        $proc = ProjectModel::api_find_token($token);
        if (!$proc) {
            \Ret::fail('项目不可用');
        }

        $file = $request->file('file');
        if (!$file) {
            \Ret::fail('file字段没有用文件提交');
        }
        $hash = $file->hash('md5');
        // 判断附件格式是否符合
        $file_name = $file->getFilename();
        if ($file_exists = AttachmentModel::where(['token' => $token, 'md5' => $file->hash('md5')])->find()) {
            $sav = ($full ? $proc['url'] . '/' : '') . $file_exists['path'];
            // 附件已存在
            return \Ret::succ($sav);
        }
        if (!Validate::fileExt($file, $proc['ext'])) {
            \Ret::fail("ext not allow");
            return;
        }
        if (!Validate::fileSize($file, (float)$proc['size'] * 1024)) {
            \Ret::fail("size too big");
            return;
        }

        $savePath = date('Ymd', time());

        $info = $file->move('upload/' . $this->token . "/" . $savePath, $hash . "." . $file->getOriginalExtension());
        $fileName = $this->token . "/" . $savePath . "/" . $info->getFilename();
        $file_info = [
            'token' => $token,
            'name' => $file->getOriginalName(),
            'mime' => $file->getOriginalMime(),
            'path' => $fileName,
            'ext' => $file->getOriginalExtension(),
            'size' => $info->getSize(),
            'md5' => $info->md5(),
        ];
        if ($proc["type"] == "local" || $proc["type"] == "all") {
            if ($proc['main_type'] == 'local') {
                $sav = ($full ? $proc['url'] . '/' : '') . $fileName;
            }
        }

        if ($proc["type"] == "remote" || $proc["type"] == "all") {
            $sf = new SendFile();
            $ret = $sf->send('http://' . $proc["endpoint"] . '/up?token=' . $proc["bucket"], realpath('upload/' . $fileName), $file->getType(), $file->getFilename());
            $json = json_decode($ret, 1);
            $sav = ($full ? $proc['url'] . '/' : '') . $json["data"];
        }
        if ($proc["type"] == "oss" || $proc["type"] == "all") {
            $oss = new \OSS\AliyunOSS($proc);
            $oss->uploadFile($proc['bucket'], $fileName, $info->getPathname());
            if ($proc['main_type'] == 'oss') {
                $sav = ($full ? $proc['url'] . '/' : '') . $fileName;
            }
            if ($proc["type"] != "all") {
                unlink($info->getPathname());
            }
        }

        AttachmentModel::create($file_info);
        if ($info) {
            if ($ue) {
                \Ret::succ(['src' => $sav]);
            } else {
                \Ret::succ($sav);
            }
        } else {
            \Ret::fail($file->getError());
        }
    }

    public function up(Request $request)
    {
        $file = $request->file('file');
        if ($file) {
            return $this->upload_file($request);
        } else {
            return $this->upload_base64($request);
        }
    }

    public function upfull(Request $request)
    {
        $file = $request->file('file');
        if ($file) {
            return $this->upload_file($request, 1);
        } else {
            return $this->upload_base64($request, 1);
        }
    }

    public function up_ue(Request $request)
    {
        $file = $request->file('file');
        if ($file) {
            return $this->upload_file($request, 1, 1);
        } else {
            return $this->upload_base64($request, 1, 1);
        }
    }

    public function upload_base64(Request $request, $full = 0, $ue = 0)
    {
        $token = $this->token;
        if (!$request->has('file')) {
            \Ret::fail('需要file字段提交base64');
        }
        $image = input('post.file');
        if (!$image) {
            return [
                'code' => 404,
                'data' => '没有找到文件'
            ];
        }
        $savePath = date('Ymd', time()) . '/';
        $file_name = md5(time() . microtime());

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $image, $result)) {
            $proc = ProjectModel::api_find_token($token);
            if (!$proc) {
                \Ret::fail('项目不可用');
            }
            $ext = explode(',', $proc['ext']);
            $type = $result[2];
            if (!in_array($type, $ext)) {
                $_message['message'] = '仅允许:' . $proc['ext'];
                return $_message;
            }
            $pic_path = 'upload/' . $savePath;
            $file_path = $pic_path . $file_name . "." . $type;
            if (!file_exists($pic_path)) {
                mkdir($pic_path);
            }
            $file_size = file_put_contents($file_path, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image)));
            if (!$file_path || $file_size > 10 * 1024 * 1024) {
                unlink($pic_path);
                return [
                    'code' => 500,
                    'data' => '图片保存失败'
                ];
            }
            $md5 = md5_file($file_path);

            if ($file_exists = AttachmentModel::where(['md5' => $md5])->find()) {
                $sav = ($full ? $proc['url'] . '/' : '') . $file_exists['path'];
                // 附件已存在
                return \Ret::succ($sav);
            }

            $fileName = $proc['name'] . '/' . $savePath . $file_name . "." . $type;

            if ($proc["type"] == "local" || $proc["type"] == "all") {
                if ($proc['main_type'] == 'local') {
                    $sav = ($full ? $proc['url'] . '/' : '') . $fileName;
                }
            }
            if ($proc["type"] == "oss" || $proc["type"] == "all") {
                $oss = new \OSS\AliyunOSS($proc);
                $oss->uploadFile($proc['bucket'], $fileName, $file_path);
                if ($proc['main_type'] == 'oss') {
                    $sav = ($full ? $proc['url'] . '/' : '') . $fileName;
                }
                unlink($file_path);
            }

            $file_info = [
                'token' => $token,
                'name' => $file_name,
                'mime' => $type,
                'path' => $fileName,
                'ext' => $ext,
                'size' => $file_size,
                'md5' => $md5,
            ];
            AttachmentModel::create($file_info);

            if ($ue) {
                \Ret::succ(['src' => $sav]);
            } else {
                \Ret::succ($sav);
            }
        } else {
            return [
                'code' => 507,
                'data' => '图片格式编码错误'
            ];
        }
    }


}
