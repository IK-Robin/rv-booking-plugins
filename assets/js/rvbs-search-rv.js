// load all post 

jQuery(document).ready(function($) {
  $('#load_more').click(function() {
    console.log( rvbs_serch_rv.ajax_url)
    var button = $(this),
      page = button.data('page'),
      ajaxurl = rvbs_serch_rv.ajax_url;

    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: rvbs_serch_rv.action,
        page: page
      },
      beforeSend: function() {
        button.text('Loading...'); // Change button text while loading
      },
      success: function(response) {
        if (response.trim() !== '') {
          $('#show_aval_room').append(response);
          button.data('page', page + 1);
          button.text('Load More');
          if(response == 'No more posts available'){
            button.remove();
          }
        } else {
          button.remove(); // Remove button when no more posts
        }
      }
    });
  });
});
