<?php
/**
 * Listener for load_class_model code event
 */

class XfAddOns_RpgFeatures_Listener_Post
{
	/**
	 * Extend with our own post model, that adds additional information to handle the postbit
	 *
	 * @param string	The name of the class to be created
	 * @param array		A modifiable list of classes that wish to extend the class.
	 */
	public static function listen($class, array &$extend)
	{
		if ($class == 'XenForo_Model_Post')
		{
			$extend[] = 'XfAddOns_RpgFeatures_Model_Post';
		}
		if ($class == 'XenForo_Model_Thread')
		{
			$extend[] = 'XfAddOns_RpgFeatures_Model_Thread';
		}
	}

}
