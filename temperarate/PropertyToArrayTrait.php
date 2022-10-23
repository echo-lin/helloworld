<?php
namespace app\framework\base\fillable\traits;


trait PropertyToArrayTrait
{
    /** @var array 当前属性集合 */
    protected static $_properties = [];

    /**
     * 转化为数组形式, 未设置的值会忽略
     *
     * @return array
     */
    public function toArray(): array
    {
        return (array)json_decode(json_encode($this), true);
    }

    /**
     * 将当前对象转化成json
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this);
    }
}