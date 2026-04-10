<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Input Jurnal Umum</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('jurnal_umum') ?>" class="text-muted text-hover-primary">Jurnal Umum</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Input</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="<?= base_url('jurnal_umum') ?>" class="btn btn-sm fw-bold btn-light">
                    <i class="ki-outline ki-arrow-left fs-5"></i> Kembali
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <form action="<?= base_url('jurnal_umum/store') ?>" method="post" id="form_jurnal">
                <!--begin::Header Card-->
                <div class="card mb-5">
                    <div class="card-body">
                        <div class="row g-5">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">No. Jurnal</label>
                                <input type="text" class="form-control form-control-solid" value="<?= $no_jurnal ?>" readonly />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required fw-semibold">Tanggal</label>
                                <input type="date" name="tgl_jurnal" class="form-control form-control-solid" value="<?= date('Y-m-d') ?>" required />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required fw-semibold">No. Bukti</label>
                                <input type="text" name="no_bukti" class="form-control form-control-solid" placeholder="Contoh: BKK-001" required />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required fw-semibold">Keterangan</label>
                                <input type="text" name="ket" class="form-control form-control-solid" placeholder="Keterangan transaksi" required />
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Header Card-->

                <!--begin::Detail Card-->
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900">Detail Jurnal</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">Masukkan debet dan kredit (harus balance)</span>
                        </h3>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-sm btn-light-primary" id="btn_add_row">
                                <i class="ki-outline ki-plus fs-5"></i> Tambah Baris
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="tbl_detail">
                            <thead>
                                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-200px">Rekening</th>
                                    <th class="min-w-150px">Debet</th>
                                    <th class="min-w-150px">Kredit</th>
                                    <th class="w-50px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="row-detail">
                                    <td>
                                        <select name="no_rek[]" class="form-select form-select-solid" required>
                                            <option value="">-- Pilih Rekening --</option>
                                            <?php foreach ($rekening as $kode => $nama): ?>
                                            <option value="<?= $kode ?>"><?= $nama ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="debet[]" class="form-control form-control-solid input-debet text-end" value="0" />
                                    </td>
                                    <td>
                                        <input type="text" name="kredit[]" class="form-control form-control-solid input-kredit text-end" value="0" />
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-icon btn-light-danger btn-remove-row">
                                            <i class="ki-outline ki-trash fs-5"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="row-detail">
                                    <td>
                                        <select name="no_rek[]" class="form-select form-select-solid" required>
                                            <option value="">-- Pilih Rekening --</option>
                                            <?php foreach ($rekening as $kode => $nama): ?>
                                            <option value="<?= $kode ?>"><?= $nama ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="debet[]" class="form-control form-control-solid input-debet text-end" value="0" />
                                    </td>
                                    <td>
                                        <input type="text" name="kredit[]" class="form-control form-control-solid input-kredit text-end" value="0" />
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-icon btn-light-danger btn-remove-row">
                                            <i class="ki-outline ki-trash fs-5"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold fs-5">
                                    <td class="text-end">Total:</td>
                                    <td class="text-end text-primary" id="total_debet">0</td>
                                    <td class="text-end text-success" id="total_kredit">0</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-center">
                                        <span id="balance_status" class="badge badge-light-warning fs-6">Belum Balance</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <a href="<?= base_url('jurnal_umum') ?>" class="btn btn-light me-3">Batal</a>
                        <button type="submit" class="btn btn-primary" id="btn_submit">
                            <i class="ki-outline ki-check fs-5"></i> Simpan Jurnal
                        </button>
                    </div>
                </div>
                <!--end::Detail Card-->
            </form>
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    var rekening_options = `
        <option value="">-- Pilih Rekening --</option>
        <?php foreach ($rekening as $kode => $nama): ?>
        <option value="<?= $kode ?>"><?= addslashes($nama) ?></option>
        <?php endforeach; ?>
    `;

    // Format number
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Parse number
    function parseNumber(str) {
        return parseFloat(str.replace(/\./g, '').replace(',', '.')) || 0;
    }

    // Calculate totals
    function calculateTotals() {
        var totalDebet = 0;
        var totalKredit = 0;

        document.querySelectorAll('.input-debet').forEach(function(el) {
            totalDebet += parseNumber(el.value);
        });

        document.querySelectorAll('.input-kredit').forEach(function(el) {
            totalKredit += parseNumber(el.value);
        });

        document.getElementById('total_debet').textContent = formatNumber(totalDebet);
        document.getElementById('total_kredit').textContent = formatNumber(totalKredit);

        var balanceStatus = document.getElementById('balance_status');
        if (totalDebet > 0 && totalDebet === totalKredit) {
            balanceStatus.className = 'badge badge-light-success fs-6';
            balanceStatus.textContent = 'Balance ✓';
            document.getElementById('btn_submit').disabled = false;
        } else {
            balanceStatus.className = 'badge badge-light-warning fs-6';
            balanceStatus.textContent = 'Belum Balance (Selisih: ' + formatNumber(Math.abs(totalDebet - totalKredit)) + ')';
            document.getElementById('btn_submit').disabled = true;
        }
    }

    // Add row
    document.getElementById('btn_add_row').addEventListener('click', function() {
        var tbody = document.querySelector('#tbl_detail tbody');
        var newRow = document.createElement('tr');
        newRow.className = 'row-detail';
        newRow.innerHTML = `
            <td>
                <select name="no_rek[]" class="form-select form-select-solid" required>
                    ${rekening_options}
                </select>
            </td>
            <td>
                <input type="text" name="debet[]" class="form-control form-control-solid input-debet text-end" value="0" />
            </td>
            <td>
                <input type="text" name="kredit[]" class="form-control form-control-solid input-kredit text-end" value="0" />
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-icon btn-light-danger btn-remove-row">
                    <i class="ki-outline ki-trash fs-5"></i>
                </button>
            </td>
        `;
        tbody.appendChild(newRow);
        attachEventListeners(newRow);
    });

    // Remove row
    function attachEventListeners(row) {
        row.querySelector('.btn-remove-row').addEventListener('click', function() {
            if (document.querySelectorAll('.row-detail').length > 2) {
                row.remove();
                calculateTotals();
            } else {
                Swal.fire('Info', 'Minimal harus ada 2 baris transaksi.', 'info');
            }
        });

        row.querySelectorAll('.input-debet, .input-kredit').forEach(function(el) {
            el.addEventListener('input', function() {
                this.value = formatNumber(parseNumber(this.value));
                calculateTotals();
            });
        });
    }

    // Attach events to existing rows
    document.querySelectorAll('.row-detail').forEach(function(row) {
        attachEventListeners(row);
    });

    // Form submit validation
    document.getElementById('form_jurnal').addEventListener('submit', function(e) {
        var totalDebet = parseNumber(document.getElementById('total_debet').textContent);
        var totalKredit = parseNumber(document.getElementById('total_kredit').textContent);

        if (totalDebet === 0 || totalDebet !== totalKredit) {
            e.preventDefault();
            Swal.fire('Error', 'Total debet dan kredit harus sama dan tidak boleh 0.', 'error');
            return false;
        }
    });

    calculateTotals();
});
</script>
