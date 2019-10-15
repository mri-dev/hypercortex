var app = angular.module('Hypercortex', []);

app.controller('Calculators', ['$scope', '$http', function($scope, $http)
{
  $scope.loaded = false;
  $scope.loading = false;
  $scope.error = false;
  $scope.form = {};
  $scope.result = {};

  $scope.calculate = function( view )
  {
    $scope.loaded = false;
    $scope.loading = true;
		$http({
			method: 'POST',
			url: '/wp-admin/admin-ajax.php?action=calc_api_interface',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			data: jQuery.param({
        calculator: view,
        input: $scope.form
			})
		}).then(function successCallback(r) {
      $scope.loading = false;
      var data = r.data.data;

      if (r.data.error == 1) {
        $scope.loaded = false;
        $scope.error = r.data.msg;
        $scope.result = {};
      } else {
        $scope.result = data;
        $scope.loaded = true;
        $scope.error = false;
      }
      console.log(r);
    }, function errorCallback(response) {
    });
	}

}]);

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
