<?php
/**
 * @author    吴宇
 * @principal 陈龙宝
 * @date      2021/11/17
 * @time      14:41
 */

namespace app\framework\base\fillable\traits;

use app\framework\base\fillable\PropertyDocParser;
use app\framework\exception\TypeErrorException;

trait FillDataTrait
{
    /**
     * 将数组配置注入实体对象
     *
     * @param null $data
     *
     * @throws \app\framework\exception\TypeErrorException
     * @throws \think\Exception
     */
    public function __construct($data = null)
    {
        $data && $this->fill($data);
    }

    /**
     * 将数组配置注入实体对象
     *
     * @param $data
     *
     * @return $this
     * @throws \app\framework\exception\TypeErrorException
     * @throws \think\Exception
     */
    public function fill($data)
    {
        if (!is_array($data) && !is_object($data)) {
            throw new \TypeError('填充数据错误');
        }
        $this->fillTypeData($data);

        return $this;
    }


    /**
     * 填充数据
     *
     * @param $data
     *
     * @throws \app\framework\exception\TypeErrorException
     * @throws \think\Exception
     */
    protected function fillTypeData($data)
    {
        $this->beforeFill($data);
        $parser = new PropertyDocParser($this);
        $parser->fillTypeData($data);
        $this->afterFill();
    }

    /**
     * 填充前操作
     */
    protected function beforeFill(&$data)
    {

    }

    /**
     * 填充后操作
     */
    protected function afterFill()
    {

    }

    /**
     * 将对象数组转为当前对象数组
     *
     * @param $data
     *
     * @return static
     * @throws TypeErrorException
     * @throws \think\Exception
     */
    public static function fromItem($data): self
    {
        return new static($data);
    }

    /**
     * 将多维数组转为当前对象数组
     *
     * @param array $list
     * @param bool $remainOriginalKey 是否保留原始的key
     * @return static[]
     * @throws \app\framework\exception\TypeErrorException
     * @throws \think\Exception
     */
    public static function fromList(array $list, bool $remainOriginalKey = false): array
    {
        $data = [];
        foreach ($list as $key => $item) {
            if ($remainOriginalKey) {
                $data[$key] = new static($item);
            } else {
                $data[] = new static($item);
            }
        }
        return $data;
    }
}