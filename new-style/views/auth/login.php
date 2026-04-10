<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login - Sistem Akuntansi</title>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="<?= base_url('assets/icon.png') ?>" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="<?= base_url('assets/plugins/global/plugins.bundle.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url('assets/css/style.bundle.css') ?>" rel="stylesheet" type="text/css" />
    <style>
        #kt_logo { width: 400px; }
        @media (max-width: 768px) { #kt_logo { width: 250px; } }
    </style>
</head>
<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center bgi-no-repeat" style="background-color: #f5f8fa;">
    <script>
        var defaultThemeMode = "light";
        document.documentElement.setAttribute("data-bs-theme", defaultThemeMode);
    </script>
    
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <div class="d-flex flex-column flex-column-fluid flex-lg-row">
            <!--begin::Aside-->
            <div class="d-flex flex-center w-lg-50 pt-15 pt-lg-0 px-10">
                <div class="d-flex flex-center flex-lg-start flex-column">
                    <div class="mb-7 text-center">
                        <i class="ki-outline ki-book-open fs-5tx text-primary"></i>
                        <h1 class="text-primary fw-bolder mt-4">Sistem Akuntansi</h1>
                        <p class="text-gray-600 fs-5">Jurnal Umum - Buku Besar - Laporan Keuangan</p>
                    </div>
                </div>
            </div>
            <!--end::Aside-->
            
            <!--begin::Body-->
            <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12 p-lg-20">
                <div class="bg-body d-flex flex-column align-items-stretch flex-center rounded-4 w-md-500px p-20" style="box-shadow: 0 0 50px 0 rgba(0,0,0,.075);">
                    <div class="d-flex flex-center flex-column flex-column-fluid px-lg-10 pb-10">
                        <!--begin::Form-->
                        <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form">
                            <div class="text-center mb-11">
                                <h1 class="text-gray-900 fw-bolder mb-3">Selamat Datang</h1>
                                <div class="text-gray-500 fw-semibold fs-6">Silahkan masuk untuk melanjutkan</div>
                            </div>

                            <div class="fv-row mb-8">
                                <label for="user_key" class="d-flex align-items-center fs-5 fw-semibold mb-2">
                                    Username <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="user_key" id="user_key" autocomplete="off" 
                                    class="form-control form-control-solid form-control-lg" placeholder="Masukkan username" />
                            </div>

                            <div class="fv-row mb-8" data-kt-password-meter="true">
                                <label class="d-flex align-items-center fs-5 fw-semibold mb-2">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative mb-3">
                                    <input class="form-control form-control-lg form-control-solid" type="password" 
                                        name="password" autocomplete="off" placeholder="Masukkan password" />
                                    <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" data-kt-password-meter-control="visibility">
                                        <i class="ki-duotone ki-eye-slash fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                        <i class="ki-duotone ki-eye d-none fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    </span>
                                </div>
                            </div>

                            <div class="d-grid mb-10">
                                <button type="submit" id="kt_sign_in_submit" class="btn btn-primary btn-lg">
                                    <span class="indicator-label">Masuk</span>
                                    <span class="indicator-progress">Memproses...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    </span>
                                </button>
                            </div>
                        </form>
                        <!--end::Form-->
                    </div>
                    
                    <div class="d-flex flex-stack px-lg-10">
                        <div class="text-gray-500 text-center fw-semibold fs-6 w-100">
                            &copy; <?= date('Y') ?> Sistem Akuntansi
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Body-->
        </div>
    </div>

    <script>var hostUrl = "assets/"; var baseUrl = "<?= base_url(); ?>";</script>
    <script src="<?= base_url('assets/plugins/global/plugins.bundle.js') ?>"></script>
    <script src="<?= base_url('assets/js/scripts.bundle.js') ?>"></script>
    <script>
    "use strict";
    var KTSigninGeneral = (function () {
        var form;
        var submitButton;
        var validation;

        var handleValidation = function () {
            validation = FormValidation.formValidation(form, {
                fields: {
                    user_key: {
                        validators: { notEmpty: { message: "Username harus diisi" } }
                    },
                    password: {
                        validators: { notEmpty: { message: "Password harus diisi" } }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: ".fv-row",
                        eleInvalidClass: "",
                        eleValidClass: ""
                    })
                }
            });
        };

        var handleSubmit = function () {
            submitButton.addEventListener("click", function (e) {
                e.preventDefault();

                validation.validate().then(function (status) {
                    if (status == "Valid") {
                        submitButton.setAttribute("data-kt-indicator", "on");
                        submitButton.disabled = true;

                        var formData = new FormData(form);
                        axios
                            .post(baseUrl + "api/auth/login", formData)
                            .then(function (response) {
                                if (response.data.status == "success") {
                                    toastr.success(response.data.message, "Login Berhasil");
                                    setTimeout(function() {
                                        window.location.href = response.data.data.redirect;
                                    }, 1000);
                                } else {
                                    toastr.warning(response.data.message, "Login Gagal");
                                    submitButton.removeAttribute("data-kt-indicator");
                                    submitButton.disabled = false;
                                }
                            })
                            .catch(function (error) {
                                submitButton.removeAttribute("data-kt-indicator");
                                submitButton.disabled = false;
                                toastr.error("System error. Hubungi Administrator.", "Error");
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
            }
        };
    })();

    KTUtil.onDOMContentLoaded(function () {
        KTSigninGeneral.init();
    });

    // Password visibility toggle
    document.querySelectorAll('[data-kt-password-meter-control="visibility"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = this.closest('[data-kt-password-meter]').querySelector('input');
            var iconShow = this.querySelector('.ki-eye-slash');
            var iconHide = this.querySelector('.ki-eye');
            if (input.type === 'password') {
                input.type = 'text';
                iconShow.classList.add('d-none');
                iconHide.classList.remove('d-none');
            } else {
                input.type = 'password';
                iconShow.classList.remove('d-none');
                iconHide.classList.add('d-none');
            }
        });
    });
    </script>
</body>
</html>
