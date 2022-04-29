<?php

namespace app\v1\excel\controller;


use PhpOffice\PhpSpreadsheet\IOFactory;
use think\facade\Request;
use think\facade\Validate;

class Index
{

    public $token;

    public function index()
    {
        $file = Request::file("file");
        if (!$file) {
            \Ret::fail('file字段没有用文件提交');
        }
        $hash = $file->hash('md5');
        if (!Validate::fileExt($file, ["xls", "xlsx"])) {
            \Ret::fail("ext not allow");
            return;
        }
        if (!Validate::fileSize($file, (float)4 * 1024)) {
            \Ret::fail("size too big");
            return;
        }

        $info = $file->move('./upload/' . $this->token, $hash . "." . $file->getOriginalExtension());
        $reader = IOFactory::load($info->getPathname());

    }

}
