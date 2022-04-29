<?php

namespace app\v1\excel\controller;


use app\v1\file\model\AttachmentModel;
use app\v1\file\model\ProjectModel;
use SendFile\SendFile;
use think\facade\Validate;
use think\Request;

class Index
{

    public $token;

    public function __construct()
    {
        header('Access-Control-Allow-Origin:*');
        $this->token = input('get.token');
        if (!$this->token) {
            $this->fail('token');
        }
    }

    public function index()
    {
        dump(config('aliyun.'));
    }

}
