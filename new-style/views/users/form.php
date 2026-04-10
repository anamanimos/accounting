<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    <?= $user_data ? 'Edit User' : 'Tambah User' ?>
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('users') ?>" class="text-muted text-hover-primary">Users</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted"><?= $user_data ? 'Edit' : 'Tambah' ?></li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="<?= base_url('users') ?>" class="btn btn-sm fw-bold btn-light">
                    <i class="ki-outline ki-arrow-left fs-5"></i> Kembali
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="card">
                <div class="card-body">
                    <?php 
                    $action = $user_data 
                        ? base_url('users/update/' . $user_data->id) 
                        : base_url('users/store');
                    ?>
                    <form action="<?= $action ?>" method="post" class="form">
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label required fw-semibold fs-6">Username</label>
                            <div class="col-lg-9">
                                <input type="text" name="username" class="form-control form-control-solid <?= form_error('username') ? 'is-invalid' : '' ?>" 
                                    value="<?= set_value('username', $user_data->username ?? '') ?>" required />
                                <?php if (form_error('username')): ?>
                                <div class="invalid-feedback"><?= form_error('username') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label required fw-semibold fs-6">Nama Lengkap</label>
                            <div class="col-lg-9">
                                <input type="text" name="nama_lengkap" class="form-control form-control-solid" 
                                    value="<?= set_value('nama_lengkap', $user_data->nama_lengkap ?? '') ?>" required />
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Email</label>
                            <div class="col-lg-9">
                                <input type="email" name="email" class="form-control form-control-solid" 
                                    value="<?= set_value('email', $user_data->email ?? '') ?>" />
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label <?= $user_data ? '' : 'required' ?> fw-semibold fs-6">Password</label>
                            <div class="col-lg-9">
                                <input type="password" name="password" class="form-control form-control-solid" 
                                    <?= $user_data ? '' : 'required' ?> minlength="6" />
                                <?php if ($user_data): ?>
                                <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
                                <?php else: ?>
                                <div class="form-text">Minimal 6 karakter.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label required fw-semibold fs-6">Level</label>
                            <div class="col-lg-9">
                                <select name="level" class="form-select form-select-solid" required>
                                    <option value="">-- Pilih Level --</option>
                                    <option value="super admin" <?= set_select('level', 'super admin', ($user_data->level ?? '') == 'super admin') ?>>Super Admin</option>
                                    <option value="admin" <?= set_select('level', 'admin', ($user_data->level ?? '') == 'admin') ?>>Admin</option>
                                    <option value="user" <?= set_select('level', 'user', ($user_data->level ?? '') == 'user') ?>>User</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Status</label>
                            <div class="col-lg-9">
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                        <?= ($user_data->is_active ?? 1) ? 'checked' : '' ?> />
                                    <label class="form-check-label">Aktif</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-9 offset-lg-3">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="ki-outline ki-check fs-5"></i> Simpan
                                </button>
                                <a href="<?= base_url('users') ?>" class="btn btn-light">Batal</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->
