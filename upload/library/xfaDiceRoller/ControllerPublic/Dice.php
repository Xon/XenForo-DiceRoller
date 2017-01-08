<?php

class xfaDiceRoller_ControllerPublic_Dice extends XenForo_ControllerPublic_Abstract
{
    public function actionOptions()
    {
        $postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);

        /* @var $ftpHelper XenForo_ControllerHelper_ForumThreadPost */
        $ftpHelper = $this->getHelper('ForumThreadPost');
        list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($postId);

        if (empty($post['canThrowDie']))
        {
            return $this->responseNoPermission();
        }

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
        if (empty($post['canThrowDie']))
        {
            return $this->responseNoPermission();
        }

        // get the faces
        $faces = $this->_input->filterSingle('diceFaces', XenForo_Input::UINT);
        if (!$faces)
        {
            return $this->responseError(new XenForo_Phrase('cz_rpg_incorrect_number_for_faces'));
        }
        // check that we don't exceed the maximum faces
        if ($faces < 2)
        {
            return $this->responseError(new XenForo_Phrase('cz_rpg_faces_must_be_at_least_2'));
        }
        $cz_max_faces = XenForo_Application::getOptions()->cz_max_faces;
        if ($faces > $cz_max_faces)
        {
            return $this->responseError(new XenForo_Phrase('cz_rpg_faces_must_be_at_most_no', array( 'no' => $cz_max_faces)));
        }
        // get the reason, if any
        $reason = $this->_input->filterSingle('diceReason', XenForo_Input::STRING);

        $diceModel = XenForo_Model::create('xfaDiceRoller_Model_Dice');

        $this->assertNotFlooding('dice', 1);
        XenForo_Db::beginTransaction();

        $diceData = $diceModel->getDiceData($post['post_id']);
        list($diceData, $diceRoll) = $diceModel->throwNewDice($post['thread_id'], $post['post_id'], $diceData, $faces, $reason);

        XenForo_Db::commit();

        $viewParams = array(
            'postId' => $post['post_id'],
            'boxId' => $diceRoll['boxId'],
            'total' => $diceRoll['total'],
            'dice' => $diceRoll['last_dice'],
            'diceRoll' => $diceRoll,
            'message' => $post,
        );


        if ($this->_noRedirect())
        {
            $view = $this->responseView('xfaDiceRoller_ViewPublic_Dice', 'cz_post_diceData', $viewParams);
            $view->jsonParams = array(
                'postId' => $post['post_id'],
                'boxId' => $diceRoll['boxId'],
                'message' => new XenForo_Phrase('xfa_rpg_dice_thrown'),
            );
            return $view;
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
        $postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);

        /* @var $ftpHelper XenForo_ControllerHelper_ForumThreadPost */
        $ftpHelper = $this->getHelper('ForumThreadPost');
        list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($postId);
        if (empty($post['canThrowDie']))
        {
            return $this->responseNoPermission();
        }

        // update the post type and dispatch to the view
        $boxId = $this->_input->filterSingle('boxId', XenForo_Input::UINT);

        if ($boxId < 0)
        {
            return $this->responseError(new XenForo_Phrase('cz_rpg_position_error_to_throw'));
        }

        $model = XenForo_Model::create('xfaDiceRoller_Model_Dice');

        $this->assertNotFlooding('dice', 1);
        XenForo_Db::beginTransaction();

        $diceData = $model->getDiceData($post['post_id']);

        if (empty($diceData) || empty($diceData[$boxId]))
        {
            return $this->responseError(new XenForo_Phrase('cz_rpg_position_error_not_exists'));
        }

        // we can roll a maximum of dices
        $diceBox = $diceData[$boxId];
        $cz_max_die_per_box = XenForo_Application::getOptions()->cz_max_die_per_box;
        if (count($diceBox['roll']) >= $cz_max_die_per_box)
        {
            return $this->responseError(new XenForo_Phrase('cz_rpg_cannot_throw_no_dice', array ( 'count' => $cz_max_die_per_box )));
        }

        // and roll the dice
        list($diceData, $diceRoll) = $model->throwDice($post['thread_id'],$post['post_id'], $diceData, $boxId);

        XenForo_Db::commit();

        $viewParams = array(
            'postId' => $post['post_id'],
            'boxId' => $boxId,
            'total' => $diceRoll['total'],
            'dice' => $diceRoll['last_dice'],
        );

        if ($this->_noRedirect())
        {
            $view = $this->responseView('xfaDiceRoller_ViewPublic_Dice', 'cz_dice', $viewParams);
            $view->jsonParams = $viewParams;
            return $view;
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