<?php

// This class is used to encapsulate global state between layers without using $GLOBAL[] or
// relying on the consumer being loaded correctly by the dynamic class autoloader
class xfaDiceRoller_Globals
{
    public static $mergeTargetId = null;
    public static $contentIdsToMerge = null;

    private function __construct() {}
}
