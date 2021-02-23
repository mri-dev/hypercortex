function Calculator( action, form ) {
  jQuery.post(
    "/wp-admin/admin-ajax.php?action=calc_api_interface",
    {
      calculator: action,
      input: form
    },function(d){
      console.log(d);
    },
    'json'
  );
}

var $ = jQuery;

function subscriber()
{
  var form = $('form#subscriber').serialize();

  $('#subscriber .btns button').hide();
  /*
  $.post(
    //"/wp-admin/admin-ajax.php?action=subscriber",
    "https://www.hypercortex.hu/wp-admin/admin-ajax.php?action=subscriber",
    {
      form: form
    },function(d){
      console.log(d);

      if( d.data && d.data.subscribed && d.data.subscribed !== false) 
      {
        Cookies.set( 'hcwg_subscribed', d.data.subscribed, { expires: 365, path: '/' } );
        window.location.reload();
      }
    },
    'json'
  );
  */

  return false;
}

(function($)
{
  $(function(){
    autoResizeHeight();
  });
  
  function autoResizeHeight() {
  	jQuery.each($('.autocorrett-height-by-width'), function(i,e){
      var ew = $(e).width();
      var ap = $(e).data('image-ratio');
      var respunder = $(e).data('image-under');
  		var pw = $(window).width();
      ap = (typeof ap !== 'undefined') ? ap : '4:3';
      var aps = ap.split(":");
      var th = ew / parseInt(aps[0])  * parseInt(aps[1]);

  		if (respunder) {
  			if (pw < respunder) {
  				$(e).css({
  	        height: th
  	      });
  			}
  		} else{
  			$(e).css({
          height: th
        });
  		}
    });
  }
})(jQuery);
