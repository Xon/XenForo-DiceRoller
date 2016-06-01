<?php

class xfaDiceRoller_Model_Dice extends XenForo_Model
{
    public function throwNewDice(array $post, $faces, $reason = '')
    {
        if (!isset($post['dice_data']) || !is_array($post['dice_data']))
        {
            $post['dice_data'] = array();
        }

        $boxId = count($post['dice_data']);
        $post['dice_data'][$boxId] = array( 
            'faces' => $faces,
            'boxId' => $boxId,
            'reason' => $reason,
            'roll' => array(),
            'total' => 0,
        );

        return $this->throwDice($post, $boxId);
    }

    public function throwDice(array $post, $boxId)
    {
        if (!isset($post['dice_data'][$boxId]))
        {
            throw new Exception('Malformed dice data');
        }

        $diceData = &$post['dice_data'][$boxId];
        $diceRolled = mt_rand(1, $diceData['faces']);
        $diceData['roll'][] = $diceRolled;
        $diceData['total'] = array_sum($diceData['roll']);

        $this->_getDb()->query("
            INSERT INTO xf_post_dice (post_id, dice_data) VALUES (?,?)
            ON DUPLICATE KEY UPDATE
                dice_data = VALUES(dice_data)
        ", array($post['post_id'], serialize($post['dice_data'])));

        $diceData['last_dice'] = $diceRolled;
        return array($post, $diceData);
    }
}