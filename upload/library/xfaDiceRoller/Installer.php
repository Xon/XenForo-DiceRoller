<?php

class xfaDiceRoller_Installer
{
    public static function install($installedAddon)
    {
        $version = is_array($installedAddon) ? $installedAddon['version_id'] : 0;

        $db = XenForo_Application::getDb();
        $db->query("DROP TABLE IF EXISTS xf_thread_rpg");
        $db->query("
            CREATE TABLE IF NOT EXISTS xf_post_dice
            (
                post_id int NOT NULL,
                dice_data mediumtext,
                PRIMARY KEY (post_id)
            ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
        ");

        SV_Utils_Install::dropColumn('xf_post_dice', 'list_data');
        $addColumn = SV_Utils_Install::addColumn('xf_thread', 'dice_count', 'int');
        if ($addColumn)
        {
            $db->query('
             UPDATE xf_thread
             SET dice_count = (select count(*)
                               from xf_post_dice
                               join xf_post on xf_post.post_id = xf_post_dice.post_id
                               where xf_post.thread_id = xf_thread.thread_id)
            ');
        }
    }


    public static function uninstall($installedAddon)
    {
        $db = XenForo_Application::getDb();
        $db->query("DROP TABLE IF EXISTS xf_thread_rpg");
        $db->query("DROP TABLE IF EXISTS xf_post_dice");
        SV_Utils_Install::dropColumn('xf_thread', 'dice_count');
    }
}
