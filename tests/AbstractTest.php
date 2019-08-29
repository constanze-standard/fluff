<?php

use PHPUnit\Framework\TestCase;

abstract class AbstractTest extends TestCase
{
    protected function setProperty($obj, $property, $value)
    {
        $reflectionProperty = new \ReflectionProperty($obj, $property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($obj, $value);
        $reflectionProperty->setAccessible(false);
    }

    protected function getProperty($obj, $property)
    {
        $reflectionProperty = new \ReflectionProperty($obj, $property);
        $reflectionProperty->setAccessible(true);
        $value = $reflectionProperty->getValue($obj);
        $reflectionProperty->setAccessible(false);
        return $value;
    }

    protected function callMethod($instance, $methodName, $args = [])
    {
        $method = new \ReflectionMethod($instance, $methodName);
        $method->setAccessible(true);
        $result = $method->invokeArgs($instance, $args);
        $method->setAccessible(false);
        return $result;
    }
}