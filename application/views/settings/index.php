<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Pengaturan Sistem (Settings)</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('home') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Control Panel</li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Pengaturan Sistem</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            <?php if ($this->session->flashdata('result_setting')) { ?>
                <div class="alert alert-dismissible bg-light-success d-flex flex-column flex-sm-row p-5 mb-10 border border-success border-dashed">
                    <i class="ki-outline ki-check-circle fs-2hx text-success me-4 mb-5 mb-sm-0"></i>
                    <div class="d-flex flex-column pe-0 pe-sm-10 justify-content-center">
                        <h5 class="mb-1 text-success">Berhasil!</h5>
                        <span><?php echo $this->session->flashdata('result_setting'); ?></span>
                    </div>
                    <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                        <i class="ki-outline ki-cross fs-1 text-success"></i>
                    </button>
                </div>
            <?php } ?>

            <!--begin::Card - AI OCR & Google Cloud Config-->
            <div class="card shadow-sm mb-8">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-outline ki-setting-3 fs-2x text-primary me-3"></i>
                            <div>
                                <h3 class="fw-bold text-gray-900 m-0">Pengaturan AI OCR & Google Cloud API</h3>
                                <span class="text-muted fs-7">Konfigurasi API Key, Provider OCR, dan Model Gemini</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <form action="<?php echo base_url(); ?>settings/simpan" method="post" id="formMainSettings">
                        
                        <div class="mb-7">
                            <label class="form-label required fw-bold text-gray-800 fs-6">Google Cloud / AI Studio API Key (Free Tier)</label>
                            <div class="input-group">
                                <input type="password" id="google_api_key" name="google_api_key" value="<?php echo htmlspecialchars($google_api_key); ?>" class="form-control form-control-solid" placeholder="Masukkan Google API Key (AIzaSy...)" />
                                <button type="button" class="btn btn-secondary" onclick="toggleKeyVisibility()">
                                    <i class="ki-outline ki-eye fs-2" id="eyeIcon"></i> Sembunyikan/Lihat
                                </button>
                            </div>
                            <div class="form-text text-muted mt-2">
                                <span class="badge badge-light-primary me-1">Free Tier</span>
                                Didapatkan dari Google AI Studio atau Google Cloud Console (Free Tier 1.500 RPD / 1.000 Vision OCR per bulan).
                            </div>
                        </div>

                        <div class="row mb-7">
                            <div class="col-md-6 mb-5 mb-md-0">
                                <label class="form-label required fw-bold text-gray-800 fs-6">Layanan OCR Provider</label>
                                <select name="ocr_provider" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                                    <option value="gemini_flash" <?php echo ($ocr_provider == 'gemini_flash') ? 'selected' : ''; ?>>Google Gemini 1.5 Flash (Gratis / AI Studio)</option>
                                    <option value="vision_api" <?php echo ($ocr_provider == 'vision_api') ? 'selected' : ''; ?>>Google Cloud Vision API (Gratis 1,000 unit/bln)</option>
                                    <option value="combined" <?php echo ($ocr_provider == 'combined') ? 'selected' : ''; ?>>Gabungan (Cloud Vision OCR + Gemini Structurer)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required fw-bold text-gray-800 fs-6">Model Gemini Default</label>
                                <select name="gemini_model" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                                    <option value="gemini-1.5-flash" <?php echo ($gemini_model == 'gemini-1.5-flash') ? 'selected' : ''; ?>>gemini-1.5-flash (Sangat Stabil & Cepat)</option>
                                    <option value="gemini-2.0-flash" <?php echo ($gemini_model == 'gemini-2.0-flash') ? 'selected' : ''; ?>>gemini-2.0-flash (Versi Terbaru)</option>
                                    <option value="gemini-1.5-pro" <?php echo ($gemini_model == 'gemini-1.5-pro') ? 'selected' : ''; ?>>gemini-1.5-pro (Akurasi Tinggi)</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end pt-5">
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-outline ki-check-circle fs-2"></i> Simpan Pengaturan Utama
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!--end::Card-->

            <!--begin::Card - Key-Value Table Manager-->
            <div class="card shadow-sm">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-outline ki-data fs-2x text-primary me-3"></i>
                            <div>
                                <h3 class="fw-bold text-gray-900 m-0">Tabel Settings (Key | Value)</h3>
                                <span class="text-muted fs-7">Manajemen Pasangan Konfigurasi Sistem Berbasis Kunci dan Nilai</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-0">
                    
                    <!-- Quick Add Section -->
                    <div class="bg-light-primary rounded p-6 mb-7 border border-primary border-dashed">
                        <h5 class="fw-bold text-gray-800 mb-4"><i class="ki-outline ki-plus-circle text-primary fs-3 me-1"></i> Tambah Setting Baru</h5>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <input type="text" id="new_kv_key" class="form-control form-control-solid" placeholder="Nama Key (misal: company_name)" />
                            </div>
                            <div class="col-md-5">
                                <input type="text" id="new_kv_val" class="form-control form-control-solid" placeholder="Nilai Value" />
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-primary w-100" onclick="addCustomKV()">
                                    <i class="ki-outline ki-plus fs-2"></i> Tambah Setting
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="tableSettingsKV">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="w-50px pe-2 text-center">No</th>
                                    <th class="min-w-200px">Key (Kunci)</th>
                                    <th class="min-w-300px">Value (Nilai)</th>
                                    <th class="text-end min-w-150px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                <?php 
                                if (!empty($all_settings)) {
                                    $no = 1;
                                    foreach ($all_settings as $st) {
                                ?>
                                    <tr id="row_<?php echo htmlspecialchars($st->key); ?>">
                                        <td class="text-center"><?php echo $no++; ?></td>
                                        <td>
                                            <span class="badge badge-light-primary fs-7 fw-bold px-3 py-2">
                                                <i class="ki-outline ki-key text-primary me-1 fs-7"></i>
                                                <?php echo htmlspecialchars($st->key); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-solid form-control-sm input-kv-val" data-key="<?php echo htmlspecialchars($st->key); ?>" value="<?php echo htmlspecialchars($st->value); ?>" />
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light-info me-2" onclick="updateKV('<?php echo htmlspecialchars($st->key); ?>')">
                                                <i class="ki-outline ki-check fs-4"></i> Update
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light-danger" onclick="deleteKV('<?php echo htmlspecialchars($st->key); ?>')">
                                                <i class="ki-outline ki-trash fs-4"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                <?php 
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">Belum ada data di tabel <code>settings</code>.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <!--end::Card-->

        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->

<script>
function toggleKeyVisibility() {
    var field = document.getElementById('google_api_key');
    if (field.type === "password") {
        field.type = "text";
    } else {
        field.type = "password";
    }
}

function addCustomKV() {
    var key = $('#new_kv_key').val().trim();
    var val = $('#new_kv_val').val().trim();
    
    if (!key) {
        Swal.fire('Peringatan', 'Silakan masukkan nama Key!', 'warning');
        return;
    }
    
    $.ajax({
        url: baseUrl + 'settings/simpan_kv',
        type: 'POST',
        data: { key: key, value: val },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                Swal.fire('Berhasil', 'Setting berhasil ditambahkan!', 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Terjadi kesalahan koneksi.', 'error');
        }
    });
}

function updateKV(key) {
    var val = $('input.input-kv-val[data-key="' + key + '"]').val();
    $.ajax({
        url: baseUrl + 'settings/simpan_kv',
        type: 'POST',
        data: { key: key, value: val },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                Swal.fire('Berhasil', 'Setting key "' + key + '" berhasil diperbarui!', 'success');
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Terjadi kesalahan koneksi.', 'error');
        }
    });
}

function deleteKV(key) {
    Swal.fire({
        title: 'Yakin hapus setting ini?',
        text: 'Key "' + key + '" akan dihapus dari tabel settings!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: baseUrl + 'settings/hapus_kv',
                type: 'POST',
                data: { key: key },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        $('#row_' + key).remove();
                        Swal.fire('Terhapus!', 'Setting "' + key + '" berhasil dihapus.', 'success');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan koneksi.', 'error');
                }
            });
        }
    });
}
</script>
