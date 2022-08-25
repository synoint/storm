<?php
namespace App\Tests\Unit;

use PHPUnit;

/**
 * Class TestCase
 */
abstract class TestCase extends PHPUnit\Framework\TestCase
{
    /**
     * Return value protected/private property from object.
     *
     * Chaining can be used to configure mocked objects:
     *
     * $this->getHiddenProperty($someObject, 'someService')
     *      ->expects($this->once())
     *      ->method('someMethod');
     *
     * @param \object $object Target object
     * @param string  $name   Name of hidden property
     *
     * @return \object
     * @throws \ReflectionException
     */
    protected function getHiddenProperty($object, $name): object
    {
        $refl = new \ReflectionProperty(get_class($object), $name);
        $refl->setAccessible(true);

        return $refl->getValue($object);
    }

    /**
     * Set value for protected/private property on object.
     *
     * @param \object $object Target object
     * @param string  $name   Property name
     * @param mixed   $value  New value
     *
     * @return void
     * @throws \ReflectionException
     */
    protected function setHiddenProperty($object, $name, $value)
    {
        $refl = new \ReflectionProperty(get_class($object), $name);
        $refl->setAccessible(true);
        $refl->setValue($object, $value);
    }
}
