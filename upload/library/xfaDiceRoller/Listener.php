<?php

class xfaDiceRoller_Listener
{
    public static function load_class($class, array &$extend)
    {
        $extend[] = 'xfaDiceRoller_'.$class;
    }
}
