!function($, window, document, _undefined)
{
    XenForo.DiceRollForm = function($form) {
        $form.bind('AutoValidationComplete', function(e) {
            var ajaxData = e.ajaxData;
             $('#post-' + ajaxData.postId + ' .messageMeta').before(ajaxData.template);
            $('#dice-' + ajaxData.postId + '-' + ajaxData.boxId).xfActivate();
        });
    };

    XenForo.ThrowNewDice = function($button) {
        var callback = function(ajaxData, textStatus) {
            if (ajaxData.error) {
                XenForo.hasResponseError(ajaxData, textStatus);
            } else {
                $('#dice-' + ajaxData.postId + '-' + ajaxData.boxId + ' .ThrowNewDice').before(ajaxData.template);
                $('#diceLegendTotal-' + ajaxData.postId + '-' + ajaxData.boxId)l.html(ajaxData.total);
            }
        };

        $button.bind('click', function(e) {
            e.preventDefault();
            XenForo.ajax($button.data('url'), {}, callback);
        });
    };

    XenForo.register('#DiceRollForm', 'XenForo.DiceRollForm');
    XenForo.register('button.ThrowNewDice', 'XenForo.ThrowNewDice');

}(jQuery, this, document);

