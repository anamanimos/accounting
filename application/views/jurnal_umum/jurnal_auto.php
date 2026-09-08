<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Jurnal Auto Prompt</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('home') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Control Panel</li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Jurnal Auto Prompt</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="row g-5">
                <!-- Panduan Format -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Format Perintah</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">Mendukung format chat PE &amp; format standar</span>
                            </h3>
                        </div>
                        <div class="card-body pt-5">
                            <div class="notice d-flex bg-light-info rounded border-info border border-dashed mb-5 p-4">
                                <i class="ki-outline ki-information-5 fs-2tx text-info me-3"></i>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-gray-800 fs-6">No. Jurnal &amp; No. Bukti otomatis dari database</span>
                                </div>
                            </div>

                            <!-- Opsi 1: Format Order PE -->
                            <div class="card bg-light-primary border border-primary border-dashed p-4 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge badge-primary">Format 1: Chat Order PE (WA)</span>
                                    <button type="button" class="btn btn-xs btn-sm btn-primary py-1 px-2" onclick="copySamplePE()">
                                        <i class="ki-outline ki-copy fs-6"></i> Gunakan Contoh
                                    </button>
                                </div>
                                <span class="text-muted fs-7">Cukup salin &amp; tempel langsung chat konfirmasi dari WhatsApp:</span>
                                <div class="bg-white rounded p-3 text-gray-700 font-monospace fs-8 mt-2" style="white-space: pre-wrap;" id="sample_pe_text">❗Mohon untuk di cek ulang filenya apakah sudah benar kak?

Cetak DTF 4700CM = Rp 1.175.000

❗Mohon transfer sesuai nominal di atas ya❗

Transfer ke no rekening di bawah ini

BCA a/n Andrias Abadi Auw 8630447241 
BRI a/n Andrias Abadi Auw 058401002241569

❗Pesanan baru bisa di proses ketika pelanggan sudah mengirim bukti transfer❗

bisa juga lewat marketplace
Shopee https://shopee.co.id/pe.production.malang</div>
                            </div>

                            <!-- Opsi 2: Format Standar Baris Jurnal -->
                            <div class="card bg-light-success border border-success border-dashed p-4 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge badge-success">Format 2: Standar Multi-Baris</span>
                                    <button type="button" class="btn btn-xs btn-sm btn-success py-1 px-2" onclick="copySampleStandard()">
                                        <i class="ki-outline ki-copy fs-6"></i> Gunakan Contoh
                                    </button>
                                </div>
                                <span class="text-muted fs-7 mb-2">Format baris per baris:</span>
                                <code class="bg-white p-2 rounded text-success fs-8 d-block mt-1">[Pelanggan] - [Suplier] - [Deskripsi] - [Ukuran] - [Modal]|[Harga Jual]</code>
                                <div class="bg-white rounded p-3 text-gray-700 font-monospace fs-8 mt-2" style="white-space: pre-wrap;" id="sample_standard_text">18 - 09 - 2025
Sevencols - Luar(P.Riyadi) - DTF KBKA TAZZAKA-18-9-25 - A4 - 10000|15000
Sevencols - PE - Cetak DTF - 557 - 139250</div>
                            </div>
                            
                            <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mb-2 p-3">
                                <div class="d-flex flex-column fs-7">
                                    <span class="fw-bold text-gray-800">Aturan Supplier &amp; Rekening:</span>
                                    <span class="text-muted mt-1"><b>PE / Lainnya</b> &rarr; Rek 118 (Kas/Bank)</span>
                                    <span class="text-muted"><b>Luar(P.Riyadi)</b> &rarr; Rek 213 (Hutang)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Area Input & Preview -->
                <div class="col-md-8">
                    <!-- SECTION 1: Textarea Input -->
                    <div class="card" id="card_input">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Input Transaksi</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">Tempel teks pesanan Anda di sini (Format Chat PE atau Standar)</span>
                            </h3>
                        </div>
                        <div class="card-body">
                            <textarea id="prompt_text" class="form-control form-control-solid font-monospace fs-6" rows="16" placeholder="Tempel seluruh teks chat WhatsApp atau format baris pesanan di sini...&#10;&#10;Contoh:&#10;Cetak DTF 4700CM = Rp 1.175.000&#10;&#10;Atau:&#10;18 - 09 - 2025&#10;Sevencols - Luar(P.Riyadi) - DTF KBKA TAZZAKA - A4 - 10000|15000"></textarea>
                            
                            <div class="d-flex justify-content-end mt-5">
                                <button type="button" class="btn btn-primary" id="btn_preview">
                                    <i class="ki-outline ki-eye fs-2"></i> Generate Preview
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Preview Table (Hidden by default) -->
                    <div class="card d-none" id="card_preview">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Pratinjau Jurnal</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">Teliti sebelum menyimpan ke database</span>
                            </h3>
                            <div class="card-toolbar">
                                <button type="button" class="btn btn-sm btn-light-primary me-2" id="btn_back_edit">
                                    <i class="ki-outline ki-pencil fs-2"></i> Edit Prompt
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
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->

<script>
var previewData = [];
var previewUrl = baseUrl + 'jurnal_umum/jurnal_auto_preview';
var saveUrl = baseUrl + 'jurnal_umum/jurnal_auto_save';
var listUrl = baseUrl + 'jurnal_umum';

document.getElementById('btn_preview').addEventListener('click', function() {
    var promptText = document.getElementById('prompt_text').value;
    if (!promptText.trim()) {
        Swal.fire('Oops!', 'Teks kosong. Harap isi prompt terlebih dahulu.', 'warning');
        return;
    }

    Swal.fire({
        title: 'Memproses...',
        text: 'Mengurai teks menjadi entri jurnal',
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
                document.getElementById('card_input').classList.add('d-none');
                document.getElementById('card_preview').classList.remove('d-none');
            }
        })
        .catch(function(err) {
            var msg = 'Gagal memproses parsing.';
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
    document.getElementById('card_input').classList.remove('d-none');
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
                        Swal.fire({
                            title: 'Jurnal Berhasil Disimpan!',
                            text: res.data.message || 'Data transaksi jurnal telah berhasil disimpan ke database.',
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: '<i class="ki-outline ki-document fs-3 me-1"></i> Ke Jurnal Umum',
                            cancelButtonText: '<i class="ki-outline ki-plus-circle fs-3 me-1"></i> Input Data Baru',
                            confirmButtonColor: '#009EF7',
                            cancelButtonColor: '#50CD89',
                            reverseButtons: true,
                            allowOutsideClick: false
                        }).then(function(choice) {
                            if (choice.isConfirmed) {
                                window.location.href = listUrl;
                            } else {
                                // Reset form & preview untuk input teks baru
                                document.getElementById('prompt_text').value = '';
                                previewData = [];
                                document.getElementById('card_preview').classList.add('d-none');
                                document.getElementById('card_input').classList.remove('d-none');
                                document.getElementById('card_input').scrollIntoView({ behavior: 'smooth' });
                            }
                        });
                    } else {
                        Swal.fire('Gagal', res.message || res.data.message, 'error');
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

function copySamplePE() {
    var txt = document.getElementById('sample_pe_text').textContent;
    document.getElementById('prompt_text').value = txt;
    document.getElementById('prompt_text').focus();
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Contoh teks order PE berhasil disalin ke input!',
        showConfirmButton: false,
        timer: 2000
    });
}

function copySampleStandard() {
    var txt = document.getElementById('sample_standard_text').textContent;
    document.getElementById('prompt_text').value = txt;
    document.getElementById('prompt_text').focus();
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Contoh format standar berhasil disalin ke input!',
        showConfirmButton: false,
        timer: 2000
    });
}
</script>
