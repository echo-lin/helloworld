<?php
/**
 * @author    吴宇
 * @principal 陈龙宝
 * @date      2021/11/17
 * @time      14:44
 */

namespace app\framework\exception;


/**
 * 类型错误
 *
 * @package app\framework\exception
 */
class TypeErrorException extends \think\Exception
{
    //方便兼容老代码特殊场景使用
    const RefactorErrorCode = 0;

    /**
     * @param     $msg
     * @param int $code
     *
     * @throws static
     */
    public static function throw($msg, $code = self::RefactorErrorCode)
    {
        throw new static($msg, $code);
    }
}