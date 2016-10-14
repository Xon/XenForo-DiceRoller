<?php

class xfaDiceRoller_XenForo_DataWriter_DiscussionMessage_Post extends XFCP_xfaDiceRoller_XenForo_DataWriter_DiscussionMessage_Post
{
    protected function _messagePostDelete()
    {
        parent::_messagePostDelete();

        $this->_db->query("
            delete from xf_post_dice where post_id = ?
        ", array($this->get('post_id')));
    }
}

