<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    <i class="ki-outline ki-technology-4 fs-2 me-2 text-primary"></i> AI Input
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">AI Input</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="row g-5">
                <!--begin::Input Panel-->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ki-outline ki-file-added fs-3 text-primary me-2"></i>
                                Raw Data Input
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="notice d-flex bg-light-info rounded border-info border border-dashed mb-5 p-5">
                                <i class="ki-outline ki-information-5 fs-2tx text-info me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-info">Format Input:</h4>
                                    <span class="text-gray-700 fs-7">
                                        <code>dd - mm - yyyy</code><br>
                                        <code>[pelanggan] - [supplier] - [deskripsi] - [ukuran] - [modal]|[harga_jual]</code>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="form-label fw-semibold">Paste Raw Data:</label>
                                <textarea id="raw_data" class="form-control form-control-solid" rows="15" placeholder="01 - 02 - 2025
Sevencols - Luar(P.Riyadi) - DTF logonike - 31x160cm - 48000|672000
Sevencols - Luar(PE) - DTF Dimas - 58x100cm - 35000|50000"></textarea>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="button" class="btn btn-primary flex-grow-1" id="btn_preview">
                                    <i class="ki-outline ki-eye fs-5"></i> Preview
                                </button>
                                <button type="button" class="btn btn-success flex-grow-1" id="btn_execute" disabled>
                                    <i class="ki-outline ki-check fs-5"></i> Simpan ke Database
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Input Panel-->

                <!--begin::Result Panel-->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ki-outline ki-code fs-3 text-success me-2"></i>
                                Preview & SQL Output
                            </h3>
                            <div class="card-toolbar">
                                <button type="button" class="btn btn-sm btn-light-primary" id="btn_copy_sql" style="display:none;">
                                    <i class="ki-outline ki-copy fs-5"></i> Copy SQL
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!--begin::Info-->
                            <div class="mb-5" id="result_info" style="display:none;">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-gray-600">Transaksi ditemukan:</span>
                                    <span class="fw-bold text-primary" id="trx_count">0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-gray-600">Total baris jurnal:</span>
                                    <span class="fw-bold text-success" id="row_count">0</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-gray-600">No. Jurnal terakhir:</span>
                                    <span class="fw-bold" id="last_jurnal"><?= $last_no_jurnal ?></span>
                                </div>
                            </div>
                            <!--end::Info-->

                            <!--begin::Preview Table-->
                            <div id="preview_table" style="display:none;">
                                <h6 class="fw-bold text-gray-700 mb-3">Preview Transaksi:</h6>
                                <div id="new_entities" class="mb-3" style="display:none;"></div>
                                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                                    <table class="table table-sm table-row-dashed fs-7">
                                        <thead>
                                            <tr class="text-gray-500 fw-bold text-uppercase">
                                                <th>No. Jurnal</th>
                                                <th>Tanggal</th>
                                                <th>Customer</th>
                                                <th>Supplier</th>
                                                <th class="text-end">Harga</th>
                                            </tr>
                                        </thead>
                                        <tbody id="preview_tbody"></tbody>
                                    </table>
                                </div>
                            </div>
                            <!--end::Preview Table-->

                            <!--begin::SQL Output-->
                            <div class="mt-5" id="sql_output_container" style="display:none;">
                                <h6 class="fw-bold text-gray-700 mb-3">Generated SQL:</h6>
                                <textarea id="sql_output" class="form-control form-control-solid bg-dark text-success font-monospace" rows="10" readonly style="font-size: 11px;"></textarea>
                            </div>
                            <!--end::SQL Output-->

                            <!--begin::Empty State-->
                            <div id="empty_state" class="text-center py-10">
                                <i class="ki-outline ki-abstract-26 fs-4x text-gray-300 mb-5"></i>
                                <p class="text-gray-500 fs-6">Paste data di sebelah kiri lalu klik <strong>Preview</strong></p>
                            </div>
                            <!--end::Empty State-->
                        </div>
                    </div>
                </div>
                <!--end::Result Panel-->
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    var rawDataEl = document.getElementById('raw_data');
    var btnPreview = document.getElementById('btn_preview');
    var btnExecute = document.getElementById('btn_execute');
    var btnCopySql = document.getElementById('btn_copy_sql');

    btnPreview.addEventListener('click', function() {
        var rawData = rawDataEl.value.trim();
        if (!rawData) {
            toastr.warning('Masukkan data terlebih dahulu');
            return;
        }

        btnPreview.disabled = true;
        btnPreview.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

        axios.post(baseUrl + 'ai_input/process', { raw_data: rawData })
            .then(function(response) {
                if (response.data.status === 'success') {
                    var data = response.data.data;
                    var sql = response.data.sql;
                    var suppliersCreated = response.data.suppliers_created || [];
                    var customersCreated = response.data.customers_created || [];

                    // Show info
                    document.getElementById('result_info').style.display = 'block';
                    document.getElementById('trx_count').textContent = data.length;
                    document.getElementById('row_count').textContent = data.length * 4;
                    if (data.length > 0) {
                        document.getElementById('last_jurnal').textContent = data[data.length - 1].no_jurnal;
                    }

                    // Show new entities notification
                    var newEntitiesEl = document.getElementById('new_entities');
                    if (suppliersCreated.length > 0 || customersCreated.length > 0) {
                        var html = '<div class="alert alert-info py-2 px-3 fs-7">';
                        if (suppliersCreated.length > 0) {
                            html += '<strong>Supplier baru:</strong> ' + suppliersCreated.map(s => s.nama + ' (' + s.kode + ')').join(', ') + '<br>';
                        }
                        if (customersCreated.length > 0) {
                            html += '<strong>Customer baru:</strong> ' + customersCreated.map(c => c.nama + ' (' + c.kode + ')').join(', ');
                        }
                        html += '</div>';
                        newEntitiesEl.innerHTML = html;
                        newEntitiesEl.style.display = 'block';
                    } else {
                        newEntitiesEl.style.display = 'none';
                    }

                    // Build preview table with supplier/customer
                    var tbody = document.getElementById('preview_tbody');
                    tbody.innerHTML = '';
                    data.forEach(function(trx) {
                        var row = '<tr>';
                        row += '<td><span class="badge badge-light-primary">' + trx.no_jurnal + '</span></td>';
                        row += '<td>' + trx.tgl_jurnal + '</td>';
                        row += '<td><span class="badge badge-light-info">' + (trx.customer_name || '-') + '</span></td>';
                        row += '<td><span class="badge badge-light-warning">' + (trx.supplier_name || '-') + '</span></td>';
                        row += '<td class="text-end fw-bold">' + new Intl.NumberFormat('id-ID').format(trx.harga_jual) + '</td>';
                        row += '</tr>';
                        tbody.innerHTML += row;
                    });

                    document.getElementById('preview_table').style.display = 'block';
                    document.getElementById('sql_output_container').style.display = 'block';
                    document.getElementById('sql_output').value = sql;
                    document.getElementById('empty_state').style.display = 'none';
                    document.getElementById('btn_copy_sql').style.display = 'inline-block';
                    btnExecute.disabled = false;

                    var msg = 'Preview berhasil! Ditemukan ' + data.length + ' transaksi.';
                    if (suppliersCreated.length > 0) msg += ' (' + suppliersCreated.length + ' supplier baru)';
                    if (customersCreated.length > 0) msg += ' (' + customersCreated.length + ' customer baru)';
                    toastr.success(msg);
                } else {
                    toastr.error(response.data.message);
                }
            })
            .catch(function(error) {
                toastr.error('Gagal memproses data');
            })
            .finally(function() {
                btnPreview.disabled = false;
                btnPreview.innerHTML = '<i class="ki-outline ki-eye fs-5"></i> Preview';
            });
    });

    btnExecute.addEventListener('click', function() {
        Swal.fire({
            title: 'Simpan ke Database?',
            text: 'Data akan disimpan ke tabel jurnal_umum.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                btnExecute.disabled = true;
                btnExecute.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

                axios.post(baseUrl + 'ai_input/execute', { raw_data: rawDataEl.value })
                    .then(function(response) {
                        if (response.data.status === 'success') {
                            toastr.success(response.data.message);
                            rawDataEl.value = '';
                            document.getElementById('result_info').style.display = 'none';
                            document.getElementById('preview_table').style.display = 'none';
                            document.getElementById('sql_output_container').style.display = 'none';
                            document.getElementById('empty_state').style.display = 'block';
                            document.getElementById('btn_copy_sql').style.display = 'none';
                        } else {
                            toastr.error(response.data.message);
                        }
                    })
                    .catch(function(error) {
                        toastr.error('Gagal menyimpan data');
                    })
                    .finally(function() {
                        btnExecute.disabled = true;
                        btnExecute.innerHTML = '<i class="ki-outline ki-check fs-5"></i> Simpan ke Database';
                    });
            }
        });
    });

    btnCopySql.addEventListener('click', function() {
        var sql = document.getElementById('sql_output');
        sql.select();
        document.execCommand('copy');
        toastr.success('SQL berhasil dicopy!');
    });
});
</script>
