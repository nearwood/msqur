app.controller("UploadController", ['$scope', '$rootScope', 'uploadManager', 
    function($scope, $rootScope, uploadManager)
    {
		$scope.files = [];
		$scope.percentage = 0;

		$scope.upload = function() {
			uploadManager.upload();
			$scope.files = [];
		};

		$rootScope.$on('fileAdded', function(e, call) {
			$scope.files.push(call);
			$scope.$apply();
		});

		$rootScope.$on('uploadProgress', function(e, call) {
			$scope.percentage = call;
			$scope.$apply();
		});
	}
]);
