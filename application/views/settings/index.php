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
                        
                        <div class="row mb-7">
                            <div class="col-md-6 mb-5 mb-md-0">
                                <label class="form-label required fw-bold text-gray-800 fs-6">Gemini API Key (Google AI Studio)</label>
                                <div class="input-group">
                                    <input type="password" id="gemini_api_key" name="gemini_api_key" value="<?php echo htmlspecialchars($gemini_api_key); ?>" class="form-control form-control-solid" placeholder="AIzaSy..." />
                                    <button type="button" class="btn btn-secondary" onclick="toggleKeyVisibility('gemini_api_key')">
                                        <i class="ki-outline ki-eye fs-2"></i>
                                    </button>
                                </div>
                                <div class="form-text text-muted mt-2">
                                    <span class="badge badge-light-primary me-1">Free Tier</span>
                                    Dibuat dari <a href="https://aistudio.google.com/app/apikey" target="_blank" class="fw-bold text-primary">Google AI Studio</a> (1.500 RPD / 15 RPM).
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-gray-800 fs-6">Google Cloud Vision API Key (Opsional)</label>
                                <div class="input-group">
                                    <input type="password" id="google_api_key" name="google_api_key" value="<?php echo htmlspecialchars($google_api_key); ?>" class="form-control form-control-solid" placeholder="AIzaSy..." />
                                    <button type="button" class="btn btn-secondary" onclick="toggleKeyVisibility('google_api_key')">
                                        <i class="ki-outline ki-eye fs-2"></i>
                                    </button>
                                </div>
                                <div class="form-text text-muted mt-2">
                                    <span class="badge badge-light-info me-1">Free Tier</span>
                                    Dibuat dari Google Cloud Console. Kosongkan jika sama dengan Gemini Key.
                                </div>
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
                                <select name="gemini_model" id="gemini_model_select" class="form-select form-select-solid">
                                    <option value="gemini-2.5-flash" <?php echo ($gemini_model == 'gemini-2.5-flash') ? 'selected' : ''; ?>>gemini-2.5-flash (⭐ Sangat Direkomendasikan - Cepat & Akurat)</option>
                                    <option value="gemini-2.0-flash" <?php echo ($gemini_model == 'gemini-2.0-flash') ? 'selected' : ''; ?>>gemini-2.0-flash (Flash Vision v2.0)</option>
                                    <option value="gemini-flash-latest" <?php echo ($gemini_model == 'gemini-flash-latest') ? 'selected' : ''; ?>>gemini-flash-latest (Alias Flash Terbaru)</option>
                                    <option value="gemini-2.5-pro" <?php echo ($gemini_model == 'gemini-2.5-pro') ? 'selected' : ''; ?>>gemini-2.5-pro (Akurasi Tinggi untuk Nota Kompleks)</option>
                                    <option value="gemini-3.6-flash" <?php echo ($gemini_model == 'gemini-3.6-flash') ? 'selected' : ''; ?>>gemini-3.6-flash (Versi 3.6 Flash)</option>
                                    <option value="gemini-3.5-flash" <?php echo ($gemini_model == 'gemini-3.5-flash') ? 'selected' : ''; ?>>gemini-3.5-flash (Versi 3.5 Flash)</option>
                                    <option value="gemini-2.0-flash-lite" <?php echo ($gemini_model == 'gemini-2.0-flash-lite') ? 'selected' : ''; ?>>gemini-2.0-flash-lite (Super Ringan & Cepat)</option>
                                    <option value="gemini-pro-latest" <?php echo ($gemini_model == 'gemini-pro-latest') ? 'selected' : ''; ?>>gemini-pro-latest (Pro Terbaru)</option>
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
                        <button type="button" class="btn btn-light-warning fw-bold" id="btnListModels" onclick="listModelsAPI()">
                            <i class="ki-outline ki-magnifier fs-2 me-1"></i> Cek Daftar Model Aktif
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
function toggleKeyVisibility(id) {
    var field = document.getElementById(id || 'gemini_api_key');
    if (field.type === "password") {
        field.type = "text";
    } else {
        field.type = "password";
    }
}

function testGeminiAPI() {
    var geminiKey = $('#gemini_api_key').val().trim();
    var googleKey = $('#google_api_key').val().trim();
    var model = $('select[name="gemini_model"]').val();
    
    $('#testResultBox').slideDown();
    $('#testStatusBadge').attr('class', 'badge badge-light-warning me-3 fs-6').text('Memproses...');
    $('#testStatusTitle').text('Menghubungi Google Gemini API (' + model + ')...');
    $('#testResultDetails').text('Mengirim cURL ping request...');
    
    $('#btnTestGemini').addClass('disabled');

    $.ajax({
        url: baseUrl + 'settings/test_gemini',
        type: 'POST',
        data: { gemini_api_key: geminiKey, google_api_key: googleKey, gemini_model: model },
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
    var googleKey = $('#google_api_key').val().trim();
    var geminiKey = $('#gemini_api_key').val().trim();
    
    $('#testResultBox').slideDown();
    $('#testStatusBadge').attr('class', 'badge badge-light-warning me-3 fs-6').text('Memproses...');
    $('#testStatusTitle').text('Menghubungi Google Cloud Vision API...');
    $('#testResultDetails').text('Mengirim cURL image annotation request...');
    
    $('#btnTestVision').addClass('disabled');

    $.ajax({
        url: baseUrl + 'settings/test_vision',
        type: 'POST',
        data: { google_api_key: googleKey, gemini_api_key: geminiKey },
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

function listModelsAPI() {
    var geminiKey = $('#gemini_api_key').val().trim();
    var googleKey = $('#google_api_key').val().trim();
    
    $('#testResultBox').slideDown();
    $('#testStatusBadge').attr('class', 'badge badge-light-warning me-3 fs-6').text('Memproses...');
    $('#testStatusTitle').text('Memeriksa Daftar Model Aktif di Google API...');
    $('#testResultDetails').text('Mengirim GET request ke /models...');
    
    $('#btnListModels').addClass('disabled');

    $.ajax({
        url: baseUrl + 'settings/list_models',
        type: 'POST',
        data: { gemini_api_key: geminiKey, google_api_key: googleKey },
        dataType: 'json',
        success: function(res) {
            $('#btnListModels').removeClass('disabled');
            if (res.success && res.models) {
                $('#testStatusBadge').attr('class', 'badge badge-light-success me-3 fs-6').text('BERHASIL (' + res.models.length + ' Model)');
                $('#testStatusTitle').text('Daftar Model yang Didukung API Key Anda (' + res.endpoint + ')');
                
                var out = "Daftar Model yang Aktif & Mendukung generateContent:\n\n";
                var $select = $('#gemini_model_select');
                var currentVal = $select.val();
                $select.empty();

                res.models.forEach(function(m, idx) {
                    out += (idx + 1) + ". " + m.name + " (" + m.displayName + ")\n";
                    var isSel = (m.name === currentVal || (idx === 0 && !currentVal)) ? 'selected' : '';
                    $select.append('<option value="' + m.name + '" ' + isSel + '>' + m.name + ' (' + m.displayName + ')</option>');
                });

                if (currentVal && $select.find('option[value="' + currentVal + '"]').length > 0) {
                    $select.val(currentVal);
                }

                $('#testResultDetails').text(out + "\n[INFO] Dropdown 'Model Gemini Default' di atas telah diperbarui secara otomatis!");
            } else {
                $('#testStatusBadge').attr('class', 'badge badge-light-danger me-3 fs-6').text('GAGAL');
                $('#testStatusTitle').text('Gagal Mengambil Daftar Model');
                $('#testResultDetails').text("Pesan Error:\n" + (res.error || 'Terjadi kesalahan') + (res.debug ? "\n\nRaw Debug:\n" + res.debug : ""));
            }
        },
        error: function(xhr) {
            $('#btnListModels').removeClass('disabled');
            $('#testStatusBadge').attr('class', 'badge badge-light-danger me-3 fs-6').text('ERROR');
            $('#testStatusTitle').text('Terjadi Kesalahan Server / Koneksi');
            $('#testResultDetails').text(xhr.responseText || 'Request gagal dijalankan.');
        }
    });
}
</script>
