<?php

/**
 * Extends the default model to provide parsing for the dice privileges
 */
class XfAddOns_RpgFeatures_Model_Post extends XFCP_XfAddOns_RpgFeatures_Model_Post
{

	/**
	 * This function is overriden to add fanfic specific options, like changing the information
	 * in the postbit
	 */
	public function preparePost(array $post, array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
	{
		$post = parent::preparePost($post, $thread, $forum, $nodePermissions, $viewingUser);

		$visitor = XenForo_Visitor::getInstance();
		$post['canThrowDice'] = $this->getCanThrowDice($post, $visitor);
		$post['canRpgAction'] = $this->getCanThrowDice($post, $visitor);
		$post['canAddCounter'] = $post['canRpgAction'] && $post['post_id'] == $thread['first_post_id'];

		// unserialize post data as available
		if (isset($post['dice_data']) && $post['dice_data'])
		{
			$post['dice_data'] = @unserialize($post['dice_data']);
			if (is_array($post['dice_data']))
			{
				$post['dice_box_count'] = @count($post['dice_data']);
			}
		}
		if (isset($post['list_data']) && $post['list_data'])
		{
			$post['list_data'] = @unserialize($post['list_data']);
			if (is_array($post['list_data']))
			{
				foreach ($post['list_data'] as $key => &$roll)
				{
					if (!isset($roll['result']))
					{
						unset($post['list_data'][$key]);
						continue;
					}
					$idx = intval($roll['result']);
					if ($idx < count($roll['options']))
					{
						$roll['selectedOption'] = $roll['options'][$idx];
					}
				}
			}
		}

		return $post;
	}

	/**
	 * Check if a dice can be thrown
	 *
	 * @param array $post		Data for the post
	 * @param XenForo_Visitor $visitor	Reference to the visitor
	 */
	private function getCanThrowDice($post, XenForo_Visitor $visitor)
	{
		// user id for the visitor
		$userId = $visitor->get('user_id');
		
		// author of the post can throw a dice
		return $post['user_id'] == $userId;
	}

	/**
	 * An overload on the join options, to add the dice capability. This will make sure that the
	 * dice information can be loaded
	 */
	public function preparePostJoinOptions(array $fetchOptions)
	{
		$options = parent::preparePostJoinOptions($fetchOptions);
		$options['selectFields'] .= ',dice_data,list_data';
		$options['joinTables'] .= ' LEFT JOIN xf_post_dice AS post_dice ON (post_dice.post_id = post.post_id)';
		return $options;
	}
    
	public function copyPosts(array $posts, array $sourceThreads, array $targetThread, array $options = array())
	{
        // strip out dice roller info from the posts. don't copy it for now.
        $posts_ = array();
        foreach ($posts AS $post)
        {
            unset($post['list_data']);        
            unset($post['dice_data']);
            unset($post['dice_box_count']);
            $posts_[] = $post;
        }
        
		$targetthread = parent::copyPosts($posts_, $sourceThreads, $targetThread, $options);
        
        return $targetthread;
	}    


}

