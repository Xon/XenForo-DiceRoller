<?php

class xfaDiceRoller_Model_Dice extends XenForo_Model
{
    public function getDiceData($postId)
    {
        $dice = $this->_getDb()->fetchRow('
            SELECT dice_data
            FROM xf_post_dice
            WHERE post_id = ?
        ', array($postId));

        if (empty($dice['dice_data']))
        {
            return null;
        }

        $dice_data = @unserialize($dice['dice_data']);

        if ($dice_data && is_array($dice_data))
        {
            return $dice_data;
        }

        return null;
    }

    public function throwNewDice($threadId, $postId, array $diceData, $faces, $reason = '')
    {
        if (empty($diceData))
        {
            $diceData = array();
        }

        $boxId = count($diceData);
        $diceData[$boxId] = array(
            'faces' => $faces,
            'boxId' => $boxId,
            'reason' => $reason,
            'roll' => array(),
            'total' => 0,
        );

        return $this->throwDice($threadId, $postId, $diceData, $boxId);
    }

    public function throwDice($threadId, $postId, array $diceData, $boxId)
    {
        if (!isset($diceData[$boxId]))
        {
            throw new Exception('Malformed dice data');
        }

        $dicebox = &$diceData[$boxId];
        $diceRolled = mt_rand(1, $dicebox['faces']);
        $dicebox['roll'][] = $diceRolled;
        $dicebox['total'] = array_sum($dicebox['roll']);

        $db = $this->_getDb();
        $db->query("
            INSERT INTO xf_post_dice (post_id, dice_data) VALUES (?,?)
            ON DUPLICATE KEY UPDATE
                dice_data = VALUES(dice_data)
        ", array($postId, serialize($diceData)));        
        $db->query("
             UPDATE xf_thread
             SET dice_count = (select count(*)
                               from xf_post_dice
                               join xf_post on xf_post.post_id = xf_post_dice.post_id
                               where xf_post.thread_id = xf_thread.thread_id)
             WHERE thread_id = ?
        ", array($threadId));

        $dicebox['last_dice'] = $diceRolled;
        return array($diceData, $dicebox);
    }
}