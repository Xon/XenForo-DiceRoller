<?php

class xfaDiceRoller_XenForo_DataWriter_Discussion_Thread extends XFCP_xfaDiceRoller_XenForo_DataWriter_Discussion_Thread
{
    protected function _getFields()
    {
        $fields = parent::_getFields();
        $fields['xf_thread']['dice_count'] =  array('type' => self::TYPE_INT, 'default' => 0);
        return $fields;
    }

    public function rebuildDiscussion()
    {
        parent::rebuildDiscussion();
        $db = $this->_db;
        $db->query('
            UPDATE xf_thread
            SET dice_count = (select count(*)
                            from xf_post_dice
                            join xf_post on xf_post.post_id = xf_post_dice.post_id
                            where xf_post.thread_id = xf_thread.thread_id)
            WHERE thread_id = ?
        ', $this->get('thread_id'));
    }
}

