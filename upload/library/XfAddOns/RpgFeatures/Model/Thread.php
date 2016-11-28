<?php

/**
 * Extends the default model to provide parsing for the dice privileges
 */
class XfAddOns_RpgFeatures_Model_Thread extends XFCP_XfAddOns_RpgFeatures_Model_Thread
{

	/**
	 * An overload on the join options, to add the dice capability. This will make sure that the
	 * dice information can be loaded
	 */
	public function prepareThreadFetchOptions(array $fetchOptions)
	{
		$ret = parent::prepareThreadFetchOptions($fetchOptions);
		$ret['selectFields'] .= ', rpg_counter';
		$ret['joinTables'] .= '
			LEFT JOIN xf_thread_rpg AS threadRpg ON (thread.thread_id = threadRpg.thread_id)
			';
		return $ret;
	}


}