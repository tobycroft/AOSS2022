<?php

namespace app\v1\excel\controller;


use app\v1\project\model\ProjectModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use think\facade\Request;
use think\facade\Validate;

class index
{

    public $token;

    public function __construct()
    {
        header('Access-Control-Allow-Origin:*');
        $this->token = input('get.token');
        if (!$this->token) {
            \Ret::fail('token');
            return;
        }
    }

    public function index()
    {
        $token = $this->token;
        $proc = ProjectModel::api_find_token($token);
        if (!$proc) {
            \Ret::fail('项目不可用');
            return;
        }
        $file = Request::file("file");
        if (!$file) {
            \Ret::fail('file字段没有用文件提交');
            return;
        }
        $hash = $file->hash('md5');
        if (!Validate::fileExt($file, ["xls", "xlsx"])) {
            \Ret::fail("ext not allow");
            return;
        }
        if (!Validate::fileSize($file, (float)8192 * 1024)) {
            \Ret::fail("size too big");
            return;
        }

        $info = $file->move('./upload/excel', $hash . "." . $file->getOriginalExtension());
        $reader = IOFactory::load($info->getPathname());
        unlink($info->getPathname());
        $datas = $reader->getActiveSheet()->toArray();
        if (count($datas) < 2) {
            \Ret::fail("表格长度不足");
            return;
        }
        $value = [];
        $i = 0;
        $keys = $datas[0];
        foreach ($keys as $key) {
            if (empty($key)) {
                \Ret::fail("表格长度不一");
                return;
            }
        }
        $count_column = count($keys);
        $colums = [];
        for ($i = 1; $i < count($datas); $i++) {
            $line = $datas[$i];
            for ($s = 0; $s < $count_column; $s++) {
                $arr[$keys[$s]] = $line[$s];
            }
            $colums[] = $arr;
        }
        echo json_encode($colums);
    }

}
