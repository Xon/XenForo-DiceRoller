<?php

/**
 * Model class for throwing a dice, it inserts the dice into the database
 *
 * Example
 * 0: {
 * 		boxId: 0,
 * 		faces: 6,
 * 		reason: ataque
 * 		roll: [ 2, 2, 2 ],
 * 		total: 6
 * },
 * 1: {
 * 		boxId: 1,
 * 		faces: 6,
 * 		reason: defensa,
 * 		roll: [ 1, 1, 1 ],
 * 		total: 3
 * }
 */
class XfAddOns_RpgFeatures_Model_Dice
{

	/**
	 * Rolls a new dice and stores the information into the database
	 * @param $post		A reference to the post information that we want to update
	 * @param $faces	The amount of faces of the dice that we want to rull
	 * @return array	An array of diceData, at total and last_dice
	 */
	public function throwNewDice(array &$post, $faces, $reason = '')
	{
		// setup the array data
		if (!is_array($post['dice_data']))
		{
			$post['dice_data'] = array();
		}

		// setup the box
		$boxId = isset($post['dice_data']) ? count($post['dice_data']) : 0;
		if (!isset($post['dice_data'][$boxId]) || !is_array($post['dice_data'][$boxId]))
		{
			$post['dice_data'][$boxId] = array();
			$post['dice_data'][$boxId]['faces'] = $faces;
			$post['dice_data'][$boxId]['boxId'] = $boxId;
			$post['dice_data'][$boxId]['reason'] = $reason;
		}

		// calculate the boxId
		return $this->throwDice($post, $boxId);
	}

	/**
	 * Rolls a dice and stores the information into the database
	 * @param $post		A reference to the post information that we want to update
	 * @param $boxId	The box (for multiple dice) to store the roll in
	 * @return array	An array of diceData, at total and last_dice
	 */
	public function throwDice(array &$post, $boxId)
	{
		// dice data
		if (!is_array($post['dice_data']) || !is_array($post['dice_data'][$boxId]))
		{
			throw new Exception(utf8_encode('La configuración de los dados no es correcta'));
		}

		// actually roll the dice
		$diceData = &$post['dice_data'][$boxId];
		$diceRolled = rand(1, $diceData['faces']);
		$diceData['roll'][] = $diceRolled;
		$diceData['total'] = array_sum($diceData['roll']);

		// send data to the database
		$db = XenForo_Application::getDb();
		$exists = $db->fetchOne("SELECT 1 existing FROM xf_post_dice WHERE post_id = ?", array( $post['post_id'] ));
		if ($exists)
		{
			$db->query(
				"UPDATE xf_post_dice SET dice_data = ? WHERE post_id = ?",
				array( serialize($post['dice_data']), $post['post_id'] )
				);
		}
		else
		{
			$db->query(
				"REPLACE INTO xf_post_dice (post_id, dice_data) VALUES (?, ?)",
				array( $post['post_id'], serialize($post['dice_data']) )
				);
		}

		// return the data
		$diceData['last_dice'] = $diceRolled;
		return $diceData;
	}

}