<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\v1\file\model;

use think\facade\Db;
use think\Model;

class ProjectModel extends Model
{

    protected $table = 'ao_project';


    public function api_find($id)
    {
        $db = Db::table($this->table);
        $where = [
            'id' => $id,
        ];
        $db->where($where);
        return $db->find();
    }

    public function api_find_token($token)
    {
        $db = Db::table($this->table);
        $where = [
            'token' => $token,
            'status' => 1,
        ];
        $db->where($where);
        return $db->find();
    }

}
