<?php

class xfaDiceRoller_XenForo_Model_Post extends XFCP_xfaDiceRoller_XenForo_Model_Post
{
    protected static $diceRoller = null;

    public function preparePost(array $post, array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

        $post = parent::preparePost($post, $thread, $forum, $nodePermissions, $viewingUser);

        if (self::$diceRoller == null)
        {
            self::$diceRoller = XenForo_Application::getOptions()->cz_enable_die;
        }

        if (self::$diceRoller)
        {
            $post['canViewDice'] = $this->canViewDice($post, $thread, $forum, $null, $nodePermissions, $viewingUser);
            $post['canThrowDie'] = $post['canViewDice'] && $this->canThrowDie($post, $thread, $forum, $null, $nodePermissions, $viewingUser);

            if ($post['canViewDice'] && !empty($post['dice_data']))
            {
                $diceData = @unserialize($post['dice_data']);
                if ($diceData && is_array($diceData))
                {
                    $post['dice_data'] = $diceData;
                }
                else
                {
                    unset($post['dice_data']);
                }
            }
            else
            {
                unset($post['dice_data']);
            }

        }

        return $post;
    }

    public function canViewDice(array $post, array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

        if (empty($viewingUser['user_id']))
        {
            return false;
        }

        return XenForo_Permission::hasContentPermission($nodePermissions, 'viewDice');
    }

    public function canThrowDie(array $post, array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

        if (empty($viewingUser['user_id']))
        {
            return false;
        }

        if (empty($post['canEdit']))
        {
            return false;
        }

        if (!XenForo_Permission::hasContentPermission($nodePermissions, 'throwDie'))
        {
            return false;
        }

        return $post['canEdit'] && ($post['user_id'] == $viewingUser['user_id']);
    }

    public function preparePostJoinOptions(array $fetchOptions)
    {
        $joinOptions = parent::preparePostJoinOptions($fetchOptions);

        if (!empty($fetchOptions['dice']))
        {
            $joinOptions['selectFields'] .= ',dice_data';
            $joinOptions['joinTables'] .= ' LEFT JOIN xf_post_dice AS post_dice ON (post_dice.post_id = post.post_id)';
        }

        return $joinOptions;
    }

    public function getPermissionBasedPostFetchOptions(array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
    {
        $fetchOptions = parent::getPermissionBasedPostFetchOptions($thread, $forum, $nodePermissions, $viewingUser);

        if (!empty($thread['dice_count']))
        {
            $fetchOptions['dice'] = true;
        }

        return $fetchOptions;
    }

    public function mergePosts(array $posts, array $threads, $targetPostId, $newMessage, $options = array())
    {
        xfaDiceRoller_Globals::$mergeTargetId = $targetPostId;
        xfaDiceRoller_Globals::$contentIdsToMerge = null;
        if ($posts && isset($posts[$targetPostId]))
        {
            xfaDiceRoller_Globals::$contentIdsToMerge = array_keys($posts);
        }
        try
        {
            $ret = parent::mergePosts($posts, $threads, $targetPostId, $newMessage, $options);
        }
        catch(Exception $e)
        {
            xfaDiceRoller_Globals::$contentIdsToMerge = null;
            xfaDiceRoller_Globals::$mergeTargetId = null;
            throw $e;
        }
        xfaDiceRoller_Globals::$contentIdsToMerge = null;
        xfaDiceRoller_Globals::$mergeTargetId = null;
        return $ret;
    }
}

