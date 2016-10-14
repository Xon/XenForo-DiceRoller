<?php

class xfaDiceRoller_XenForo_DataWriter_DiscussionMessage_Post extends XFCP_xfaDiceRoller_XenForo_DataWriter_DiscussionMessage_Post
{
    protected function _messagePostSave()
    {
        parent::_messagePostSave();
/*
        if ($this->isUpdate() && $this->isChanged('thread_id'))
        {
            $this->_db->query("
                update xf_thread set dice_count = dice_count + 1 where thread_id = ?
            ", array($this->get('thread_id')));

            $this->_db->query("
                update xf_thread set dice_count = GREATEST(dice_count - 1, 0) where thread_id = ?
            ", array($this->getExisting('thread_id')));
        }
*/
    }

    protected function _messagePostDelete()
    {
        parent::_messagePostDelete();

        $deleteResult =  $this->_db->query("
            delete from xf_post_dice where post_id = ?
        ", array($this->get('post_id')));

        if ($deleteResult->rowCount())
        {
            $this->_db->query("
                update xf_thread set dice_count = GREATEST(dice_count - 1, 0) where thread_id = ?
            ", array($this->get('thread_id')));
        }
    }
}

