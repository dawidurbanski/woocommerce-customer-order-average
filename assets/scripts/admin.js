(function($) {
  $(document).ready(function() {
    $('.update-users-average-order-meta').on('click', function() {
      var ajaxProgress = $('.users-progress');
      var progressCurrent = ajaxProgress.find('.current');
      var total = ajaxProgress.find('.total').text();

      ajaxProgress.removeClass('hidden');

      var users = $(this).data('users');

      setTimeout(function() {
        $.each(users, function(index, userID) {
          $.when(update_user_ajax_call(userID, progressCurrent, total, ajaxProgress)).done(function(){
            // run one afer another (kinda synchronously)
          });
        });
      }, 1000);
    });
  });

  function update_user_ajax_call(userID, progressCurrent, total, ajaxProgress) {
    $.ajax({
      type: 'POST',
      url: ajax.url,
      data: {
        _ajax_nonce: $('.update-users-average-order-meta').data('ajax-nonce'),
        action: 'add-user-order-average',
        user_id: userID,
      },
      success: function() {
        var current = parseInt(progressCurrent.text());
        progressCurrent.text(current + 1);

        if (current + 1 >= total) {
          ajaxProgress.addClass('hidden');
          set_initial_user_meta_updated_flag();
        }
      },
    });
  }

  function set_initial_user_meta_updated_flag() {
    $.ajax({
      type: 'POST',
      url: ajax.url,
      data: {
        _ajax_nonce: $('.update-users-average-order-meta').data('ajax-nonce'),
        action: 'users-updated-flag',
      },
      success: function() {
        $('.notice-error').remove();
      },
    });
  }
})(jQuery);
