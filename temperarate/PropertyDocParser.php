<?php

namespace app\framework\base\fillable;

use app\framework\exception\TypeErrorException;
use app\framework\library\ExceptionHelper;

class PropertyDocParser
{
    /**
     * 文档属性类型
     *
     * @var array
     */
    protected static $propertyTypes = [];

    /** @var object 要设置的类 */
    protected $class;

    /** @var string 类名 */
    protected $className;

    public function __construct($class)
    {
        $this->class = $class;
        $this->className = get_class($class);
    }

    /**
     * 根据属性类型填充数据
     *
     * @param array $data
     *
     * @throws \app\framework\exception\TypeErrorException
     * @throws \think\Exception
     */
    public function fillTypeData($data = [])
    {
        if (!$data) {
            return;
        }
        if ($data instanceof \think\Model) {
            $data = $data->toArray();
        }
        $propertyTypes = self::getPropertyTypes();
        foreach ($data as $propertyName => $propertyValue) {
            // 不在类属性里的忽略
            if (!array_key_exists($propertyName, $propertyTypes)) {
                continue;
            }
            try {
                $this->setPropertyValue($propertyValue, $propertyName, $propertyTypes[$propertyName]);
            } catch (\Throwable $e) {
                if ($e instanceof TypeErrorException) {
                    throw ExceptionHelper::setExceptionMessage($e, $propertyName . '.' . $e->getMessage());
                }
                TypeErrorException::throw("{$propertyName}类型错误");
            }
        }
    }

    /**
     * 设置类的属性
     *
     * @param                                                $propertyValue
     * @param string                                         $propertyName
     * @param \app\framework\base\fillable\PropertyType      $propertyType
     *
     * @throws \ReflectionException
     * @throws \app\framework\exception\TypeErrorException
     */
    protected function setPropertyValue($propertyValue, string $propertyName, PropertyType $propertyType)
    {
        $reflectionProperty = new \ReflectionProperty($this->class, $propertyName);
        if (!$reflectionProperty->isPublic()) {
            return;
        }
        if ($propertyType->is_collection) {
            if (!is_array($propertyValue)) {
                TypeErrorException::throw("{$propertyName}类型错误");
            }
            $values = [];
            foreach ($propertyValue as $item) {
                if ($propertyType->is_class) {
                    $values[] = $this->newInstance($propertyType->type, $item);
                } else {
                    $values[] = $this->transform($item, $propertyType->type);
                }
            }
            $reflectionProperty->setValue($this->class, $values);
        } else {
            $value = null;
            if ($propertyType->is_class) {
                $value = $this->newInstance($propertyType->type, $propertyValue);
            } else {
                $value = $this->transform($propertyValue, $propertyType->type);
            }
            // 值不是null的才设置值，避免默认值被覆盖
            if (!is_null($value)) {
                $reflectionProperty->setValue($this->class, $value);
            }
        }
    }

    /**
     * 转化字段类型
     *
     * @param        $value
     * @param string $type
     *
     * @return array|bool|float|int|string
     * @throws \app\framework\exception\TypeErrorException
     */
    private function transform($value, string $type)
    {
        if (is_null($value)) {
            return null;
        }
        switch ($type) {
            case 'string':
                if (!is_string($value) && !is_int($value) && !is_float($value)) {
                    throw new \TypeError("类型转化错误");
                }
                return (string)$value;
            case 'int':
                if (!preg_match('/^\-?\d+$/', $value)) {
                    throw new \TypeError("类型转化错误");
                }
                return (int)$value;
            case 'bool':
                if (!is_bool($value) && !is_numeric($value) && !is_string($value)) {
                    throw new \TypeError("类型转化错误");
                }
                return (bool)$value;
            case 'float':
                if (!is_numeric($value)) {
                    throw new \TypeError("类型转化错误");
                }
                return (float)$value;
            case 'array':
                if (!is_array($value)) {
                    throw new \TypeError("类型转化错误");
                }
                return (array)$value;
            default:
                throw new \TypeError("未知的类型");
        }
    }

    /**
     * @param string $className
     * @param        $params
     *
     * @return mixed
     */
    private function newInstance(string $className, $params)
    {
        return new $className($params);
    }

    /**
     * 获取对象文档属性类型
     *
     * @return \app\framework\base\fillable\PropertyType[]
     */
    public function getPropertyTypes(): array
    {
        $classPropertyTypes = &self::$propertyTypes[$this->className];
        if (!empty($classPropertyTypes)) {
            return $classPropertyTypes;
        }
        $classPropertyTypes = [];
        $reflection = new \ReflectionClass($this->class);
        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
            if (!$property->isPublic()) {
                continue;
            }
            $name = $property->getName();
            $propertyType = $this->parseProperty($property->getDocComment(), $reflection->getNamespaceName());
            self::$propertyTypes[$this->className][$name] = $propertyType;
        }
        return self::$propertyTypes[$this->className];
    }

    /**
     * 从注释中解析出属性类型
     *
     * @param string $doc
     * @param string $namespace
     *
     * @return \app\framework\base\fillable\PropertyType
     */
    protected function parseProperty(string $doc, string $namespace): PropertyType
    {
        $type = new PropertyType();
        // 从注释中解析出类型
        preg_match("/@var \??(.*?)(\[\])?[\|\s\*]/", $doc, $matches);
        $matchType = $matches[1] ?? '';
        //如果注释没有命名空间, 则查看是否再当前命名空间下存在该类
        if (!class_exists($matchType)) {
            $classType = "{$namespace}\\{$matchType}";
            if (class_exists($classType)) {
                $matchType = $classType;
            }
        }
        $type->type = $matchType;
        $type->is_class = class_exists($matchType);
        $type->is_collection = isset($matches[2]) && '[]' === $matches[2];

        return $type;
    }
}
