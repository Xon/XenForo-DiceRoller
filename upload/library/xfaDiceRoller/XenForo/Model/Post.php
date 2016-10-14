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
            $post['canViewDice'] = $this->getViewDice($post, $thread, $forum, $nodePermissions, $viewingUser);
            $post['canThrowDie'] = $post['canViewDice'] && $this->getCanThrowDie($post, $thread, $forum, $nodePermissions, $viewingUser);
        }
        if (isset($thread['dice_count']) && !isset($post['dice_count']))
        {
            $post['thread_dice_count'] = $thread['dice_count'];
        }
        return $post;
    }

    public function getViewDice(array $post, array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
    {
        return XenForo_Permission::hasContentPermission($nodePermissions, 'viewDice');
    }

    public function getCanThrowDie(array $post, array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
    {
        if (empty($post['canEdit']))
        {
            return false;
        }

        if (!XenForo_Permission::hasContentPermission($nodePermissions, 'throwDie'))
        {
            return false;
        }

        return $post['canEdit'] && ($post['user_id'] == $viewingUser['userId']);
    }

    public function getAndMergeAttachmentsIntoPosts(array $posts)
    {
        $posts = parent::getAndMergeAttachmentsIntoPosts($posts);

        if (self::$diceRoller == null)
        {
            self::$diceRoller = XenForo_Application::getOptions()->cz_enable_die;
        }
        if (self::$diceRoller)
        {
            $postIds = array();
            foreach ($posts AS $postId => $post)
            {
                if (!empty($post['thread_dice_count']) && empty($post['isDeleted']) && !empty($post['canViewDice']))
                {
                    $postIds[] = $postId;
                }
            }

            if ($postIds)
            {
                $dice = $this->fetchAllKeyed('
                    SELECT post_id, dice_data
                    FROM xf_post_dice
                    WHERE post_id IN (' . $this->_getDb()->quote($postIds) . ')
                ', 'post_id');

                foreach ($dice AS $post_id => $die)
                {
                    if (!empty($die['dice_data']))
                    {
                        $dice_data = @unserialize($die['dice_data']);
                        if ($dice_data && is_array($dice_data))
                        {
                            $posts[$post_id]['dice_data'] = $dice_data;
                        }
                    }
                }
            }
        }

        return $posts;
    }
}

