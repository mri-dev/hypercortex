var app = angular.module('Hypercortex', []);

app.controller('Calculators', ['$scope', '$http', function($scope, $http)
{
  $scope.loaded = false;
  $scope.loading = false;

  $scope.calculate = function( view, inputs )
  {
    $scope.loaded = false;
    $scope.loading = false;

    console.log(inputs);

    /*
		$http({
			method: 'POST',
			url: '/wp-admin/admin-ajax.php?action=app',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			data: jQuery.param({
        type: 'getSettings'
			})
		}).then(function successCallback(r) {
      console.log(r.data);
      if (r.data.data) {
        $scope.settings = r.data.data;
        $scope.loaded = true;
      }
    }, function errorCallback(response) {
    });*/
	}

}]);
