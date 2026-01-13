app.run(function ($rootScope, $window, ClientService, $timeout) {
    
    $rootScope.LOG_STATUS = [
        {"val":-1, "name": "In-queue"},
        {"val":0, "name": "Downloaded"},
        {"val":1, "name": "Partial"},
        {"val":2, "name": "Failed"}
    ];
    $rootScope.getLogStatus = function(status) {
        if( status === -1 ) return "In-queue";
        else if( status === 0 ) return "Downloaded";
        else if( status === 1 ) return "Partial";
        else if( status === 2 ) return "Failed";
        else return "Unknown";
    };
    
    $rootScope.hasPermission = function (module, component) {
        return ClientService.hasPermission(module, null, component);
    };
    $rootScope.featureName = function (module, component) {
        return ClientService.featureName(module, component);
    };
    $rootScope.setPageName = function (module, component) {
        $rootScope.page_name = ClientService.featureName(module, component);
        angular.element('#page_name').html($rootScope.page_name);
    };
    $rootScope.toastSuccess = function (messaage) {
        $rootScope.toast(1, messaage);
    };
    $rootScope.toastError = function (messaage) {
        $rootScope.toast(0, messaage);
    };
    $rootScope.toastWarning = function (messaage) {
        $rootScope.toast(2, messaage);
    };
    $rootScope.toast = function (type, messaage) {
        angular.element(type === 1 ? '#operation_scs_alert' : '#operation_dng_alert').removeClass('hide');
        angular.element(type === 1 ? '#operation_scs_msg' : '#operation_dng_msg').html(messaage);
        angular.element(type === 1 ? '#operation_dng_alert' : '#operation_scs_alert').addClass('hide');
        $timeout(function () {
            angular.element('div.alert').addClass('hide');
        }, TOAST_TIMEOUT);
    };

    $rootScope.log = function (tag, messaage) {
        ClientService.log(tag, messaage);
    };

});

app.directive('sarkerPagination', function () {
    return {
        restrict: 'EA',
        scope: {
            itemCount: '=',
            itemPerPage: '=',
            selectedPage: '=',
            linkCount: '=',
            getItems: '&'
        },
        controller: function ($scope) {
            $scope.showCurrentPageItems = function (pageNum) {
                $scope.getItems({pageNumber: pageNum, itemPerPage: $scope.itemPerPage});
            };
            $scope.isShowalble = function (selectedPage, buttonSL, totalLink) {
                if (selectedPage === buttonSL) return true;
                var showing = 7 / 2, start, end;
                start = selectedPage - showing;
                end = selectedPage + showing;
                if (start < 1) {
                    if (start === 0)
                        start = -1;
                    end += (start * -1);
                    start = 1;
                }
                if (end > totalLink) {
                    start -= (end - totalLink);
                    end = totalLink;
                }
                if (start < 1) {start = 1;}
                if (end > totalLink) {end = totalLink;}
                if (buttonSL >= start && buttonSL <= end) {return true;}
                return false;
            };
        },
        template: '<ul class="pagination pagination-md" style="float:left;margin: 10px 5px;" >\n\
                        <li><a href ng-click="showCurrentPageItems(1)"><i class="fa fa-step-backward"></i></a></li>\n\
                        <li ng-if="selectedPage===1" class="disabled" ><a><i class="fa fa-caret-left"></i></a></li>\n\
                        <li ng-if="selectedPage>1" ><a href ng-click="showCurrentPageItems( selectedPage-1 )"><i class="fa fa-caret-left"></i></a></li>\n\
                        <li ng-repeat="n in [].constructor( (itemCount/itemPerPage) | ceil ) track by $index" ng-class="{\'active\':selectedPage===($index+1)}" ng-if="isShowalble( selectedPage, ($index+1), ((itemCount/itemPerPage) | ceil) ) ">\n\
                            <a href ng-click="showCurrentPageItems($index+1)">{{ $index+1 }}</a>\n\
                        </li>\n\
                        <li ng-if="selectedPage===((itemCount/itemPerPage) | ceil)" class="disabled"><a><i class="fa fa-caret-right"></i></a></li>\n\
                        <li ng-if="selectedPage<((itemCount/itemPerPage) | ceil)"><a href ng-click="showCurrentPageItems( selectedPage+1 )"><i class="fa fa-caret-right"></i></a></li>\n\
                        <li><a href ng-click="showCurrentPageItems( (itemCount/itemPerPage) | ceil )"><i class="fa fa-step-forward"></i></a></li>\n\
                    </ul><span style="float:left;margin: 10px 5px;line-height: 35px;">Showing {{itemPerPage*(selectedPage-1) + 1}} - {{itemPerPage*(selectedPage-1) + itemPerPage}} of {{itemCount}} records<span>'
    };
});
app.filter('ceil', function () {return function (input) {return Math.ceil(input);};});
app.directive('onFinishRender', function ($timeout) {
    return {
        restrict: 'A',
        link: function (scope, element, attr) {
            if (scope.$last === true) {
                $timeout(function () {scope.$emit(attr.onFinishRender);});
            }
        }
    };
});

app.directive('pressEnter', function () {
    return function (scope, element, attrs) {
        element.bind("keypress", function (event) {
            if (event.which === 13) {scope.$apply(function () {scope.$eval(attrs.pressEnter);});event.preventDefault();}
        });
    };
});

app.directive('dateTimePicker', function () {
    return {
        restrict: "EA",
        require: "ng-model",
        scope:{
            minDate: "=mindate"
        },
        link: function (scope, element, attrs, ngModelCtrl) {
            var parent = $(element).parent();
            var dtp;
            
            if( scope.minDate ){
                dtp = parent.datetimepicker({
                    minDate: scope.minDate,
                    format: "YYYY-MM-DD HH:mm",
                    showTodayButton: false,
                    sideBySide: true,
                    stepping: 5,
                    useStrict:true,
                    //date: moment(new Date()).format("YYYY-MM-DD HH:mm"),
                    useCurrent: false
                });
            } else{
                dtp = parent.datetimepicker({
                    format: "YYYY-MM-DD HH:mm",
                    showTodayButton: false,
                    sideBySide: true,
                    stepping: 5,
                    useStrict:true,
                    //date: moment(new Date()).format("YYYY-MM-DD HH:mm"),
                    useCurrent: false
                });
            }
            
            dtp.on("dp.change", function (e) {
                if (e.oldDate === null) {
                    var d = moment(e.date).format("YYYY-MM-DD") + ' ' + moment(new Date()).format("HH:mm");
                    $(this).data('DateTimePicker').date(moment(d).format("YYYY-MM-DD HH:mm"));
                    ngModelCtrl.$setViewValue(moment(d).format("YYYY-MM-DD HH:mm"));
                } else {
                    ngModelCtrl.$setViewValue(moment(e.date).format("YYYY-MM-DD HH:mm"));
                }
                scope.$apply();
            });
        }
    };
});
app.directive('datePicker', function () {
    return {
        restrict: "EA",
        require: "ng-model",
        link: function (scope, element, attrs, ngModelCtrl) {
            var parent = $(element).parent();
            var dtp = parent.datetimepicker({
                format: "YYYY-MM-DD",
                showTodayButton: false,
                sideBySide: false,
                 useStrict:true,
                useCurrent: false
            });
            dtp.on("dp.change", function (e) {
                ngModelCtrl.$setViewValue(moment(e.date).format("YYYY-MM-DD"));
                scope.$apply();
            });
        }
    };
});

app.directive('ngFile', ['$parse', function ($parse) {
        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                element.bind('change', function () {
                    $parse(attrs.ngFile).assign(scope, element[0].files);
                    scope.$apply();
                });
            }
        };
    }]);

app.directive('fileModel', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            var model = $parse(attrs.fileModel);
            var modelSetter = model.assign;
            element.bind('change', function () {
                scope.$apply(function () {modelSetter(scope, element[0].files[0]);});
            });
        }
    };
}]);
