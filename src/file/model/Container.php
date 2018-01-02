<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\model;

use tkanstantsin\fileupload\config\InvalidConfigException;

/**
 * Class Container
 */
class Container
{
    /**
     * Configures an object with the initial property values. Inspired by Yii2.
     * @see \yii\BaseYii::configure
     * @param object $object the object to be configured
     * @param array $properties the property initial values given in terms of
     *     name-value pairs.
     * @return object the object itself
     * @throws InvalidConfigException
     */
    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            if (!property_exists($object, $name)) {
                throw new InvalidConfigException(sprintf('Property %s in class %s not found.', $name, \get_class($object)));
            }
            $object->$name = $value;
        }

        return $object;
    }

    /**
     * Creates a new object using the given configuration. Inspired by Yii2.
     * @see \yii\BaseYii::createObject
     * @param string|array $type
     * @return mixed
     * @throws InvalidConfigException
     * @throws \ReflectionException
     */
    public static function createObject($type)
    {
        if (\is_string($type)) {
            return static::get($type);
        } elseif (\is_array($type)) {
            if (!isset($type['class'])) {
                throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
            }

            $class = $type['class'];
            unset($type['class']);

            return static::get($class, $type);
        }

        throw new InvalidConfigException('Unsupported configuration type: ' . \gettype($type));
    }

    /**
     * Creates an instance of the specified class. Inspired by Yii2 di.
     * @see \yii\di\Container::build
     * @param string $class the class name
     * @param array $config configurations to be applied to the new instance
     * @return mixed
     * @throws \ReflectionException
     * @throws InvalidConfigException
     */
    public static function get(string $class, array $config = [])
    {
        $reflection = new \ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new \ReflectionException(sprintf('`%s` is not instantiable.', $reflection->name));
        }
        if ($reflection->implementsInterface(IConfigurable::class)) {
            // set $config as the last parameter (existing one will be overwritten)
            return $reflection->newInstanceArgs([$config]);
        }

        $object = $reflection->newInstance();
        static::configure($object, $config);

        return $object;
    }
}