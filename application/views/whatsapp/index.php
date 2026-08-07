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
            
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success d-flex align-items-center p-5 mb-7">
                    <i class="ki-outline ki-check-circle fs-2hx text-success me-4"></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Berhasil</h4>
                        <span><?= $this->session->flashdata('success') ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!--begin::Card - Status Connection-->
            <div class="card shadow-sm mb-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Status Koneksi WA Gateway</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Device ID: <strong><?= htmlspecialchars($device_id) ?></strong> | Server: <strong><?= htmlspecialchars($gateway_url) ?></strong></span>
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
                                <h4 class="mb-1 text-danger">Gagal Menghubungi Server WA Gateway</h4>
                                <span>Server di <code><?= htmlspecialchars($gateway_url) ?></code> tidak merespon. Harap periksa URL Server atau kredensial pada form di bawah.</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!--end::Card - Status Connection-->

            <!--begin::Card - Form Configuration-->
            <div class="card shadow-sm mb-8">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-outline ki-setting-2 fs-2x text-primary me-3"></i>
                            <div>
                                <h3 class="fw-bold text-gray-900 m-0">Konfigurasi Server WhatsApp Gateway</h3>
                                <span class="text-muted fs-7">Pengaturan server gateway, device ID, dan grup target bot</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <form action="<?= base_url('whatsapp/simpan') ?>" method="post">
                        
                        <div class="mb-7">
                            <label class="form-label required fw-bold text-gray-800 fs-6">URL WA Gateway Server</label>
                            <input type="text" name="wa_gateway_url" value="<?= htmlspecialchars($gateway_url) ?>" class="form-control form-control-solid" placeholder="https://wag.nams.my.id" required />
                            <div class="form-text text-muted mt-2">
                                <span class="badge badge-light-primary me-1">Default</span>
                                Server Gateway Utama: <code>https://wag.nams.my.id</code>
                            </div>
                        </div>

                        <div class="row mb-7">
                            <div class="col-md-6 mb-5 mb-md-0">
                                <label class="form-label required fw-bold text-gray-800 fs-6">Device ID (Sesi Perangkat)</label>
                                <input type="text" name="wa_device_id" value="<?= htmlspecialchars($device_id) ?>" class="form-control form-control-solid" placeholder="erp-damaijaya" required />
                                <div class="form-text text-muted mt-1">ID unik sesi WhatsApp di server gateway.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required fw-bold text-gray-800 fs-6">Target Group JID</label>
                                <input type="text" name="wa_group_id" value="<?= htmlspecialchars($group_id) ?>" class="form-control form-control-solid" placeholder="120363426581172416@g.us" required />
                                <div class="form-text text-muted mt-1">ID Grup WhatsApp untuk memproses nota AI.</div>
                            </div>
                        </div>

                        <div class="row mb-7">
                            <div class="col-md-6 mb-5 mb-md-0">
                                <label class="form-label fw-bold text-gray-800 fs-6">Username Basic Auth Gateway</label>
                                <input type="text" name="wa_gateway_username" value="<?= htmlspecialchars($username) ?>" class="form-control form-control-solid" placeholder="admin" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-gray-800 fs-6">Password Basic Auth Gateway</label>
                                <input type="password" name="wa_gateway_password" value="<?= htmlspecialchars($password) ?>" class="form-control form-control-solid" placeholder="admin" />
                            </div>
                        </div>

                        <div class="d-flex justify-content-end pt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-outline ki-check-circle fs-2"></i> Simpan Pengaturan WA Gateway
                            </button>
                        </div>

                    </form>
                </div>
            </div>
            <!--end::Card - Form Configuration-->

        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->
