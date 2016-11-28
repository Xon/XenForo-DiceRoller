<?php

class XfAddOns_RpgFeatures_Model_Counter
{

	/**
	 * Update the value of the counter for the thread. This method gets a specific timestamp for the counter, and
	 * updates the specified thread with that timezone
	 *
	 * @param unknown_type $threadId	The identifier for the thread
	 * @param unknown_type $counter		The timestamp
	 */
	public function updateCounter($threadId, $counter)
	{
		$db = XenForo_Application::getDb();
		$db->query("REPLACE INTO xf_thread_rpg (thread_id, rpg_counter) VALUES (?, ?)",
			array( $threadId, $counter ));
	}


}