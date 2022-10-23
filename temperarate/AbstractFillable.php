<?php


namespace app\framework\base\fillable;

use app\framework\base\fillable\traits\ArrayAccessTrait;
use app\framework\base\fillable\traits\FillDataTrait;
use app\framework\base\fillable\traits\PropertyToArrayTrait;

/**
 * 可填充基类
 * 注意：没有默认值的属性，php7.0默认会给null值，如果是集合(数组)，最好给默认值空数组[]。
 *
 * @package app\module\purchase\lib\fillable
 */
abstract class AbstractFillable implements \ArrayAccess
{
    use ArrayAccessTrait, PropertyToArrayTrait, FillDataTrait;
}