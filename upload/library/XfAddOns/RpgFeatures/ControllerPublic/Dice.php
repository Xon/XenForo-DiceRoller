<?php

class XfAddOns_RpgFeatures_ControllerPublic_Dice extends XenForo_ControllerPublic_Abstract
{

	/**
	 * This method is called to show the overlay with the dice contents
	 * @return XenForo_ControllerResponse_View
	 */
	public function actionShowOptionsDice()
	{
		$input = $this->getInput();
		$postId = $input->filterSingle('post_id', XenForo_Input::UINT);
		return $this->responseView('XenForo_ViewPublic_Base', 'cz_rpg_dice_overlay', array(
			'post' => array(
				'post_id' => $postId
				)
			));
	}

	/**
	 * Show the overlay with the list data contents
	 * @return XenForo_ControllerResponse_View
	 */
	public function actionShowOptionsList()
	{
		$input = $this->getInput();
		$postId = $input->filterSingle('post_id', XenForo_Input::UINT);
		return $this->responseView('XenForo_ViewPublic_Base', 'cz_rpg_list_overlay', array(
			'post' => array(
				'post_id' => $postId
				)
			));
	}

	/**
	 * Add a counter in this thread
	 * @return XenForo_ControllerResponse_View
	 */
	public function actionShowCounter()
	{
		$hours = array();
		for ($i = 0; $i <= 23; $i++) {
			$hours[] = $i;
		}

		$minutes = array();
		for ($i = 0; $i <= 59; $i++) {
			$minutes[] = $i;
		}

		// get the time offset
		$visitor = XenForo_Visitor::getInstance();
		$tz = $visitor->get('timezone');
		$timeZone = new DateTimeZone($tz);
		$dateObj = new DateTime('', $timeZone);
		$offset = $dateObj->getOffset();
		$millis = XenForo_Application::$time + $offset;

		// do a 1 hour counter
		$millis += 3600;

		$input = $this->getInput();
		$postId = $input->filterSingle('post_id', XenForo_Input::UINT);
		$threadId = $input->filterSingle('thread_id', XenForo_Input::UINT);
		return $this->responseView('XenForo_ViewPublic_Base', 'cz_rpg_counter_overlay', array(
			'end_date' => date('Y-m-d', $millis),
			'selectedHour' => date('G', $millis),
			'selectedMinute' => date('i', $millis),
			'hours' => $hours,
			'minutes' => $minutes,
			'post' => array(
				'post_id' => $postId
				),
			'thread' => array(
				'thread_id' => $threadId
				)
			));
	}

	/**
	 * This action is invoked when the form is submitted, and we are asked to setup a new counter on the page
	 */
	public function actionConfigureCounter()
	{
		// validate that we can throw a dice
		$post = $this->getPost();
		if (!$post['canAddCounter'])
		{
			return $this->responseError(new XenForo_Phrase('cz_rpg_no_permissions_to_add_countdown'));
		}

		$input = $this->getInput();
		$data = $input->filter(array(
			'thread_id' => XenForo_Input::UINT,
			'end_date' => XenForo_Input::DATE_TIME,
			'hour' => XenForo_Input::UINT,
			'minute' => XenForo_Input::UINT
			));

		$millis = $data['end_date'] + $data['hour'] * 3600 + $data['minute'] * 60;

		$model = new XfAddOns_RpgFeatures_Model_Counter();
		$model->updateCounter($data['thread_id'], $millis);

		// craft the response
		$timeParams = XfAddOns_RpgFeatures_Helper_Counter::getTimeParams($millis - XenForo_Application::$time);
		$viewParams = array(
			'template' => $this->getTemplate('cz_rpg_thread_view_counter', $timeParams)
			);

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildBasicLinkWithIntegerParam('thread', 'index', '', $data, 'thread_id'),
			'Has agregado un contador', $viewParams
		);
	}

	/**
	 * Action invoked to throw a dice
	 * @return A view object
	 */
	public function actionNewDice()
	{
		// validate that we can throw a dice
		$post = $this->getPost();
		if (!$post['canThrowDice'])
		{
			return $this->responseError(new XenForo_Phrase('cz_rpg_no_permissions_to_throw_dice'));
		}

		// get the faces
		$faces = $this->getFaces();

		// get the reason, if any
		$input = $this->getInput();
		$reason = $input->filterSingle('diceReason', XenForo_Input::STRING);
		$reason = strip_tags(XenForo_Input::cleanString($reason));

		// and roll the dice
		$model = new XfAddOns_RpgFeatures_Model_Dice();
		$diceRoll = $model->throwNewDice($post, $faces, $reason);

		// create the params and do the redirect
		$viewParams = array(
			'postId' => $post['post_id'],
			'boxId' => $diceRoll['boxId'],
			'template' => $this->getTemplate('cz_post_diceData', array( 'message' => $post, 'diceRoll' => $diceRoll ))
			);

		$msgResponse = new XenForo_Phrase('xfa_rpg_dice_thrown');
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildBasicLinkWithIntegerParam('thread', 'index', '', $post, 'thread_id'),
			$msgResponse, $viewParams
		);
	}

	/**
	 * This method is called when we throw a dice in an existing slot
	 */
	public function actionExistingDice()
	{
		// validate that we can throw a dice
		$post = $this->getPost();
		if (!$post['canThrowDice'])
		{
			return $this->responseError(new XenForo_Phrase('cz_rpg_no_permissions_to_throw_dice'));
		}

		// update the post type and dispatch to the view
		$boxId = $this->getBox($post);

		// options we'll need
		$options = XenForo_Application::get('options');

		// we can roll a maximum of dices
		$diceData = &$post['dice_data'][$boxId];
		if (count($diceData['roll']) >= $options->cz_max_die_per_box)
		{
			return $this->responseError(new XenForo_Phrase('cz_rpg_cannot_throw_no_die', array ( 'no' => $options->cz_max_die_per_box )));
		}

		// and roll the dice
		$model = new XfAddOns_RpgFeatures_Model_Dice();
		$diceRoll = $model->throwDice($post, $boxId);

		// create the params and do the redirect
		$viewParams = array(
			'postId' => $post['post_id'],
			'boxId' => $boxId,
			'total' => $diceRoll['total'],
			'template' => $this->getTemplate('cz_dice', array( 'dice' => $diceRoll['last_dice'] ))
			);

		$msgResponse = new XenForo_Phrase('xfa_rpg_dice_thrown');
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildBasicLinkWithIntegerParam('thread', 'index', '', $post, 'thread_id'),
			$msgResponse, $viewParams
		);
	}

	/**
	 * Return the post from the input data
	 */
	private function getPost()
	{
		$input = $this->getInput();

		// validate the postId
		$postId = $input->filterSingle('post_id', XenForo_Input::UINT);
		if (!$postId)
		{
			throw new XenForo_ControllerResponse_Exception($this->responseError(new XenForo_Phrase('cz_rpg_please_enter_post_to_throw')));
		}

		/* @var $ftpHelper XenForo_ControllerHelper_ForumThreadPost */
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($postId);
		if (!$post)
		{
			$msg = 'No se ha encontrado el post para tirar los dados';
			throw new XenForo_ControllerResponse_Exception($this->responseError(new XenForo_Phrase('cz_rpg_please_enter_post_to_throw')));
		}
		return $post;
	}

	/**
	 * The number of faces that we should use in the roll. If the post data has already been initialized, we will use
	 * the same faces configuration, otherwise we will try to get the information from the request. If we cannot get
	 * either, an exception is thrown
	 *
	 * @param array $post	Array with the post information
	 * @return int
	 */
	private function getFaces()
	{
		// if we do not have that, check from the request input
		$input = $this->getInput();
		$faces = $input->filterSingle('diceFaces', XenForo_Input::UINT);
		if (!$faces)
		{
			throw new XenForo_ControllerResponse_Exception($this->responseError(new XenForo_Phrase('cz_rpg_incorrect_number_for_faces')));
		}

		// settings we need
		$options = XenForo_Application::get('options');

		// check that we don't exceed the maximum faces
		if ($faces < 2)
		{
			throw new XenForo_ControllerResponse_Exception($this->responseError(new XenForo_Phrase('cz_rpg_faces_must_be_at_least_2')));
		}
		if ($faces > $options->cz_max_faces)
		{
			throw new XenForo_ControllerResponse_Exception($this->responseError(new XenForo_Phrase('cz_rpg_faces_must_be_at_most_no', array( 'no' => $options->cz_max_faces ))));
		}

		return $faces;
	}

	/**
	 * Box is used when we throw a dice in a box that already exists. For this, we need to validate that it is contained
	 * inside the post
	 */
	private function getBox($post)
	{
		$input = $this->getInput();
		$boxId = $input->filterSingle('boxId', XenForo_Input::UINT);

		if ($boxId < 0)
		{
			throw new XenForo_ControllerResponse_Exception($this->responseError(new XenForo_Phrase('cz_rpg_position_error_to_throw')));
		}
		if (!isset($post['dice_data']) || !isset($post['dice_data'][$boxId]))
		{
			throw new XenForo_ControllerResponse_Exception($this->responseError(new XenForo_Phrase('cz_rpg_position_error_not_exists')));
		}
		return $boxId;
	}

	/**
	 * Prepares and fetches a tempaltes, and returns the results.
	 *
	 * @param string $name	The name of the template
	 * @param array $params	Any params to initialize for the template
	 * @return XenForo_Template_Public
	 */
	private function getTemplate($name, array $params)
	{
		$template = new XenForo_Template_Public($name, $params);
		$visitor = XenForo_Visitor::getInstance();
		$options = XenForo_Application::get('options');
		
		$styleId = $visitor->get('style_id');
		$styleId = $styleId ? $styleId : $options->defaultStyleId;
		$template->setStyleId($styleId);
		
		$languageId = $visitor->get('language_id');
		$languageId = $languageId ? $languageId : $options->defaultLanguageId;
		$template->setLanguageId($languageId);
		return $template;
	}


	/**
	 * Action invoked to pick an element from a list
	 * @return A view object
	 */
	public function actionPickOptionFromList()
	{
		// validate that we can pick from a list
		$post = $this->getPost();
		if (!$post['canRpgAction'])
		{
			return $this->responseError(new XenForo_Phrase('cz_rpg_no_permissions_choose_list'));
		}

		// get the options
		$listOptions = $this->getListOptions();

		// roll the list options
		$model = new XfAddOns_RpgFeatures_Model_List();
		$listRoll = $model->pickOptionFromList($post, $listOptions);

		// create the params and do the redirect
		$viewParams = array(
			'postId' => $post['post_id'],
			'boxId' => $listRoll['boxId'],
			'template' => $this->getTemplate('cz_post_listData', array( 'message' => $post, 'listRoll' => $listRoll ))
			);

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildBasicLinkWithIntegerParam('thread', 'index', '', $post, 'thread_id'),
			'Has elegido una opci&oacute;n al azar de una lista', $viewParams
		);
	}

	/**
	 * Returns the list options, from which we need to choose one. Sanitizes the list to remove empty elements
	 *
	 * @param array $post	Array with the post information
	 * @return int
	 */
	private function getListOptions()
	{
		// if we do not have that, check from the request input
		$input = $this->getInput();
		$options = $input->filterSingle('options', XenForo_Input::ARRAY_SIMPLE);
		$options = !empty($options) ? $options : array();

		$ret = array();
		foreach ($options as $opt)
		{
			$opt = strip_tags(XenForo_Input::cleanString($opt));
			if (!empty($opt))
			{
				$ret[] = $opt;
			}
		}

		if (empty($ret))
		{
			throw new XenForo_ControllerResponse_Exception($this->responseError(new XenForo_Phrase('cz_rpg_must_write_list_of_options')));
		}
		return $ret;
	}



}