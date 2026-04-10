<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    <?= $rekening ? 'Edit Rekening' : 'Tambah Rekening' ?>
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('rekening') ?>" class="text-muted text-hover-primary">Rekening</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted"><?= $rekening ? 'Edit' : 'Tambah' ?></li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="<?= base_url('rekening') ?>" class="btn btn-sm fw-bold btn-light">
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
                    $action = $rekening ? base_url('rekening/update/' . $rekening->no_rek) : base_url('rekening/store');
                    ?>
                    <form action="<?= $action ?>" method="post" class="form">
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label required fw-semibold fs-6">Nomor Rekening</label>
                            <div class="col-lg-9">
                                <input type="text" name="no_rek" class="form-control form-control-solid <?= form_error('no_rek') ? 'is-invalid' : '' ?>" 
                                    value="<?= set_value('no_rek', $rekening->no_rek ?? '') ?>" 
                                    placeholder="Contoh: 111" required />
                                <?php if (form_error('no_rek')): ?>
                                <div class="invalid-feedback"><?= form_error('no_rek') ?></div>
                                <?php endif; ?>
                                <div class="form-text">Kode unik untuk rekening ini.</div>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label required fw-semibold fs-6">Nama Rekening</label>
                            <div class="col-lg-9">
                                <input type="text" name="nama_rek" class="form-control form-control-solid <?= form_error('nama_rek') ? 'is-invalid' : '' ?>" 
                                    value="<?= set_value('nama_rek', $rekening->nama_rek ?? '') ?>" 
                                    placeholder="Contoh: Kas" required />
                                <?php if (form_error('nama_rek')): ?>
                                <div class="invalid-feedback"><?= form_error('nama_rek') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Induk</label>
                            <div class="col-lg-9">
                                <input type="text" name="induk" class="form-control form-control-solid" 
                                    value="<?= set_value('induk', $rekening->induk ?? '') ?>" 
                                    placeholder="Kode rekening induk (opsional)" />
                                <div class="form-text">Kosongkan jika ini adalah rekening induk.</div>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Level</label>
                            <div class="col-lg-9">
                                <select name="level" class="form-select form-select-solid">
                                    <?php for ($i = 0; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>" <?= set_select('level', $i, ($rekening->level ?? 0) == $i) ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <div class="form-text">0 = Rekening utama, 1+ = Sub-rekening.</div>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Tipe</label>
                            <div class="col-lg-9">
                                <select name="tipe" class="form-select form-select-solid">
                                    <?php foreach ($tipe_options as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= set_select('tipe', $value, ($rekening->tipe ?? '') == $value) ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Status</label>
                            <div class="col-lg-9">
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                        <?= ($rekening->is_active ?? 1) ? 'checked' : '' ?> />
                                    <label class="form-check-label">Aktif</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-9 offset-lg-3">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="ki-outline ki-check fs-5"></i> Simpan
                                </button>
                                <a href="<?= base_url('rekening') ?>" class="btn btn-light">Batal</a>
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
