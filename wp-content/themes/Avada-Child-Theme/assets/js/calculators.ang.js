var app = angular.module('Hypercortex', []);

/**
* Contact Form
**/
app.controller('ContactForm', ['$scope', '$http', function($scope, $http)
{
  $scope.loading = false;
  $scope.form = {};
  $scope.error = false;
  $scope.missing = [];
  $scope.error_elements = [];
  $scope.button_text = 'Üzenet küldése';
  $scope.button_class = 'grad-button';

  $scope.send = function()
  {
    $scope.loading = true;
    $scope.missing = [];
    $scope.error_elements = [];
    $scope.error = false;

    var form = {};
    angular.copy($scope.form, form);

    $http({
			method: 'POST',
			url: '/wp-admin/admin-ajax.php?action=contact_form',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			data: jQuery.param({
        form: form
			})
		}).then(function successCallback(r) {
      $scope.loading = false;
      var d = r.data;
      if (d.error == 0) {

      } else {
        $scope.error = d.msg;
      }
      console.log(r.data);
    }, function errorCallback(response){});
  }
}]);

/**
* Calculator
**/
app.controller('Calculators', ['$scope', '$http', function($scope, $http)
{
  $scope.loaded = false;
  $scope.loading = false;
  $scope.error = false;
  $scope.form = {};
  $scope.result = {};
  $scope.missing = [];
  $scope.error_elements = [];
  $scope.settings = {};

  $scope.init = function( calc ) {
    $scope.predefineFormSettings(calc);
  }

  $scope.calculate = function( view )
  {
    $scope.loaded = false;
    $scope.loading = true;
    $scope.missing = [];
    $scope.error_elements = [];
    $scope.error = false;

    var form = {};
    angular.copy($scope.form, form);

    if ( view == 'belepo_szabadsag' && form.iden_kezdett_dolgozni == 'Igen' && typeof $scope.form.belepes_datuma !== 'undefined' ) {
      var date = new Date($scope.form.belepes_datuma);
      var belepes = date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();
      form.belepes_datuma = belepes;
    } else {
      form.belepes_datuma = '';
    }

		$http({
			method: 'POST',
			url: '/wp-admin/admin-ajax.php?action=calc_api_interface',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			data: jQuery.param({
        calculator: view,
        input: form
			})
		}).then(function successCallback(r) {
      $scope.loading = false;
      var data = r.data.data;
      if (r.data.error == 1) {
        $scope.loaded = false;
        $scope.error = r.data.msg;
        $scope.result = {};
        if (r.data.missing_elements) {
          $scope.missing = r.data.missing_elements;
        }
        if (r.data.error_elements) {
          $scope.error_elements = r.data.error_elements;
        }
      } else {
        $scope.result = data;
        $scope.loaded = true;
        $scope.error = false;
      }
      console.log(r.data);
    }, function errorCallback(response) {
    });
	}

  $scope.predefineFormSettings = function( calc ) {
    switch (calc)
    {
      case 'netto_ber':
        $scope.settings.select_yesno = $scope.select_yesno();
        $scope.form.csaladkedvezmenyre_jogosult = 'Nem';
        $scope.form.frisshazas_jogosult = 'Nem';
        $scope.form.szemelyikedvezmeny_jogosult = 'Nem';
      break;
      case 'teljes_berkoltseg':
        $scope.settings.select_yesno = $scope.select_yesno();
        $scope.form.csaladkedvezmenyre_jogosult = 'Nem';
        $scope.form.frisshazas_jogosult = 'Nem';
        $scope.form.szemelyikedvezmeny_jogosult = 'Nem';
        $scope.form.ceg_kisvallalati_ado_alany = 'Nem';
      break;
      case 'belepo_szabadsag':
        $scope.settings.select_yesno = $scope.select_yesno();
        $scope.form.iden_kezdett_dolgozni = 'Nem';
        $scope.form.gyerek16ev_fiatalabb_fogyatekos = 'Nem';
        $scope.form.megvaltozott_munkakepessegu = 'Nem';
        $scope.form.athozott_szabadsagok = 0;
        $scope.form.gyerek16ev_fiatalabb = 0;
      break;
    }

    console.log($scope.form);
  }

  $scope.select_yesno = function() {
    var yn = [];

    yn[0] = 'Nem';
    yn[1] = 'Igen';

    return yn;
  }

}]);
app.filter('unsafe', function($sce){ return $sce.trustAsHtml; });
app.filter('cash', function(){
	return function(cash, text, aftertext){
		if (cash) {
			cash = cash.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
			if (typeof text === 'undefined' || text == 1) {
				if (typeof aftertext === 'undefined') {
					cash += " Ft + ÁFA";
				} else {
					cash += " "+aftertext;
				}
			} else {
        cash += " "+text+" "+aftertext;
      }
			return cash;
		} else {
			return '0'+" "+text+" "+aftertext;
		}
	};
});
app.filter('range', function() {
  return function(input, start, end) {
    start = parseInt(start);
    end = parseInt(end);
    var direction = (start <= end) ? 1 : -1;
    while (start != end) {
        input.push(start);
        start += direction;
    }
    return input;
  };
});
