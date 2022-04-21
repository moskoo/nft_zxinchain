<?php
/*
 * This file is part of the moshong/nft_zxinchainn.
 * Tencent Zhixin Chain NFT Platform Interface SDK.
 *
 * (c) moshong <9080@live.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace NftZxinchainn\Tools\Support;

use NftZxinchainn\Tools\Exceptions\RuntimeException;

/**
 * Class Str.
 */
class Str
{
    /**
     * snake生成的变量的缓存
     *
     * @var array
     */
    protected static $snakeCache = [];

    /**
     * camel生成的变量的缓存
     *
     * @var array
     */
    protected static $camelCache = [];

    /**
     * studly生成的变量的缓存
     *
     * @var array
     */
    protected static $studlyCache = [];

    /**
     * 将值转换为驼峰式大小写。
     *
     * @param string $value
     *
     * @return string
     */
    public static function camel($value)
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * 生成更确切的“随机”的字母数字字符串。
     *
     * @param int $length
     *
     * @return string
     *
     * @throws \NftZxinchainn\Tools\Exceptions\RuntimeException
     */
    public static function random($length = 16)
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = static::randomBytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * 生成更确切的“随机”字符。
     *
     * @param int $length
     *
     * @return string
     *
     * @throws RuntimeException
     *
     * @codeCoverageIgnore
     *
     * @throws \Exception
     */
    public static function randomBytes($length = 16)
    {
        if (function_exists('random_bytes')) {
            $bytes = random_bytes($length);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length, $strong);
            if (false === $bytes || false === $strong) {
                throw new RuntimeException('无法生成随机字符串。');
            }
        } else {
            throw new RuntimeException('PHP5版本需要安装OpenSSL扩展');
        }

        return $bytes;
    }

    /**
     * 生成一个“随机”字母数字字符串。
     *
     * 不推荐用它来生成密码，不够安全
     *
     * @param int $length
     *
     * @return string
     */
    public static function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    /**
     * 将给定的字符串转换为大写。
     *
     * @param string $value
     *
     * @return string
     */
    public static function upper($value)
    {
        return mb_strtoupper($value);
    }

    /**
     * 将给定的字符串转换为标题字母。
     *
     * @param string $value
     *
     * @return string
     */
    public static function title($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * 将字符串转换为_连接值。
     *
     * @param string $value
     * @param string $delimiter
     *
     * @return string
     */
    public static function snake($value, $delimiter = '_')
    {
        $key = $value.$delimiter;

        if (isset(static::$snakeCache[$key])) {
            return static::$snakeCache[$key];
        }

        if (!ctype_lower($value)) {
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1'.$delimiter, $value));
        }

        return static::$snakeCache[$key] = trim($value, '_');
    }

    /**
     * 将值转换为大小写
     *
     * @param string $value
     *
     * @return string
     */
    public static function studly($value)
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }
}
