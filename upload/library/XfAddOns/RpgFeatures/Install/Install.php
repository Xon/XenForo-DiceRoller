<?php

/**
 * Installation and uninstallation code
 * We require an additional field in the database
 */
class XfAddOns_RpgFeatures_Install_Install
{

	/**
	 * Add the RPG features tables
	 */
	public static function install($installedAddon)
	{
		$version = is_array($installedAddon) ? $installedAddon['version_id'] : 0;

		$db = XenForo_Application::getDb();
		if ($version < 111)
		{
			$db->query("
				CREATE TABLE IF NOT EXISTS xf_thread_rpg
				(
					thread_id int not null primary key,
					rpg_counter int null
				) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
			");
			$db->query("
				CREATE TABLE IF NOT EXISTS xf_post_dice
				(
					post_id int NOT NULL,
					dice_data mediumtext,
					list_data mediumtext,
					PRIMARY KEY (post_id)
				) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
			");
		}
	}

	/**
	 * Remove the tables used by this hack
	 * @param unknown_type $installedAddon
	 */
	public static function uninstall($installedAddon)
	{
		$db = XenForo_Application::getDb();
		$db->query("DROP TABLE xf_thread_rpg");
		$db->query("DROP TABLE xf_post_dice");
	}

}
