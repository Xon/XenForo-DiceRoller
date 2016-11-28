<?php

/**
 * Class for manipulating templates
 */
class XfAddOns_RpgFeatures_Template_Hook
{

	/**
	 * Called whenever the template object constructor is called. You may use this event to modify the name of the template being called,
	 * to modify the params being passed to the template, or to pre-load additional templates as needed.
	 *
	 * @param	string &$templateName - the name of the template to be rendered
	 * @param	array &$params - key-value pairs of parameters that are available to the template
	 * @param	XenForo_Template_Abstract $template - the template object itself
	 *
	 */
	public static function templateCreate($templateName, array &$params, XenForo_Template_Abstract $template)
	{
		if ($templateName == 'thread_view')
		{
			$template->preloadTemplate('cz_post_publicControls_dice');
			$template->preloadTemplate('cz_post_messageContent_dice');
			$template->preloadTemplate('cz_thread_view_dice');
			$template->addRequiredExternal('js', 'js/xfa-rpg/dice.js');
			$template->addRequiredExternal('js', 'js/xfa-rpg/jquery.lwtCountdown-1.0.js');
		}
	}

	/**
	 * Called whenever a template hook is encountered (via <xen:hook> tags)
	 *
	 * @param string $name		the name of the template hook being called
	 * @param string $contents	the contents of the template hook block. This content will be the final rendered output of the block. You should manipulate this, such as by adding additional output at the end.
	 * @param array $params		explicit key-value params that have been passed to the hook, enabling content-aware decisions. These will not be all the params that are available to the template
	 * @param XenForo_Template_Abstract $template	the raw template object that has called this hook. You can access the template name and full, raw set of parameters via this object.
	 * @return unknown
	 */
    public static function template($name, &$contents, array $params, XenForo_Template_Abstract $template)
    {
    	switch ($name)
    	{
    		case 'post_public_controls':
    			$templateParams = array_merge($template->getParams(), $params);
    			$contents .= $template->create('cz_post_publicControls_dice', $templateParams);
    			break;
    		case 'message_content':
    			$contents .= $template->create('cz_post_messageContent_dice', $params);
    			break;
			case 'thread_view_pagenav_before':
				self::addCounter($contents, $params, $template);
    			break;
    	}
    }

    /**
     * If a counter exists on the thread, we need to add it
     *
     * @param string $contents	The template parsed contents
     * @param array $params		An array that contents a reference to the thread
     */
    private static function addCounter(&$contents, $params, XenForo_Template_Abstract $template)
    {
    	$thread = $params['thread'];
    	if (!isset($thread['rpg_counter']) || $thread['rpg_counter'] <= 0)
    	{
    		return;
    	}

		$millis = $thread['rpg_counter'] - XenForo_Application::$time;
		$params = XfAddOns_RpgFeatures_Helper_Counter::getTimeParams($millis);
    	$contents .= $template->create('cz_rpg_thread_view_counter', $params);
    }

}
