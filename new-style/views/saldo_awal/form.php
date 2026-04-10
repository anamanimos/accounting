<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    <?= $saldo ? 'Edit Saldo Awal' : 'Tambah Saldo Awal' ?>
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('saldo_awal') ?>" class="text-muted text-hover-primary">Saldo Awal</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted"><?= $saldo ? 'Edit' : 'Tambah' ?></li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="<?= base_url('saldo_awal') ?>" class="btn btn-sm fw-bold btn-light">
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
                    $action = $saldo ? base_url('saldo_awal/update/' . $saldo->id) : base_url('saldo_awal/store');
                    ?>
                    <form action="<?= $action ?>" method="post" class="form">
                        <?php if (!$saldo): ?>
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label required fw-semibold fs-6">Periode</label>
                            <div class="col-lg-9">
                                <input type="number" name="periode" class="form-control form-control-solid" 
                                    value="<?= set_value('periode', date('Y')) ?>" min="2000" max="2099" required />
                                <div class="form-text">Tahun periode saldo awal (biasanya tahun sebelumnya).</div>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label required fw-semibold fs-6">Rekening</label>
                            <div class="col-lg-9">
                                <select name="no_rek" class="form-select form-select-solid" required>
                                    <option value="">-- Pilih Rekening --</option>
                                    <?php foreach ($rekening as $kode => $nama): ?>
                                    <option value="<?= $kode ?>" <?= set_select('no_rek', $kode) ?>><?= $nama ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Periode</label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control form-control-solid" value="<?= $saldo->periode ?>" readonly />
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Rekening</label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control form-control-solid" 
                                    value="<?= $saldo->no_rek ?> - <?= $rekening[$saldo->no_rek] ?? '' ?>" readonly />
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Debet</label>
                            <div class="col-lg-9">
                                <input type="text" name="debet" class="form-control form-control-solid input-rupiah text-end" 
                                    value="<?= set_value('debet', number_format($saldo->debet ?? 0, 0, ',', '.')) ?>" />
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Kredit</label>
                            <div class="col-lg-9">
                                <input type="text" name="kredit" class="form-control form-control-solid input-rupiah text-end" 
                                    value="<?= set_value('kredit', number_format($saldo->kredit ?? 0, 0, ',', '.')) ?>" />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-9 offset-lg-3">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="ki-outline ki-check fs-5"></i> Simpan
                                </button>
                                <a href="<?= base_url('saldo_awal') ?>" class="btn btn-light">Batal</a>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.input-rupiah').forEach(function(el) {
        el.addEventListener('input', function() {
            var value = this.value.replace(/\D/g, '');
            this.value = new Intl.NumberFormat('id-ID').format(value);
        });
    });
});
</script>
