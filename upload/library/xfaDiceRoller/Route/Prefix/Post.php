<?php

class xfaDiceRoller_Route_Prefix_Post implements XenForo_Route_Interface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'post_id');
        return $router->getRouteMatch('xfaDiceRoller_ControllerPublic_Dice', $action);
    }

    /**
     * Method to build a link that includes the post_id
     * @see XenForo_Route_BuilderInterface
     */
    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'post_id');
    }

}