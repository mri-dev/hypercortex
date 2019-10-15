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
