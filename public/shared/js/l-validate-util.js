var LValidator = {
	validEmail: function (email) { 
		var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(email);
	},
	validTel: function(tel) {
		var re = /^\+?\d+$/gm;
		return re.test(tel);
	},
	validNumber: function(str) {
		var re = /^\+?\d+(\.\d+)?$/gm;
		return re.test(str);
	},
	validInt: function(str) {
		var re = /^\+?\d+$/gm;
		return re.test(str);
	},
	validDomain: function(str) {
		var re = /^[^-]([A-Za-z0-9-]{1,63}[^-]\.)+[A-Za-z]{2,6}$/gm;
		return re.test(str);
	}
};