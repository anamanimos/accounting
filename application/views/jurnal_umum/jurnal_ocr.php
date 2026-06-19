<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Jurnal OCR (Nota AI)</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('home') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Control Panel</li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Jurnal OCR</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            
            <?php if (!$has_api_key): ?>
            <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                <i class="ki-outline ki-shield-cross fs-2hx text-danger me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-danger">Gemini API Key Belum Dikonfigurasi</h4>
                    <span>Sistem tidak dapat menggunakan fitur AI. Harap tambahkan <code>GEMINI_API_KEY=kunci_anda</code> di file <code>.env</code> Anda.</span>
                </div>
            </div>
            <?php endif; ?>

            <div class="row g-5">
                <!-- Area Input Upload & OCR -->
                <div class="col-md-5">
                    <div class="card h-100" id="card_upload">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Upload Nota</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">Unggah gambar nota dan isi Nama Order</span>
                            </h3>
                        </div>
                        <div class="card-body pt-5">
                            <form id="form_ocr">
                                <div class="mb-5">
                                    <label class="form-label required">Gambar Nota</label>
                                    <input class="form-control" type="file" id="image" name="image" accept="image/png, image/jpeg, image/webp, application/pdf" required>
                                </div>
                                <div class="mb-5">
                                    <label class="form-label required">Nama Order / Deskripsi</label>
                                    <input class="form-control form-control-solid" type="text" id="nama_order" name="nama_order" placeholder="Contoh: Order Shopee, Order Tokopedia" required>
                                    <div class="text-muted fs-7 mt-1">Jika ada lebih dari 1 barang di nota, pisahkan penamaannya dengan koma (,).</div>
                                </div>
                                <div class="d-flex justify-content-end mt-5">
                                    <button type="submit" class="btn btn-warning" id="btn_scan" <?= !$has_api_key ? 'disabled' : '' ?>>
                                        <i class="ki-outline ki-scan-barcode fs-2"></i> Scan Nota
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Area Hasil AI & Preview -->
                <div class="col-md-7">
                    <!-- SECTION 1: Textarea Hasil AI -->
                    <div class="card h-100" id="card_input">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Hasil Scan (Bisa Diedit)</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">Hasil pembacaan AI dari gambar nota Anda</span>
                            </h3>
                        </div>
                        <div class="card-body pt-5">
                            <textarea id="prompt_text" class="form-control form-control-solid font-monospace fs-6" rows="8" placeholder="Menunggu hasil scan..."></textarea>
                            
                            <div class="d-flex justify-content-end mt-5">
                                <button type="button" class="btn btn-primary" id="btn_preview">
                                    <i class="ki-outline ki-eye fs-2"></i> Generate Preview Jurnal
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Preview Table (Hidden by default) -->
            <div class="card d-none mt-5" id="card_preview">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Pratinjau Jurnal</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Teliti sebelum menyimpan ke database</span>
                    </h3>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-primary me-2" id="btn_back_edit">
                            <i class="ki-outline ki-pencil fs-2"></i> Edit Teks Hasil Scan
                        </button>
                        <button type="button" class="btn btn-sm btn-success" id="btn_save_jurnal">
                            <i class="ki-outline ki-check fs-2"></i> Simpan Data
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="table_preview">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 rounded-start">No Jurnal</th>
                                    <th>No Bukti</th>
                                    <th>Tanggal</th>
                                    <th>Rek</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Debet</th>
                                    <th class="text-end pe-4 rounded-end">Kredit</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr class="fw-bold bg-light-success">
                                    <td colspan="5" class="ps-4 text-end">TOTAL</td>
                                    <td class="text-end" id="total_debet">0</td>
                                    <td class="text-end pe-4" id="total_kredit">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->

<script>
var previewData = [];
var scanUrl = baseUrl + 'jurnal_ocr/scan';
var previewUrl = baseUrl + 'jurnal_umum/jurnal_auto_preview';
var saveUrl = baseUrl + 'jurnal_umum/jurnal_auto_save';
var listUrl = baseUrl + 'jurnal_umum';

// Handle Form Scan OCR
document.getElementById('form_ocr').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);

    Swal.fire({
        title: 'Menganalisis...',
        text: 'AI Gemini sedang membaca nota Anda',
        allowOutsideClick: false,
        didOpen: function() { Swal.showLoading(); }
    });

    axios.post(scanUrl, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    })
    .then(function(res) {
        Swal.close();
        if (res.data.status === 'success') {
            document.getElementById('prompt_text').value = res.data.data;
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Berhasil membaca nota!',
                showConfirmButton: false,
                timer: 3000
            });
            // Auto click preview
            document.getElementById('btn_preview').click();
        }
    })
    .catch(function(err) {
        var msg = 'Gagal memproses gambar.';
        if (err.response && err.response.data && err.response.data.message) {
            msg = err.response.data.message;
        }
        Swal.fire('Error', msg, 'error');
    });
});

// Handle Preview Generation
document.getElementById('btn_preview').addEventListener('click', function() {
    var promptText = document.getElementById('prompt_text').value;
    if (!promptText.trim()) {
        Swal.fire('Oops!', 'Teks hasil scan kosong. Harap scan nota atau isi manual terlebih dahulu.', 'warning');
        return;
    }

    Swal.fire({
        title: 'Memproses Jurnal...',
        allowOutsideClick: false,
        didOpen: function() { Swal.showLoading(); }
    });

    var formData = new FormData();
    formData.append('prompt_text', promptText);

    axios.post(previewUrl, formData)
        .then(function(res) {
            Swal.close();
            if (res.data.status === 'success') {
                previewData = res.data.data;
                renderPreview(previewData);
                document.getElementById('card_preview').classList.remove('d-none');
                
                // Scroll to preview
                document.getElementById('card_preview').scrollIntoView({ behavior: 'smooth' });
            }
        })
        .catch(function(err) {
            var msg = 'Gagal membuat jurnal.';
            if (err.response && err.response.data && err.response.data.message) {
                msg = err.response.data.message;
            }
            Swal.fire('Error', msg, 'error');
        });
});

function renderPreview(data) {
    var tbody = document.querySelector('#table_preview tbody');
    tbody.innerHTML = '';
    var totalD = 0, totalK = 0;
    var fmt = new Intl.NumberFormat('id-ID');

    data.forEach(function(row) {
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td class="ps-4 fw-semibold">' + row.no_jurnal + '</td>' +
            '<td>' + row.no_bukti + '</td>' +
            '<td>' + row.tgl_jurnal + '</td>' +
            '<td><span class="badge badge-light-primary fs-7">' + row.no_rek + '</span></td>' +
            '<td class="text-muted">' + row.ket + '</td>' +
            '<td class="text-end fw-bold">' + fmt.format(row.debet) + '</td>' +
            '<td class="text-end pe-4 fw-bold">' + fmt.format(row.kredit) + '</td>';
        tbody.appendChild(tr);
        totalD += parseInt(row.debet);
        totalK += parseInt(row.kredit);
    });

    document.getElementById('total_debet').textContent = fmt.format(totalD);
    document.getElementById('total_kredit').textContent = fmt.format(totalK);
}

document.getElementById('btn_back_edit').addEventListener('click', function() {
    document.getElementById('card_preview').classList.add('d-none');
    document.getElementById('card_input').scrollIntoView({ behavior: 'smooth' });
});

document.getElementById('btn_save_jurnal').addEventListener('click', function() {
    if (previewData.length === 0) return;

    Swal.fire({
        title: 'Simpan ke Database?',
        text: 'Terdapat ' + previewData.length + ' baris jurnal yang akan diinsert.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#50CD89'
    }).then(function(result) {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });

            axios.post(saveUrl, { data: previewData })
                .then(function(res) {
                    if (res.data.status === 'success') {
                        Swal.fire({ title: 'Berhasil!', text: res.data.message, icon: 'success' })
                            .then(function() { window.location.href = listUrl; });
                    } else {
                        Swal.fire('Gagal', res.data.message, 'error');
                    }
                })
                .catch(function(err) {
                    var msg = 'Terjadi kesalahan sistem';
                    if (err.response && err.response.data && err.response.data.message) {
                        msg = err.response.data.message;
                    }
                    Swal.fire('Error', msg, 'error');
                });
        }
    });
});
</script>
