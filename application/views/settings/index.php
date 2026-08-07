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
                                <i class="ki-outline ki-check-circle fs-2"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!--end::Card-->

            <!--begin::Card - Test API Connections-->
            <div class="card shadow-sm mb-8">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-outline ki-technology-4 fs-2x text-info me-3"></i>
                            <div>
                                <h3 class="fw-bold text-gray-900 m-0">Uji Coba & Testing API</h3>
                                <span class="text-muted fs-7">Verifikasi langsung koneksi Google Gemini API dan Google Cloud Vision OCR</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <div class="d-flex flex-wrap gap-4 mb-6">
                        <button type="button" class="btn btn-light-primary fw-bold" id="btnTestGemini" onclick="testGeminiAPI()">
                            <i class="ki-outline ki-technology fs-2 me-1"></i> Test Gemini API Connection
                        </button>
                        <button type="button" class="btn btn-light-info fw-bold" id="btnTestVision" onclick="testVisionAPI()">
                            <i class="ki-outline ki-scan-barcode fs-2 me-1"></i> Test Cloud Vision OCR API
                        </button>
                    </div>

                    <!-- Output Box -->
                    <div id="testResultBox" style="display:none;" class="p-5 rounded border border-dashed">
                        <div class="d-flex align-items-center mb-3">
                            <span id="testStatusBadge" class="badge me-3 fs-6"></span>
                            <span id="testStatusTitle" class="fw-bold fs-5 text-gray-800"></span>
                        </div>
                        <pre id="testResultDetails" class="bg-dark text-light p-4 rounded fs-7" style="max-height:300px;overflow:auto;margin:0;"></pre>
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

function testGeminiAPI() {
    var key = $('#google_api_key').val().trim();
    var model = $('select[name="gemini_model"]').val();
    
    $('#testResultBox').slideDown();
    $('#testStatusBadge').attr('class', 'badge badge-light-warning me-3 fs-6').text('Memproses...');
    $('#testStatusTitle').text('Menghubungi Google Gemini API (' + model + ')...');
    $('#testResultDetails').text('Mengirim cURL ping request...');
    
    $('#btnTestGemini').addClass('disabled');

    $.ajax({
        url: baseUrl + 'settings/test_gemini',
        type: 'POST',
        data: { google_api_key: key, gemini_model: model },
        dataType: 'json',
        success: function(res) {
            $('#btnTestGemini').removeClass('disabled');
            if (res.success) {
                $('#testStatusBadge').attr('class', 'badge badge-light-success me-3 fs-6').text('BERHASIL');
                $('#testStatusTitle').text(res.message);
                $('#testResultDetails').text("Respon dari Gemini API:\n" + res.response);
            } else {
                $('#testStatusBadge').attr('class', 'badge badge-light-danger me-3 fs-6').text('GAGAL');
                $('#testStatusTitle').text('Gagal Terhubung ke Gemini API');
                $('#testResultDetails').text("Pesan Error:\n" + res.error + (res.debug ? "\n\nRaw Debug:\n" + res.debug : ""));
            }
        },
        error: function(xhr) {
            $('#btnTestGemini').removeClass('disabled');
            $('#testStatusBadge').attr('class', 'badge badge-light-danger me-3 fs-6').text('ERROR');
            $('#testStatusTitle').text('Terjadi Kesalahan Server / Koneksi');
            $('#testResultDetails').text(xhr.responseText || 'Request gagal dijalankan.');
        }
    });
}

function testVisionAPI() {
    var key = $('#google_api_key').val().trim();
    
    $('#testResultBox').slideDown();
    $('#testStatusBadge').attr('class', 'badge badge-light-warning me-3 fs-6').text('Memproses...');
    $('#testStatusTitle').text('Menghubungi Google Cloud Vision API...');
    $('#testResultDetails').text('Mengirim cURL image annotation request...');
    
    $('#btnTestVision').addClass('disabled');

    $.ajax({
        url: baseUrl + 'settings/test_vision',
        type: 'POST',
        data: { google_api_key: key },
        dataType: 'json',
        success: function(res) {
            $('#btnTestVision').removeClass('disabled');
            if (res.success) {
                $('#testStatusBadge').attr('class', 'badge badge-light-success me-3 fs-6').text('BERHASIL');
                $('#testStatusTitle').text(res.message);
                $('#testResultDetails').text("Respon dari Cloud Vision API:\n" + res.response);
            } else {
                $('#testStatusBadge').attr('class', 'badge badge-light-danger me-3 fs-6').text('GAGAL');
                $('#testStatusTitle').text('Gagal Terhubung ke Cloud Vision API');
                $('#testResultDetails').text("Pesan Error:\n" + res.error + (res.debug ? "\n\nRaw Debug:\n" + res.debug : ""));
            }
        },
        error: function(xhr) {
            $('#btnTestVision').removeClass('disabled');
            $('#testStatusBadge').attr('class', 'badge badge-light-danger me-3 fs-6').text('ERROR');
            $('#testStatusTitle').text('Terjadi Kesalahan Server / Koneksi');
            $('#testResultDetails').text(xhr.responseText || 'Request gagal dijalankan.');
        }
    });
}
</script>
