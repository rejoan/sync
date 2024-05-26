jQuery(document).ready(function () {
  jQuery('#sync_data').click(function(e){
    e.preventDefault();
    var admin_url = jQuery('#admin_url').val();
    var plugin_url = jQuery('#plugin_url').val();
      jQuery.ajax({
        url:  admin_url + 'admin-ajax.php',
        type: 'post',
        cache: false,
        context: this,
        timeout: 7000,
        dataType: 'json',
        data: {
          action: 'sync_external'
        },
        beforeSend: function () {
            jQuery('#button-section').append('<img style="position:absolute;" id="loader" width="60" src="'+plugin_url+'/sync/loader.gif"/>');
            jQuery(this).addClass('disabled');
            jQuery('p.btn-para').remove();
        },
        success: function (response) {
        jQuery(this).removeClass('disabled');
        jQuery('#loader').remove();
          if(response.code == 'empty'){
              jQuery('p.btn-para').remove();
              jQuery('<p class="btn-para">No New Data Found for Sync</p>').insertAfter('#button-section');
              return false;
          }
          location.reload();
        },
        error: function (xmlhttprequest, textstatus, message) {
            jQuery(this).removeClass('disabled');
            jQuery('#loader').remove();
        }
      });
  });

});