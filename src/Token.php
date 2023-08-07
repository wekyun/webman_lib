<?php
/**
 * Created by PhpStorm.
 * UserValidate: hg
 */

namespace Wekyun\WebmanLib;

use Wekyun\WebmanLib\common\exception\CheckException;

/**
 * Token操作
 * Class Token
 */
class Token
{
    /**
     * OPENSSL key
     * @var string
     */
    private static $key = 'd5vp782flPgdktyFG76oH2DOMKJe8245';

    /**
     * OPENSSL iv
     * @var string
     */
    private static $iv = '8NONw5a9PQiysWpM';

    /**
     * OPENSSL 加密方法
     * @var string
     */
    private static $method = 'AES-256-CBC';

    /**
     * OPENSSL 初始化配置
     * @param string $key
     * @param string $iv
     */
    public static function init(string $key, string $iv)
    {
        self::$key = $key;
        self::$iv = $iv;
    }

    /**
     * 生成Token
     * @param array $data 额外数据
     * @param int $gqt 有效期天
     * @return string
     */
    public static function make(array $data, int $gqt = 90): string
    {
        $time = time();
        $info['data'] = $data;
        $info['qft'] = $time;
        $info['gqt'] = $time + ($gqt * 86400);
        return self::encrypt(json_encode($info));
    }


    /**
     * 解析token字符串
     * @param string $token
     * @return array|bool
     */
    public static function read(string $token): array|bool
    {
        $time = time();
        $data = json_decode(self::decrypt($token), true);
        if ($data && $data['gqt'] > $time && $time >= $data['qft']) {
            return $data;
        }
        return false;
    }

    /**
     * OPENSSL 加密
     * @param string $val 需加密字符串
     * @return string
     */
    private static function encrypt(string $val): string
    {
        $encrypt = openssl_encrypt($val, self::$method, self::$key, OPENSSL_RAW_DATA, self::$iv);
        return base64_encode($encrypt);
    }

    /**
     * OPENSSL 解密
     * @param string $val 需解密的字符串
     * @return string
     */
    private static function decrypt(string $val): string
    {
        return openssl_decrypt(
            base64_decode($val),
            self::$method,
            self::$key,
            OPENSSL_RAW_DATA,
            self::$iv
        );
    }

}