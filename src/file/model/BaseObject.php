<?php

namespace tkanstantsin\fileupload\model;

use tkanstantsin\fileupload\config\InvalidConfigException;

/**
 * Class BaseObject
 */
class BaseObject implements IConfigurable
{
    /**
     * Util constructor.
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct(array $config = [])
    {
        Container::configure($this, $config);
        $this->init();
    }

    /**
     * Initialize object
     * E.g. instantiate some variables or check correct data
     */
    public function init(): void
    {
    }

    /**
     * Returns the value of an object property.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$value = $object->property;`.
     * @param string $name the property name
     * @return mixed the property value
     * @throws \RuntimeException
     * @see __set()
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . $name)) {
            throw new \RuntimeException('Getting write-only property: ' . \get_class($this) . '::' . $name);
        }

        throw new \RuntimeException('Getting unknown property: ' . \get_class($this) . '::' . $name);
    }

    /**
     * Sets value of an object property.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$object->property = $value;`.
     * @param string $name the property name or the event name
     * @param mixed $value the property value
     * @throws \RuntimeException
     * @see __get()
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new \RuntimeException('Setting read-only property: ' . \get_class($this) . '::' . $name);
        } else {
            throw new \RuntimeException('Setting unknown property: ' . \get_class($this) . '::' . $name);
        }
    }

    /**
     * Checks if a property is set, i.e. defined and not null.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `isset($object->property)`.
     *
     * Note that if the property is not defined, false will be returned.
     * @param string $name the property name or the event name
     * @return bool whether the named property is set (not null).
     * @see http://php.net/manual/en/function.isset.php
     */
    public function __isset($name): bool
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        }

        return false;
    }

    /**
     * Sets an object property to null.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `unset($object->property)`.
     *
     * Note that if the property is not defined, this method will do nothing.
     * If the property is read-only, it will throw an exception.
     * @param string $name the property name
     * @throws \RuntimeException
     * @see http://php.net/manual/en/function.unset.php
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new \RuntimeException('Unsetting read-only property: ' . \get_class($this) . '::' . $name);
        }
    }
}