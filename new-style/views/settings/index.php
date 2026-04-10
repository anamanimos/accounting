<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Pengaturan</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Pengaturan</li>
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

            <form action="<?= base_url('settings/update') ?>" method="post">
                <div class="row g-5">
                    <!--begin::API Settings-->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="ki-outline ki-key fs-3 text-primary me-2"></i>
                                    API Configuration
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-6">
                                    <label class="form-label fw-semibold">Gemini API Key</label>
                                    <div class="input-group">
                                        <input type="password" name="gemini_api_key" id="api_key_input" 
                                            class="form-control form-control-solid" 
                                            value="<?= $settings['gemini_api_key'] ?? '' ?>" 
                                            placeholder="AIzaSy..." />
                                        <button type="button" class="btn btn-light-primary" id="btn_toggle_key">
                                            <i class="ki-outline ki-eye fs-5"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        Dapatkan API key dari <a href="https://aistudio.google.com/apikey" target="_blank">Google AI Studio</a>
                                    </div>
                                </div>

                                <?php if (!empty($settings['gemini_api_key'])): ?>
                                <div class="notice d-flex bg-light-success rounded border-success border border-dashed p-4">
                                    <i class="ki-outline ki-shield-tick fs-2tx text-success me-4"></i>
                                    <div class="d-flex flex-column">
                                        <span class="text-success fw-bold">API Key Configured</span>
                                        <span class="text-gray-700 fs-7">API key sudah tersimpan dan siap digunakan.</span>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-4">
                                    <i class="ki-outline ki-information-5 fs-2tx text-warning me-4"></i>
                                    <div class="d-flex flex-column">
                                        <span class="text-warning fw-bold">API Key Not Set</span>
                                        <span class="text-gray-700 fs-7">Masukkan API key untuk mengaktifkan fitur AI.</span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!--end::API Settings-->

                    <!--begin::Prompt Settings-->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="ki-outline ki-message-text fs-3 text-info me-2"></i>
                                    Prompt Template
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-5">
                                    <label class="form-label fw-semibold">Template untuk AI Input</label>
                                    <textarea name="prompt_template" class="form-control form-control-solid" rows="12"
                                        placeholder="Masukkan prompt template..."><?= $settings['prompt_template'] ?? file_get_contents(FCPATH . 'prompt.txt') ?></textarea>
                                    <div class="form-text">Prompt ini akan digunakan sebagai instruksi untuk AI saat memproses data.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Prompt Settings-->
                </div>

                <div class="mt-5">
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-outline ki-check fs-5"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->

<script>
document.getElementById('btn_toggle_key').addEventListener('click', function() {
    var input = document.getElementById('api_key_input');
    var icon = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('ki-eye');
        icon.classList.add('ki-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('ki-eye-slash');
        icon.classList.add('ki-eye');
    }
});
</script>
