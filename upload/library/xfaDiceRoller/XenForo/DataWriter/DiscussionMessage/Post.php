<?php

class xfaDiceRoller_XenForo_DataWriter_DiscussionMessage_Post extends XFCP_xfaDiceRoller_XenForo_DataWriter_DiscussionMessage_Post
{
    protected function _messagePostSave()
    {
        parent::_messagePostSave();
        // only one DataWriter is edited when merging posts
        if (xfaDiceRoller_Globals::$mergeTargetId && 
            xfaDiceRoller_Globals::$mergeTargetId == $this->get('post_id') &&
            $this->isUpdate())
        {
            $contentId = $this->get('post_id');
            /* @var $diceModel xfaDiceRoller_Model_Dice */
            $diceModel = $this->getModelFromCache('xfaDiceRoller_Model_Dice');
            if(xfaDiceRoller_Globals::$contentIdsToMerge)
            {
                $diceModel->mergeDicePosts($this->get('thread_id'), $contentId, xfaDiceRoller_Globals::$contentIdsToMerge);
            }
        }  
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

