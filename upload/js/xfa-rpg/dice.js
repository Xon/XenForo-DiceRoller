!function($, window, document, _undefined)
{
    XenForo.DiceRollForm = function($form) {
        $form.bind('AutoValidationComplete', function(e) {
            var ajaxData = e.ajaxData;
            $('#post-' + ajaxData.postId + ' .messageMeta').before(ajaxData.templateHtml);
            $('#dice-' + ajaxData.postId + '-' + ajaxData.boxId).xfActivate();
        });
    };

    XenForo.ThrowAnotherDie = function($button) {
        $button.bind('click', function(e) {
            e.preventDefault();
            XenForo.ajax($button.data('url'), {}, function(ajaxData, textStatus) {
                if (ajaxData.error) {
                    XenForo.hasResponseError(ajaxData, textStatus);
                } else {
                    $('#dice-' + ajaxData.postId + '-' + ajaxData.boxId + ' .ThrowAnotherDie').before(ajaxData.templateHtml);
                    $('#diceLegendTotal-' + ajaxData.postId + '-' + ajaxData.boxId).html(ajaxData.total);
                }
            });
        });
    };
    XenForo.register('#DiceRollForm', 'XenForo.DiceRollForm');
    XenForo.register('button.ThrowAnotherDie', 'XenForo.ThrowAnotherDie');
}(jQuery, this, document);

