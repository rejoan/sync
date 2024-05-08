jQuery(document).ready(function () {
  jQuery('#sync_data').click(function(){
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
        },
        success: function (response) {
        jQuery(this).removeClass('disabled');
        jQuery('#loader').remove();
          if(response.code == 'empty'){
              jQuery('#button-section').next('p').remove();
              jQuery('<p>No New Data Found for Sync</p>').insertAfter('#button-section');
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