<?php

/**
 * Model class for choosing from a list
 *
 * Example
 * 0: {
 * 		boxId: 0,
 * 		options: [ 'apple', 'orange', 'pineapple' ],
 * 		result: 1
 * },
 * 1: {
 * 		boxId: 1,
 * 		options: [ 'apple', 'orange', 'pineapple' ],
 * 		result: 2
 * }
 */
class XfAddOns_RpgFeatures_Model_List
{

	/**
	 * Chooses an element from a list
	 *
	 * @param $post		A reference to the post information that we want to update
	 * @param $options	All the available options
	 * @return array	An array of listData
	 */
	public function pickOptionFromList(array &$post, $options)
	{
		// setup the array data
		if (!is_array($post['list_data']))
		{
			$post['list_data'] = array();
		}

		// setup the box
		$boxId = isset($post['list_data']) ? count($post['list_data']) : 0;

		$post['list_data'][$boxId] = array();
		$post['list_data'][$boxId]['boxId'] = $boxId;
		$post['list_data'][$boxId]['options'] = $options;

		$result = mt_rand(0, count($options) - 1);
		$post['list_data'][$boxId]['result'] = $result;

			// send data to the database
		$db = XenForo_Application::getDb();
		$exists = $db->fetchOne("SELECT 1 existing FROM xf_post_dice WHERE post_id = ?", array( $post['post_id'] ));
		if ($exists)
		{
			$db->query(
				"UPDATE xf_post_dice SET list_data = ? WHERE post_id = ?",
				array( serialize($post['list_data']), $post['post_id'] )
				);
		}
		else
		{
			$db->query(
				"REPLACE INTO xf_post_dice (post_id, list_data) VALUES (?, ?)",
				array( $post['post_id'], serialize($post['list_data']) )
				);
		}

		// return the data
		$post['list_data'][$boxId]['selectedOption'] = $options[$result];
		return $post['list_data'][$boxId];
	}

}