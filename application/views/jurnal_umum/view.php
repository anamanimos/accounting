<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Jurnal Umum</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('home') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Jurnal</li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Jurnal Umum</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div id="jurnal-umum-container">

<div id="view">
	<div class="d-flex justify-content-between align-items-center mb-5">
		<div>
			<a href="<?php echo base_url(); ?>jurnal_umum" class="btn btn-sm btn-light-primary ms-2">
				<i class="ki-outline ki-arrows-circle fs-3"></i> Refresh
			</a>
		</div>
		<div>
			<form id="form-search" class="d-flex align-items-center">
				<label class="me-2 fw-semibold text-muted">Cari No.Jurnal/Rek:</label>
				<input type="text" name="txt_cari" id="txt_cari" class="form-control form-control-sm form-control-solid w-200px me-2" placeholder="Pencarian..." />
				<button type="submit" name="cari" id="cari" class="btn btn-sm btn-icon btn-light-success">
                    <i class="ki-outline ki-magnifier fs-3"></i>
                </button>
			</form>
		</div>
	</div>
    
    <!-- Table Container will be populated via AJAX -->
	<div id="content-table">
        <?php $this->load->view('jurnal_umum/ajax_table'); ?>
    </div>
</div>

<!-- Modal Edit Jurnal Dynamic (Full Transaction) -->
<div class="modal fade" id="modal-edit-jurnal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 justify-content-between align-items-center pt-5 px-7">
                <h3 class="fw-bold text-gray-900 m-0">
                    <i class="ki-outline ki-pencil fs-2 text-primary me-2"></i> Edit Transaksi Jurnal Umum
                </h3>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            
            <div class="modal-body scroll-y mx-3 mx-xl-7 my-3 pt-4">
                <form id="form-edit-jurnal" class="form">
                    
                    <div class="card bg-light-primary border border-primary border-dashed mb-6 p-4">
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label class="required fs-7 fw-bold text-gray-800 mb-1">No. Jurnal</label>
                                <input type="text" name="no_jurnal" id="edit_no_jurnal" class="form-control form-control-sm form-control-solid fw-bold" readonly />
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label class="required fs-7 fw-bold text-gray-800 mb-1">Tanggal Jurnal</label>
                                <input type="date" name="tgl_jurnal" id="edit_tgl_jurnal" class="form-control form-control-sm form-control-solid" required />
                            </div>
                            <div class="col-md-4">
                                <label class="fs-7 fw-bold text-gray-800 mb-1">No. Bukti</label>
                                <input type="text" name="no_bukti" id="edit_no_bukti" class="form-control form-control-sm form-control-solid" placeholder="Nomor Bukti" />
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-gray-800 m-0">Rincian Baris Transaksi</h5>
                        <button type="button" class="btn btn-sm btn-light-primary" id="btn-add-edit-row">
                            <i class="ki-outline ki-plus fs-3"></i> Tambah Baris
                        </button>
                    </div>

                    <div class="table-responsive mb-5" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-2 fs-7" id="table-edit-rows">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-3" style="width: 28%;">No. Rekening</th>
                                    <th style="width: 34%;">Keterangan</th>
                                    <th class="text-end" style="width: 17%;">Debet (Rp)</th>
                                    <th class="text-end" style="width: 17%;">Kredit (Rp)</th>
                                    <th class="text-center" style="width: 4%;">#</th>
                                </tr>
                            </thead>
                            <tbody id="edit-rows-tbody">
                                <!-- Dynamic rows populated via JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary & Balance Status Bar -->
                    <div class="d-flex flex-stack bg-light p-4 rounded mb-7 border">
                        <div class="d-flex align-items-center">
                            <span class="fw-bold text-gray-800 me-3 fs-7">Status Jurnal:</span>
                            <span id="badge-edit-balance" class="badge badge-light-success fs-7 fw-bold px-3 py-2">
                                Balance (Seimbang)
                            </span>
                        </div>
                        <div class="text-end fs-7">
                            <span class="text-muted me-3">Total Debet: <strong id="sum-edit-debet" class="text-dark">Rp 0</strong></span>
                            <span class="text-muted">Total Kredit: <strong id="sum-edit-kredit" class="text-dark">Rp 0</strong></span>
                        </div>
                    </div>

                    <div class="text-end pt-3">
                        <button type="button" class="btn btn-sm btn-light me-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="btn-save-edit-jurnal" class="btn btn-sm btn-primary">
                            <span class="indicator-label"><i class="ki-outline ki-check-circle fs-3 me-1"></i> Simpan Perubahan Jurnal</span>
                            <span class="indicator-progress">Disimpan... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {

    var modalEditEl = document.getElementById('modal-edit-jurnal');
    var modalEdit = modalEditEl ? new bootstrap.Modal(modalEditEl) : null;

    // Rekening options array built from PHP
    var listRekOptions = [];
    <?php if (isset($list_rek) && $list_rek->num_rows() > 0): ?>
        <?php foreach ($list_rek->result() as $rk): ?>
            listRekOptions.push({
                no_rek: '<?= $rk->no_rek ?>',
                nama_rek: '<?= addslashes($rk->nama_rek) ?>'
            });
        <?php endforeach; ?>
    <?php endif; ?>

    // Function to load table via AJAX
    function loadTable(url, data = {}) {
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            beforeSend: function() {
                $('#content-table').html('<div class="d-flex justify-content-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            },
            success: function(response) {
                $('#content-table').html(response);
            },
            error: function() {
                Swal.fire('Error', 'Gagal memuat data tabel', 'error');
            }
        });
    }

    // Function to render Rekening select HTML
    function buildRekSelect(selectedNoRek) {
        var html = '<select class="form-select form-select-sm form-select-solid select-edit-rek" required>';
        html += '<option value="">-- Pilih Rekening --</option>';
        for (var i = 0; i < listRekOptions.length; i++) {
            var r = listRekOptions[i];
            var sel = (r.no_rek == selectedNoRek) ? 'selected' : '';
            html += '<option value="' + r.no_rek + '" ' + sel + '>' + r.no_rek + ' - ' + r.nama_rek + '</option>';
        }
        html += '</select>';
        return html;
    }

    // Function to Add Dynamic Row to Edit Modal Table
    function addEditRow(rowData = {}) {
        var noRek = rowData.no_rek || '';
        var ket = rowData.ket || '';
        var debet = rowData.debet !== undefined ? rowData.debet : 0;
        var kredit = rowData.kredit !== undefined ? rowData.kredit : 0;

        var trHtml = '<tr>' +
            '<td class="ps-2">' + buildRekSelect(noRek) + '</td>' +
            '<td><input type="text" class="form-control form-control-sm form-control-solid input-edit-ket" value="' + htmlEscape(ket) + '" placeholder="Keterangan..." required /></td>' +
            '<td><input type="number" class="form-control form-control-sm form-control-solid text-end input-edit-debet" min="0" value="' + debet + '" required /></td>' +
            '<td><input type="number" class="form-control form-control-sm form-control-solid text-end input-edit-kredit" min="0" value="' + kredit + '" required /></td>' +
            '<td class="text-center"><button type="button" class="btn btn-icon btn-sm btn-light-danger h-25px w-25px btn-remove-edit-row" title="Hapus Baris"><i class="ki-outline ki-trash fs-6"></i></button></td>' +
            '</tr>';

        $('#edit-rows-tbody').append(trHtml);
        recalculateEditTotals();
    }

    function htmlEscape(str) {
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // Recalculate totals and check balance status
    function recalculateEditTotals() {
        var totalDebet = 0;
        var totalKredit = 0;

        $('#edit-rows-tbody tr').each(function() {
            var debetVal = parseInt($(this).find('.input-edit-debet').val()) || 0;
            var kreditVal = parseInt($(this).find('.input-edit-kredit').val()) || 0;
            totalDebet += debetVal;
            totalKredit += kreditVal;
        });

        $('#sum-edit-debet').text('Rp ' + totalDebet.toLocaleString('id-ID'));
        $('#sum-edit-kredit').text('Rp ' + totalKredit.toLocaleString('id-ID'));

        var badge = $('#badge-edit-balance');
        var btnSave = $('#btn-save-edit-jurnal');

        if (totalDebet === totalKredit && totalDebet > 0) {
            badge.removeClass('badge-light-danger badge-light-warning')
                 .addClass('badge-light-success')
                 .text('✅ Balance (Seimbang)');
            btnSave.prop('disabled', false);
        } else if (totalDebet === 0 && totalKredit === 0) {
            badge.removeClass('badge-light-success badge-light-danger')
                 .addClass('badge-light-warning')
                 .text('⚠️ Baris Kosong');
            btnSave.prop('disabled', true);
        } else {
            var diff = Math.abs(totalDebet - totalKredit);
            badge.removeClass('badge-light-success badge-light-warning')
                 .addClass('badge-light-danger')
                 .text('❌ Tidak Balance (Selisih: Rp ' + diff.toLocaleString('id-ID') + ')');
            btnSave.prop('disabled', true);
        }
    }

    // Event: Add Dynamic Row Button
    $('#btn-add-edit-row').on('click', function() {
        var lastKet = $('#edit-rows-tbody tr:last .input-edit-ket').val() || '';
        addEditRow({ ket: lastKet, debet: 0, kredit: 0 });
    });

    // Event: Remove Row Button
    $(document).on('click', '.btn-remove-edit-row', function() {
        if ($('#edit-rows-tbody tr').length <= 1) {
            Swal.fire('Info', 'Minimal 1 baris transaksi harus ada', 'info');
            return;
        }
        $(this).closest('tr').remove();
        recalculateEditTotals();
    });

    // Event: Input numbers change recalculation
    $(document).on('input change', '.input-edit-debet, .input-edit-kredit', function() {
        recalculateEditTotals();
    });

    // Handle Search Form Submit via AJAX
    $('#form-search').on('submit', function(e) {
        e.preventDefault();
        var searchData = $(this).serialize();
        loadTable('<?php echo base_url(); ?>jurnal_umum/index/0', searchData);
    });

    // Handle Pagination Clicks via AJAX
    $(document).on('click', '.ajax-pagination a', function(e) {
        e.preventDefault();
        var pageUrl = $(this).attr('href');
        var searchVal = $('#txt_cari').val();
        loadTable(pageUrl, { txt_cari: searchVal });
    });

    function getModalInstance() {
        if (!modalEditEl) return null;
        var inst = bootstrap.Modal.getInstance(modalEditEl);
        if (!inst) {
            inst = new bootstrap.Modal(modalEditEl);
        }
        return inst;
    }

    // Handle Click Edit Jurnal Button
    $(document).on('click', '.btn-edit-jurnal', function(e) {
        e.preventDefault();
        var rawNoJurnal = $(this).attr('data-nojurnal');
        if (!rawNoJurnal) {
            Swal.fire('Gagal', 'No Jurnal tidak valid', 'warning');
            return;
        }
        var noJurnal = String(rawNoJurnal).trim();

        $.ajax({
            url: '<?php echo base_url(); ?>jurnal_umum/get_jurnal_full',
            type: 'POST',
            data: { no_jurnal: noJurnal },
            dataType: 'json',
            beforeSend: function() {
                Swal.fire({
                    title: 'Memuat Data...',
                    text: 'Mengambil seluruh baris transaksi No. Jurnal: ' + noJurnal,
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
            },
            success: function(res) {
                Swal.close();
                if (res.status === 'success' && res.rows) {
                    $('#edit_no_jurnal').val(res.no_jurnal);
                    $('#edit_tgl_jurnal').val(res.tgl_jurnal);
                    $('#edit_no_bukti').val(res.no_bukti);

                    $('#edit-rows-tbody').empty();
                    for (var i = 0; i < res.rows.length; i++) {
                        addEditRow(res.rows[i]);
                    }

                    recalculateEditTotals();

                    var modalInst = getModalInstance();
                    if (modalInst) {
                        modalInst.show();
                    }
                } else {
                    Swal.fire('Gagal', res.message || 'Data jurnal tidak ditemukan', 'error');
                }
            },
            error: function(xhr) {
                Swal.close();
                var msg = 'Gagal terhubung ke server';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseText && xhr.responseText.length < 200) {
                    msg = xhr.responseText;
                }
                Swal.fire('Gagal Memuat Data', msg, 'error');
            }
        });
    });

    // Handle Form Submit Save Full Journal Entry
    $('#form-edit-jurnal').on('submit', function(e) {
        e.preventDefault();

        var noJurnal = $('#edit_no_jurnal').val();
        var tglJurnal = $('#edit_tgl_jurnal').val();
        var noBukti = $('#edit_no_bukti').val();
        var rowsData = [];

        $('#edit-rows-tbody tr').each(function() {
            var noRek = $(this).find('.select-edit-rek').val();
            var ket = $(this).find('.input-edit-ket').val();
            var debet = $(this).find('.input-edit-debet').val();
            var kredit = $(this).find('.input-edit-kredit').val();

            if (noRek) {
                rowsData.push({
                    no_rek: noRek,
                    ket: ket,
                    debet: debet,
                    kredit: kredit
                });
            }
        });

        if (rowsData.length === 0) {
            Swal.fire('Peringatan', 'Minimal 1 baris rekening harus dipilih', 'warning');
            return;
        }

        var btn = $('#btn-save-edit-jurnal');

        $.ajax({
            url: '<?php echo base_url(); ?>jurnal_umum/save_jurnal_full',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                no_jurnal: noJurnal,
                tgl_jurnal: tglJurnal,
                no_bukti: noBukti,
                rows: rowsData
            }),
            dataType: 'json',
            beforeSend: function() {
                btn.prop('disabled', true);
            },
            success: function(res) {
                btn.prop('disabled', false);
                if (res.status === 'success') {
                    if (modalEdit) {
                        modalEdit.hide();
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    var searchVal = $('#txt_cari').val();
                    loadTable('<?php echo base_url(); ?>jurnal_umum/index/0', { txt_cari: searchVal });
                } else {
                    Swal.fire('Gagal', res.message || 'Gagal menyimpan perubahan jurnal', 'error');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false);
                var errJson = xhr.responseJSON;
                Swal.fire('Gagal Simpan', (errJson && errJson.message) ? errJson.message : 'Terjadi kesalahan pada server', 'error');
            }
        });
    });

});
</script>
<div id="tampil_data"></div>
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>