"use strict";
/**
 * SuperUser Login Handler
 * Separate login for SuperAdmin with role validation
 */
var KTSuperuserSignin = (function () {
	var form;
	var submitButton;
	var validation;

	var handleValidation = function (e) {
		validation = FormValidation.formValidation(form, {
			fields: {
				user_key: {
					validators: {
						notEmpty: { message: "Email/Username harus diisi" },
					},
				},
				password: {
					validators: { notEmpty: { message: "Password harus diisi" } },
				},
			},
			plugins: {
				trigger: new FormValidation.plugins.Trigger(),
				bootstrap: new FormValidation.plugins.Bootstrap5({
					rowSelector: ".fv-row",
					eleInvalidClass: "",
					eleValidClass: "",
				}),
			},
		});
	};

	var handleSubmit = function (e) {
		submitButton.addEventListener("click", function (e) {
			e.preventDefault();

			validation.validate().then(function (status) {
				if (status == "Valid") {
					submitButton.setAttribute("data-kt-indicator", "on");
					submitButton.disabled = true;

					var formData = new FormData(form);
					// Add flag to indicate superadmin login
					formData.append("superadmin_login", "1");
					
					axios
						.post(baseUrl + "api/auth/login", formData)
						.then(function (response) {
							if (response.data.status == "success") {
								// Check if user is actually a superadmin (role_id = 1)
								if (response.data.data.role_id != 1) {
									toastr.error(
										"Akses ditolak. Akun Anda bukan Super Admin.",
										"Gagal!",
										{
											timeOut: 5000,
											extendedTimeOut: 0,
											closeButton: true,
											closeDuration: 0,
										}
									);
									submitButton.removeAttribute("data-kt-indicator");
									submitButton.disabled = false;
									
									// Logout the non-superadmin user
									axios.post(baseUrl + "api/auth/logout");
									return;
								}
								
								toastr.success(response.data.message, "Login Berhasil", {
									extendedTimeOut: 0,
									closeButton: false,
									closeDuration: 0,
								});
								// Redirect to superuser dashboard
								window.location.href = baseUrl + "superuser/dashboard";
							} else {
								toastr.warning(response.data.message, "Gagal!", {
									timeOut: 5000,
									extendedTimeOut: 0,
									closeButton: false,
									closeDuration: 0,
								});
								submitButton.removeAttribute("data-kt-indicator");
								submitButton.disabled = false;
							}
						})
						.catch(function (error) {
							submitButton.removeAttribute("data-kt-indicator");
							submitButton.disabled = false;

							toastr.error(
								"System mengalami gangguan. Hubungi Administrator.",
								"Gagal!",
								{
									timeOut: 2000,
									extendedTimeOut: 0,
									closeButton: false,
									closeDuration: 0,
								}
							);
						});
				}
			});
		});
	};

	return {
		init: function () {
			form = document.querySelector("#kt_sign_in_form");
			submitButton = document.querySelector("#kt_sign_in_submit");

			handleValidation();
			handleSubmit();
		},
	};
})();
KTUtil.onDOMContentLoaded(function () {
	KTSuperuserSignin.init();
});
