<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Profil Saya</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Profil</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                <i class="ki-outline ki-shield-tick fs-2hx text-success me-4"></i>
                <div class="d-flex flex-column"><span><?= $this->session->flashdata('success') ?></span></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                <i class="ki-outline ki-shield-cross fs-2hx text-danger me-4"></i>
                <div class="d-flex flex-column"><span><?= $this->session->flashdata('error') ?></span></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="row g-5">
                <!--begin::Profile Card-->
                <div class="col-lg-4">
                    <div class="card mb-5 mb-xl-8">
                        <div class="card-body pt-15">
                            <div class="d-flex flex-center flex-column mb-5">
                                <div class="symbol symbol-100px symbol-circle mb-7">
                                    <div class="symbol-label fs-1 fw-bolder bg-primary text-inverse-primary">
                                        <?= generate_initials($user_data->nama_lengkap ?? 'User') ?>
                                    </div>
                                </div>
                                <span class="fs-3 text-gray-800 fw-bold mb-1"><?= $user_data->nama_lengkap ?></span>
                                <span class="fs-5 fw-semibold text-gray-500 mb-3">@<?= $user_data->username ?></span>
                                <span class="badge badge-lg badge-light-primary d-inline"><?= ucfirst($user_data->level) ?></span>
                            </div>
                            <div class="d-flex flex-stack fs-4 py-3">
                                <div class="fw-bold">
                                    <span class="d-block text-gray-500 fs-7">Email</span>
                                    <?= $user_data->email ?: '-' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Profile Card-->

                <!--begin::Edit Forms-->
                <div class="col-lg-8">
                    <!--begin::Edit Profile-->
                    <div class="card mb-5">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ki-outline ki-user fs-3 me-2"></i> Edit Profil</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('profile/update') ?>" method="post">
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nama Lengkap</label>
                                    <div class="col-lg-8">
                                        <input type="text" name="nama_lengkap" class="form-control form-control-solid" 
                                            value="<?= set_value('nama_lengkap', $user_data->nama_lengkap) ?>" required />
                                    </div>
                                </div>
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Email</label>
                                    <div class="col-lg-8">
                                        <input type="email" name="email" class="form-control form-control-solid" 
                                            value="<?= set_value('email', $user_data->email) ?>" required />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-8 offset-lg-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ki-outline ki-check fs-5"></i> Simpan Perubahan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--end::Edit Profile-->

                    <!--begin::Change Password-->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ki-outline ki-lock fs-3 me-2"></i> Ganti Password</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('profile/change_password') ?>" method="post">
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Password Lama</label>
                                    <div class="col-lg-8">
                                        <input type="password" name="current_password" class="form-control form-control-solid" required />
                                    </div>
                                </div>
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Password Baru</label>
                                    <div class="col-lg-8">
                                        <input type="password" name="new_password" class="form-control form-control-solid" minlength="6" required />
                                        <div class="form-text">Minimal 6 karakter.</div>
                                    </div>
                                </div>
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Konfirmasi Password</label>
                                    <div class="col-lg-8">
                                        <input type="password" name="confirm_password" class="form-control form-control-solid" required />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-8 offset-lg-4">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="ki-outline ki-lock fs-5"></i> Ganti Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--end::Change Password-->
                </div>
                <!--end::Edit Forms-->
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->
