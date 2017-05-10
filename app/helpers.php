<?php
/**
 * Created by PhpStorm.
 * User: Jh
 * Date: 2017/5/9
 * Time: 15:39
 */
/**
 * 数组转换对象
 *
 * @param $e 数组
 * @return object|void
 */
function arrayToObject($e)
{

    if (gettype($e) != 'array') return;
    foreach ($e as $k => $v) {
        if (gettype($v) == 'array' || getType($v) == 'object')
            $e[$k] = (object)$this->arrayToObject($v);
    }
    return (object)$e;
}

/**
 * 对象转换数组
 *
 * @param $e StdClass对象实例
 * @return array|void
 */
function objectToArray($e)
{
    $e = (array)$e;
    foreach ($e as $k => $v) {
        if (gettype($v) == 'resource') return;
        if (gettype($v) == 'object' || gettype($v) == 'array')
            $e[$k] = (array)$this->objectToArray($v);
    }
    return $e;
}