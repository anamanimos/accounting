<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Manajemen WhatsApp</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('home') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">WhatsApp</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            
            <div class="card shadow-sm">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Status Koneksi WA Gateway</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Device ID: <strong><?= htmlspecialchars($device_id) ?></strong></span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="<?= base_url('whatsapp') ?>" class="btn btn-sm btn-light-primary">
                            <i class="ki-outline ki-arrows-circle fs-2"></i> Refresh Status
                        </a>
                    </div>
                </div>
                
                <div class="card-body py-5">
                    <?php if ($status === 'connected'): ?>
                        <div class="alert alert-success d-flex align-items-center p-5 mb-0">
                            <i class="ki-outline ki-shield-tick fs-2hx text-success me-4"></i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-success">Berhasil Terhubung!</h4>
                                <span>WhatsApp Gateway sudah tersambung dengan nomor JID: <strong><?= htmlspecialchars($jid) ?></strong></span>
                            </div>
                        </div>
                    <?php elseif ($status === 'disconnected'): ?>
                        <div class="alert alert-warning d-flex align-items-center p-5 mb-5">
                            <i class="ki-outline ki-information-5 fs-2hx text-warning me-4"></i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-warning">WhatsApp Terputus</h4>
                                <span>Silakan scan QR Code di bawah ini menggunakan aplikasi WhatsApp di HP Anda (Pilih Linked Devices > Link a Device).</span>
                            </div>
                        </div>
                        
                        <?php if ($qrData): ?>
                            <div class="text-center mt-5">
                                <?php if (isset($qrData['qr_link'])): ?>
                                    <img src="<?= $qrData['qr_link'] ?>" alt="QR Code" class="img-thumbnail border-primary" style="max-width: 300px;">
                                <?php elseif (isset($qrData['qr_code'])): ?>
                                    <!-- Jika berbentuk text base64 qr -->
                                    <div class="p-4 bg-white d-inline-block rounded shadow-sm">
                                        <div id="qrcode"></div>
                                        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
                                        <script>
                                            new QRCode(document.getElementById("qrcode"), {
                                                text: "<?= $qrData['qr_code'] ?>",
                                                width: 256,
                                                height: 256
                                            });
                                        </script>
                                    </div>
                                <?php else: ?>
                                    <p class="text-danger">Format QR Code tidak dikenali.</p>
                                    <pre class="text-start bg-light p-3 rounded"><?= json_encode($qrData, JSON_PRETTY_PRINT) ?></pre>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <p>Gagal mengambil QR Code dari WA Gateway.</p>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="alert alert-danger d-flex align-items-center p-5 mb-0">
                            <i class="ki-outline ki-shield-cross fs-2hx text-danger me-4"></i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-danger">Gagal Menghubungi Gateway</h4>
                                <span>Pastikan <code>WA_GATEWAY_URL</code>, <code>WA_GATEWAY_USERNAME</code>, dan <code>WA_GATEWAY_PASSWORD</code> sudah diatur dengan benar di file <code>.env</code>.</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->
