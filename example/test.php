<?php

class A
{
    public function t($a) {
        return $a + 1;
    }
}

$sa = serialize([new A]);
echo $sa;

$a = unserialize($sa);
echo $a[0]->t(2);