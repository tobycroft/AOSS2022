<?php

class Ret
{
    public static function succ($data = '成功', $code = 0)
    {
        echo json_encode([
            'code' => $code,
            'data' => $data,
        ], 320);
        exit(0);
    }

    public static function fail($data = '失败', $code = 400)
    {
        self::succ($data, $code);
    }

}