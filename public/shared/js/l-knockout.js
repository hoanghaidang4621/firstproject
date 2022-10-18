$(function () {
    ko.bindingHandlers.date = {
        init: function (element, valueAccessor, allBindingsAccessor, viewModel) {
            var jsonDate = valueAccessor();
            var ret;
            if (jsonDate == null) {
                ret = "...";
            }
            else {
                var value = new Date((parseInt(jsonDate.substr(6))));
                ret = value.getDate() + "/" + (value.getMonth() + 1) + "/" + value.getFullYear();
            }
            element.innerHTML = ret;
        },
        update: function (element, valueAccessor, allBindingsAccessor, viewModel) {
        }
    };
    ko.bindingHandlers.datetime = {
        init: function (element, valueAccessor, allBindingsAccessor, viewModel) {
            var jsonDate = valueAccessor();
            var ret;
            if (jsonDate == null) {
                ret = "...";
            }
            else {
                var value = new Date(parseInt(jsonDate.substr(6)));
                ret = value.getHours() + ":" + value.getMinutes() + ":" + value.getSeconds() + " " + value.getDate() + "/" + (value.getMonth() + 1) + "/" + value.getFullYear();
            }
            element.innerHTML = ret;
        },
        update: function (element, valueAccessor, allBindingsAccessor, viewModel) {
        }
    };
    ko.bindingHandlers.yesno = {
        init: function (element, valueAccessor, allBindingsAccessor, viewModel) {
        },
        update: function (element, valueAccessor, allBindingsAccessor, viewModel) {
			var state = valueAccessor();
			// Next, whether or not the supplied model property is observable, get its current value
        	var valueUnwrapped = ko.unwrap(state);
            var value = valueUnwrapped==true ? "Yes" : "No";
            element.innerHTML = value;
        }
    };
});