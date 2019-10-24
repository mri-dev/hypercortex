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
(function($){
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
