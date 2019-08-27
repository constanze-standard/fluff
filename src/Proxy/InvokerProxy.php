<?php

namespace ConstanzeStandard\Fluff\Proxy;

use Beige\Invoker\Interfaces\InvokerInterface;

class InvokerProxy
{
    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * Calling a function or callable object.
     * Declare type parameters that will inject instances from container,
     * You can provide additional parameter values to invoker through the second parameter.
     * 
     * @param callable $invoker
     * @param array $parameters (i.e. ['param1' => 1])
     * 
     * @return mixed Return value of invoker returns.
     */
    public function call(callable $function, array $parameters = [])
    {
        return $this->invoker->call($function, $parameters);
    }

    /**
     * Instantiating classes by class name.
     * Declare type parameters that will inject instances from container,
     * You can provide additional parameter values to `__construct` through the second parameter.
     * 
     * @param string $className
     * @param array $parameters (i.e. ['param1' => 1])
     * 
     * @return object Instance of class.
     */
    public function new(string $className, array $parameters = [])
    {
        return $this->invoker->new($className, $parameters);
    }

    /**
     * Calling a method of instance.
     * Declare type parameters that will inject instances from container,
     * You can provide additional parameter values to method through the third parameter.
     * 
     * @param object $instance
     * @param string $method
     * @param array $parameters
     * 
     * @throws \ReflectionException
     * 
     * @return mixed The value of method.
     */
    public function callMethod(object $instance, string $method, array $parameters = [])
    {
        return $this->invoker->callMethod($instance, $method, $parameters);
    }
}
