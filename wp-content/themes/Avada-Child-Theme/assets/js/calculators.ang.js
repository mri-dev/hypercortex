var app = angular.module('Hypercortex', []);

/**
* Contact Form
**/
app.controller('ContactForm', ['$scope', '$http', function($scope, $http)
{
  $scope.loading = false;
  $scope.form = {};
  $scope.error = false;
  $scope.success = false;
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
        $scope.success = d.msg;
        $scope.form = {};
      } else {
        $scope.error = d.msg;
        if (d.missing_elements) {
          $scope.missing = d.missing_elements;
        }
        if (d.error_elements) {
          $scope.error_elements = d.error_elements;
        }
      }
      console.log(r.data);
    }, function errorCallback(response){});
  }
}]);

/**
* Calculator
**/
app.controller('Calculators', ['$scope', '$http', '$locale', function($scope, $http, $locale)
{
  $scope.loaded = false;
  $scope.loading = false;
  $scope.error = false;
  $scope.form = {};
  $scope.result = {};
  $scope.missing = [];
  $scope.error_elements = [];
  $scope.settings = {};
  $locale.NUMBER_FORMATS.GROUP_SEP = ' ';

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

    if ( view == 'anyak_szabadsaga' )
    {
      if ( typeof $scope.form.munkaviszony_kezedete !== 'undefined') {
        var date = new Date($scope.form.munkaviszony_kezedete);
        var munkaviszony_kezedete = date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();
        form.munkaviszony_kezedete = munkaviszony_kezedete;
      }

      if ( typeof $scope.form.szules_ideje !== 'undefined') {
        var date = new Date($scope.form.szules_ideje);
        var szules_ideje = date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();
        form.szules_ideje = szules_ideje;
      }

      if ( typeof $scope.form.csed_kezdete !== 'undefined') {
        var date = new Date($scope.form.csed_kezdete);
        var csed_kezdete = date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();
        form.csed_kezdete = csed_kezdete;
      }

      if ( typeof $scope.form.gyedgyes_kezdete !== 'undefined') {
        var date = new Date($scope.form.gyedgyes_kezdete);
        var gyedgyes_kezdete = date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();
        form.gyedgyes_kezdete = gyedgyes_kezdete;
      }
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
        $scope.form.gyerek16ev_fiatalabb_fogyatekos = 'Nem';
        $scope.form.megvaltozott_munkakepessegu = 'Nem';
        $scope.form.athozott_szabadsagok = 0;
        $scope.form.gyerek16ev_fiatalabb = 0;
      break;

      case 'anyak_szabadsaga':
        $scope.settings.potszabigyermek = $scope.select_potszabigyermek();
        $scope.settings.select_yesno = $scope.select_yesno();
        $scope.form.gyerek16ev_fiatalabb_fogyatekos = 'Nem';
        $scope.form.athozott_szabadsagok = 0;
        $scope.form.gyerek16ev_fiatalabb = 0;
        $scope.form.szulev_igenybevett_szabadsag = 0;
        $scope.form.szul_elott_igenybevett_potszabadsag_gyermek = '0';

        //$scope.form.szuletesi_ev = 1990;

      break;
      case 'ingatlan_ertekesites':
        //$scope.form.atruhazas_eve = 2019;
        //$scope.form.szerzes_eve = 2019;
        //$scope.form.atruhazasbol_bevetel = 0;
        //$scope.form.megszerzes_osszeg = 0;
        $scope.form.megszerzes_egyeb_kiadas = 0;
        $scope.form.erteknovelo_beruhazasok = 0;
        $scope.form.erteknovelo_beruhazasok_allammegovas = 0;
        $scope.form.atruhazas_koltsegei = 0;
      break;
      case 'osztalekado':
        $scope.settings.select_yesno = $scope.select_yesno();
        $scope.form.osztalek_kifizetes = 'Igen';
        $scope.form.osztalekeloleg_kifizetes = 'Nem';

        $scope.$watch('form.osztalek_kifizetes', function(n, o, s) {
          if (n == 'Igen' && s.form.osztalekeloleg_kifizetes == 'Igen') {
            s.form.osztalekeloleg_kifizetes = 'Nem';
          }
          if (n == 'Nem' && s.form.osztalekeloleg_kifizetes == 'Nem') {
            s.form.osztalekeloleg_kifizetes = 'Igen';
          }
        });

        $scope.$watch('form.osztalekeloleg_kifizetes', function(n, o, s) {
          if (n == 'Igen' && s.form.osztalek_kifizetes == 'Igen') {
            s.form.osztalek_kifizetes = 'Nem';
          }
          if (n == 'Nem' && s.form.osztalek_kifizetes == 'Nem') {
            s.form.osztalek_kifizetes = 'Igen';
          }
        });
      break;
      case 'cafeteria':
        $scope.settings.select_yesno = $scope.select_yesno();
        $scope.settings.cafateria_jutattasok = $scope.cafateria_jutattasok();
        $scope.form.ceg_kiva = 'Nem';
      break;
      case 'brutto_ber':
        $scope.settings.select_yesno = $scope.select_yesno();
        $scope.form.csaladkedvezmenyre_jogosult = 'Nem';
        $scope.form.frisshazas_jogosult = 'Nem';
        $scope.form.szemelyikedvezmeny_jogosult = 'Nem';
      break;
    }

    console.log($scope.form);
  }

  $scope.select_potszabigyermek = function() {
    var yn = [];

    yn[0] = '0';
    yn[1] = '1';
    yn[2] = '2';
    yn[3] = '3 vagy több';

    return yn;
  }

  $scope.select_yesno = function() {
    var yn = [];

    yn[0] = 'Nem';
    yn[1] = 'Igen';

    return yn;
  }

  $scope.cafateria_jutattasok = function() {
    var yn = [];

    yn.push('Számítógéphasználat');
    yn.push('Iskolarendszeren kívüli oktatás támogatása');
    yn.push('Bőlcsödei, óvodai szolgáltatás');
    yn.push('Bőlcsödei, óvodai étkeztetés');
    yn.push('Sportrendezvényre szóló belépőjegy, bérlet');
    yn.push('Kulturális szolgáltatásra szóló belépőjegy, bérlet');
    yn.push('Munkaruházat');
    yn.push('Védőoltás');

    yn.push('SZÉP kártya vendéglátás');
    yn.push('SZÉP kártya szálláshely');
    yn.push('SZÉP kártya szabadidő');

    yn.push('Önkéntes kölcsönös biztosítópénztár célzott szolgáltatásra befizetett összeg');
    yn.push('Csekély értékű ajándék');
    yn.push('Munkavállalónak juttatott hivatali, üzleti utazáshoz kapcsolódó étkezés vagy más szolgáltatás');

    yn.push('Erzsébet utalvány');
    yn.push('Helyi utazási bérlet');
    yn.push('Mobilitási célú lakhatási támogatás');
    yn.push('Adóköteles biztosítási díj');
    yn.push('Kockázati biztosítás');
    yn.push('Iskolarendszerű oktatás támogatása');
    yn.push('Diákhitel támogatása');
    yn.push('Lakáscélú támogatás');
    yn.push('Munkahelyi étkeztetés');
    yn.push('Iskolakezdési támogatás');
    yn.push('Üdülési szolgáltatás');

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
app.directive('inputThousandSeparator', [
  function() {
    return {
      restrict: 'A',
      require: 'ngModel',
      link: function(scope, element, attr, ngModel) {

        var viewValue, noCommasVal;
        var numberMode = attr['inputThousandSeparator'];

        var currencyReg = /^(?!0+\.00)(?=.{1,9}(\.|$))(?!0(?!\.))\d{1,3}(,\d{3})*(\.[0-9]{2})?$/;
        var percentageReg = /(^100([.]0{1,2})?)$|(^\d{1,2}([.]\d{1,2})?)$/;
        var wholeNosReg = /^(?=.{1,9}(\.|$))(?!0(?!\.))\d{1,3}(,\d{3})?$/;

        function testValue(value) {
          switch(numberMode) {
            case 'currency':
              ngModel.$setValidity('pattern',currencyReg.test(value));
              break;

            case 'percentage':
              ngModel.$setValidity('pattern',percentageReg.test(value));
              break;

            case 'whole':
              ngModel.$setValidity('pattern',wholeNosReg.test(value));
              break;
          }
        }

        function setThousandSeperator(value) {
          if (value) {
            noCommasVal = value.toString().replace(/ /g, '');
            viewValue = noCommasVal.replace(/\B(?=(\d{3})+(?!\d))/g, " ");
            ngModel.$setViewValue(viewValue);
            ngModel.$render();
          }
        }

        ngModel.$parsers.push(function(value) {
          if (!value) {
            ngModel.$setValidity('pattern',true);
          } else {
            testValue(value);
            setThousandSeperator(value);
            return noCommasVal;
          }
        });
        ngModel.$formatters.push(function(value) {
          if (!value) {
            ngModel.$setValidity('pattern',true);
            return value;
          } else {
            testValue(value);
            setThousandSeperator(value);
            return viewValue;
          }
        });
      }
    };
  }
]);
