<?php

class xfaDiceRoller_ControllerPublic_Dice extends XenForo_ControllerPublic_Abstract
{
    public function actionOptions()
    {
        $postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);
        return $this->responseView('XenForo_ViewPublic_Base', 'cz_rpg_dice_overlay', array(
            'post' => array(
                'post_id' => $postId
                )
            ));
    }

    public function actionAdd()
    {
        $postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);

        /* @var $ftpHelper XenForo_ControllerHelper_ForumThreadPost */
        $ftpHelper = $this->getHelper('ForumThreadPost');
        list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($postId);
        if (!$post)
        {
            return $this->responseException($this->responseNoPermission());
        }

        // validate that we can throw a dice
        if (empty($post) || empty($post['canThrowDice']))
        {
            return $this->responseException($this->responseNoPermission());
        }

        // get the faces
        $faces = $this->_input->filterSingle('diceFaces', XenForo_Input::UINT);
        if (!$faces)
        {
            throw new XenForo_ControllerResponse_Exception($this->responseError(new XenForo_Phrase('cz_rpg_incorrect_number_for_faces')));
        }

        // check that we don't exceed the maximum faces
        if ($faces < 2)
        {
            throw new XenForo_ControllerResponse_Exception($this->responseError(new XenForo_Phrase('cz_rpg_faces_must_be_at_least_2')));
        }
        $cz_max_faces = XenForo_Application::getOptions()->cz_max_faces;
        if ($faces > $cz_max_faces)
        {
            throw new XenForo_ControllerResponse_Exception($this->responseError(new XenForo_Phrase('cz_rpg_faces_must_be_at_most_no', array( 'no' => $cz_max_faces ))));
        }

        // get the reason, if any
        $reason = $this->_input->filterSingle('diceReason', XenForo_Input::STRING);
        // and roll the dice
        $model = XenForo_Model::create('xfaDiceRoller_Model_Dice');
        list($post, $diceRoll) = $model->throwNewDice($post, $faces, $reason);

        $viewParams = array(
            'postId' => $post['post_id'],
            'boxId' => $boxId,
            'total' => $diceRoll['total'],
            'message' => $post,
            'dice' => $diceRoll['last_dice'],
        );
            
        if ($this->_noRedirect())
        {
            return $this->responseView('xfaDiceRoller_ViewPublic_Dice', 'cz_dice', $viewParams);
        }
        else
        {
            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildBasicLinkWithIntegerParam('thread', 'index', '', $post, 'thread_id'),
                new XenForo_Phrase('xfa_rpg_dice_thrown')
            );
        }
    }

    public function actionAddMore()
    {
        // validate that we can throw a dice
        $post = $this->getPost();
        if (empty($post['canThrowDie']))
        {
            return $this->responseException($this->responseNoPermission());
        }

        // update the post type and dispatch to the view
        $boxId = $this->_input->filterSingle('boxId', XenForo_Input::UINT);

        if ($boxId < 0)
        {
            throw new XenForo_ControllerResponse_Exception($this->responseError(new XenForo_Phrase('cz_rpg_position_error_to_throw')));
        }
        if (!isset($post['dice_data']) || !isset($post['dice_data'][$boxId]))
        {
            throw new XenForo_ControllerResponse_Exception($this->responseError(new XenForo_Phrase('cz_rpg_position_error_not_exists')));
        }

        // we can roll a maximum of dices
        $diceData = $post['dice_data'][$boxId];
        $cz_max_die_per_box = XenForo_Application::getOptions()->cz_max_die_per_box;
        if (count($diceData['roll']) >= $cz_max_die_per_box)
        {
            return $this->responseError(new XenForo_Phrase('cz_rpg_cannot_throw_no_die', array ( 'no' => $cz_max_die_per_box )));
        }

        // and roll the dice
        $model = XenForo_Model::create('xfaDiceRoller_Model_Dice');
        list($post, $diceRoll) = $model->throwDice($post, $boxId);

        $viewParams = array(
            'postId' => $post['post_id'],
            'boxId' => $boxId,
            'total' => $diceRoll['total'],
            'message' => $post,
            'dice' => $diceRoll['last_dice']
        );
            
        if ($this->_noRedirect())
        {
            return $this->responseView('xfaDiceRoller_ViewPublic_Dice', 'cz_dice', $viewParams);
        }
        else
        {
            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildBasicLinkWithIntegerParam('thread', 'index', '', $post, 'thread_id'),
                new XenForo_Phrase('xfa_rpg_dice_thrown')
            );
        }
    }
}