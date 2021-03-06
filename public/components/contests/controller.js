tappyn.controller('contestsController', function($scope, $rootScope, $location, contestsFactory) {

    contestsFactory.grabAllContests().success(function(response) {
        var filter = [];
        var all_contests = response.data.contests;
        if ($rootScope.user && $rootScope.user.interests && all_contests) {
            if ($rootScope.user.show_all_contest == '1') {
                filter = all_contests;
            } else {
                for (var i = 0; i <= all_contests.length - 1; i++) {
                    for (var j = all_contests[i].industry.length - 1; j >= 0; j--) {
                        // interests
                        if ($.inArray(all_contests[i].industry[j], $rootScope.user.interests) != -1) {
                            // age
                            //if ($rootScope.user.age >= all_contests[i].min_age && $rootScope.user.age <= all_contests[i].max_age) {
                                // gender
                                if (all_contests[i].gender == '0' || all_contests[i].gender == $rootScope.user.gender) {
                                    filter.push(all_contests[i]);
                                    break;
                                }
                            //}
                        }
                    }
                }
            }
        }
        $scope.contests = filter;
        //$scope.contests = response.data.contests;
    });

    $scope.filter_industry = function(pass) {
        contestsFactory.filterGrab(pass).success(function(response) {
            $scope.contests = response.data.contests;
        })
    }

    $scope.grab_all = function() {
        $scope.tab = 'all';
        contestsFactory.grabAllContests().success(function(response) {
            $scope.contests = response.data.contests;
        });
    }

    $scope.grab_my = function() {
        $scope.tab = "my";
        contestsFactory.grabMyContests().success(function(response) {
            if (response.http_status_code == 200) {
                if (response.success) $scope.contests = response.data.contests;
                else $scope.set_alert(response.message, "default");
            } else if (response.http_status_code == 500) {
                $scope.set_alert(response.error, "default");
                $scope.adding_interests();
            } else $scope.check_code(response.http_status_code);
        })
    }


    $scope.to_account = function() {
        $scope.with_email = false;
        $scope.have_account = true;
        $scope.forgot = false;
    }

    $scope.email_regis = function() {
        $scope.with_email = true;
        $scope.have_account = false;
        $scope.forgot = false;
    }

    $scope.forgotten = function() {
        $scope.with_email = false;
        $scope.have_account = false;
        $scope.forgot = true;
    }
})
