if (typeof(RECAPTCHA_PUBLIC_KEY) != "undefined" && typeof(grecaptcha) != "undefined") {
	//console.log('start grecaptca!');
	grecaptcha.ready(function () {
		//console.log('recaptca ready!');
		grecaptcha.execute(RECAPTCHA_PUBLIC_KEY, {action: 'user_check'})
			.then(function (token) {
			// Verify the token on the server.
				RECAPTCHA_TOKEN = token;
				var ajaxParam = {
					type: "POST",
					url: "/bitrix/modules/x51.recaptchav3/assets/verify.php",
					data: {
						"token": token,
					},
					success: function (data) {
						if (typeof (recaptchaSuccess) == "function") {
							recaptchaSuccess(data);
						}
					},
					error: function (request, status) {
						if (typeof (recaptchaError) == "function") {
							recaptchaError(request, status);
						}
					},
					//timeout: _self.settings.timeout,
					dataType: "html"
				};
				jQuery.ajax(ajaxParam);
			// set token to form
				var forms = document.getElementsByTagName("form");
				if (forms.length > 0) {
					var etoken = document.createElement('input');
					etoken.type = "hidden";
					etoken.name = "g-recaptcha-token";
					etoken.value = token;
					for (var i=0; i<forms.length; i++) {
						forms[i].appendChild(etoken.cloneNode());
					}
				}				
		});
	});
};