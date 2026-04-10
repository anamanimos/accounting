<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    <?= $supplier ? 'Edit Supplier' : 'Tambah Supplier' ?>
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted"><a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted"><a href="<?= base_url('suppliers') ?>" class="text-muted text-hover-primary">Supplier</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted"><?= $supplier ? 'Edit' : 'Tambah' ?></li>
                </ul>
            </div>
            <a href="<?= base_url('suppliers') ?>" class="btn btn-sm fw-bold btn-light"><i class="ki-outline ki-arrow-left fs-5"></i> Kembali</a>
        </div>
    </div>
    
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="card">
                <div class="card-body">
                    <?php $action = $supplier ? base_url('suppliers/update/' . $supplier->id) : base_url('suppliers/store'); ?>
                    <form action="<?= $action ?>" method="post" class="form">
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label required fw-semibold fs-6">Kode</label>
                            <div class="col-lg-9">
                                <input type="text" name="kode" class="form-control form-control-solid" value="<?= set_value('kode', $kode) ?>" <?= $supplier ? 'readonly' : '' ?> required />
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label required fw-semibold fs-6">Nama</label>
                            <div class="col-lg-9">
                                <input type="text" name="nama" class="form-control form-control-solid" value="<?= set_value('nama', $supplier->nama ?? '') ?>" required />
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Alamat</label>
                            <div class="col-lg-9">
                                <textarea name="alamat" class="form-control form-control-solid" rows="3"><?= set_value('alamat', $supplier->alamat ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Telepon</label>
                            <div class="col-lg-9">
                                <input type="text" name="telepon" class="form-control form-control-solid" value="<?= set_value('telepon', $supplier->telepon ?? '') ?>" />
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Email</label>
                            <div class="col-lg-9">
                                <input type="email" name="email" class="form-control form-control-solid" value="<?= set_value('email', $supplier->email ?? '') ?>" />
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Status</label>
                            <div class="col-lg-9">
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= ($supplier->is_active ?? 1) ? 'checked' : '' ?> />
                                    <label class="form-check-label">Aktif</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-9 offset-lg-3">
                                <button type="submit" class="btn btn-primary me-2"><i class="ki-outline ki-check fs-5"></i> Simpan</button>
                                <a href="<?= base_url('suppliers') ?>" class="btn btn-light">Batal</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
