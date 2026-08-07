<?php if ($this->session->flashdata('result_setting')) { ?>
    <div style="background:#d4edda; color:#155724; border:1px solid #c3e6cb; padding:10px 15px; border-radius:4px; margin-bottom:15px;">
        <strong>Sukses!</strong> <?php echo $this->session->flashdata('result_setting'); ?>
    </div>
<?php } ?>

<div class="easyui-panel" title="Pengaturan AI OCR & Google Cloud API" style="width:100%;padding:15px;margin-bottom:20px;">
    <form action="<?php echo base_url(); ?>settings/simpan" method="post" id="formSettings">
        <table class="list" width="100%" cellpadding="6" cellspacing="0" style="border-collapse:collapse;">
            <tr style="background:#f9f9f9;border-bottom:1px solid #ddd;">
                <td width="250"><b>Google Cloud / AI Studio API Key (Free Tier)</b></td>
                <td>
                    <input type="password" id="google_api_key" name="google_api_key" value="<?php echo htmlspecialchars($google_api_key); ?>" style="width:70%;padding:6px;border:1px solid #ccc;border-radius:4px;" placeholder="AIzaSy..." />
                    <button type="button" onclick="toggleKeyVisibility()" style="padding:6px 12px;cursor:pointer;background:#6c757d;color:#fff;border:none;border-radius:4px;">Lihat / Sembunyikan</button>
                    <br />
                    <small style="color:#666;">API Key ini didapatkan dari Google AI Studio atau Google Cloud Console (Free Tier 1.500 RPD / 1.000 Vision OCR per bulan).</small>
                </td>
            </tr>
            <tr style="border-bottom:1px solid #ddd;">
                <td><b>Layanan OCR Provider</b></td>
                <td>
                    <select name="ocr_provider" style="padding:6px;width:300px;border:1px solid #ccc;border-radius:4px;">
                        <option value="gemini_flash" <?php echo ($ocr_provider == 'gemini_flash') ? 'selected' : ''; ?>>Google Gemini 1.5 Flash (Gratis / AI Studio)</option>
                        <option value="vision_api" <?php echo ($ocr_provider == 'vision_api') ? 'selected' : ''; ?>>Google Cloud Vision API (Gratis 1,000 unit/bln)</option>
                        <option value="combined" <?php echo ($ocr_provider == 'combined') ? 'selected' : ''; ?>>Gabungan (Cloud Vision OCR + Gemini Structurer)</option>
                    </select>
                </td>
            </tr>
            <tr style="background:#f9f9f9;border-bottom:1px solid #ddd;">
                <td><b>Model Gemini Default</b></td>
                <td>
                    <select name="gemini_model" style="padding:6px;width:300px;border:1px solid #ccc;border-radius:4px;">
                        <option value="gemini-1.5-flash" <?php echo ($gemini_model == 'gemini-1.5-flash') ? 'selected' : ''; ?>>gemini-1.5-flash (Sangat Stabil & Cepat)</option>
                        <option value="gemini-2.0-flash" <?php echo ($gemini_model == 'gemini-2.0-flash') ? 'selected' : ''; ?>>gemini-2.0-flash (Versi Terbaru)</option>
                        <option value="gemini-1.5-pro" <?php echo ($gemini_model == 'gemini-1.5-pro') ? 'selected' : ''; ?>>gemini-1.5-pro (Akurasi Tinggi)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="padding-top:15px;">
                    <button type="submit" class="easyui-linkbutton" iconCls="icon-save" style="padding:8px 20px;background:#28a745;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:bold;">Simpan Pengaturan Utama</button>
                </td>
            </tr>
        </table>
    </form>
</div>

<div class="easyui-panel" title="Tabel Pengaturan Dosis & Custom Settings (Key | Value)" style="width:100%;padding:15px;">
    <p style="margin-top:0;color:#555;">Tabel <code>settings</code> menyimpan pasangan data <b>Key | Value</b> secara fleksibel untuk konfigurasi sistem.</p>
    
    <!-- Form Tambah Quick KV -->
    <div style="background:#f1f3f5;padding:10px 15px;border-radius:4px;margin-bottom:15px;display:flex;gap:10px;align-items:center;">
        <b>Tambah Key-Value Baru:</b>
        <input type="text" id="new_kv_key" placeholder="Nama Key (misal: company_name)" style="padding:6px;width:200px;border:1px solid #ccc;border-radius:4px;" />
        <input type="text" id="new_kv_val" placeholder="Nilai Value" style="padding:6px;width:300px;border:1px solid #ccc;border-radius:4px;" />
        <button type="button" onclick="addCustomKV()" style="padding:6px 15px;background:#007bff;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:bold;">+ Tambah Setting</button>
    </div>

    <table id="tableSettingsKV" width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;border:1px solid #ddd;">
        <thead>
            <tr style="background:#e9ecef;text-align:left;">
                <th width="5%" style="border:1px solid #ddd;padding:8px;text-align:center;">No</th>
                <th width="30%" style="border:1px solid #ddd;padding:8px;">Key (Kunci)</th>
                <th width="45%" style="border:1px solid #ddd;padding:8px;">Value (Nilai)</th>
                <th width="20%" style="border:1px solid #ddd;padding:8px;text-align:center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (!empty($all_settings)) {
                $no = 1;
                foreach ($all_settings as $st) {
            ?>
                <tr id="row_<?php echo htmlspecialchars($st->key); ?>" style="border:1px solid #ddd;">
                    <td style="border:1px solid #ddd;text-align:center;"><?php echo $no++; ?></td>
                    <td style="border:1px solid #ddd;">
                        <code style="font-size:13px;font-weight:bold;color:#0056b3;"><?php echo htmlspecialchars($st->key); ?></code>
                    </td>
                    <td style="border:1px solid #ddd;">
                        <input type="text" class="input-kv-val" data-key="<?php echo htmlspecialchars($st->key); ?>" value="<?php echo htmlspecialchars($st->value); ?>" style="width:95%;padding:5px;border:1px solid #ccc;border-radius:3px;" />
                    </td>
                    <td style="border:1px solid #ddd;text-align:center;">
                        <button type="button" onclick="updateKV('<?php echo htmlspecialchars($st->key); ?>')" style="padding:4px 10px;background:#17a2b8;color:#fff;border:none;border-radius:3px;cursor:pointer;">Update</button>
                        <button type="button" onclick="deleteKV('<?php echo htmlspecialchars($st->key); ?>')" style="padding:4px 10px;background:#dc3545;color:#fff;border:none;border-radius:3px;cursor:pointer;margin-left:5px;">Hapus</button>
                    </td>
                </tr>
            <?php 
                }
            } else {
            ?>
                <tr>
                    <td colspan="4" style="text-align:center;padding:15px;color:#888;">Belum ada data di tabel <code>settings</code>.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script type="text/javascript">
function toggleKeyVisibility() {
    var field = document.getElementById('google_api_key');
    if (field.type === "password") {
        field.type = "text";
    } else {
        field.type = "password";
    }
}

function addCustomKV() {
    var key = $('#new_kv_key').val().trim();
    var val = $('#new_kv_val').val().trim();
    
    if (!key) {
        alert('Silakan masukkan nama Key!');
        return;
    }
    
    $.ajax({
        url: '<?php echo base_url(); ?>settings/simpan_kv',
        type: 'POST',
        data: { key: key, value: val },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                alert('Setting berhasil ditambahkan!');
                location.reload();
            } else {
                alert('Gagal: ' + res.message);
            }
        },
        error: function() {
            alert('Terjadi kesalahan koneksi.');
        }
    });
}

function updateKV(key) {
    var val = $('input.input-kv-val[data-key="' + key + '"]').val();
    $.ajax({
        url: '<?php echo base_url(); ?>settings/simpan_kv',
        type: 'POST',
        data: { key: key, value: val },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                alert('Key ' + key + ' berhasil diperbarui!');
            } else {
                alert('Gagal: ' + res.message);
            }
        },
        error: function() {
            alert('Terjadi kesalahan koneksi.');
        }
    });
}

function deleteKV(key) {
    if (!confirm('Apakah Anda yakin ingin menghapus setting ' + key + '?')) {
        return;
    }
    $.ajax({
        url: '<?php echo base_url(); ?>settings/hapus_kv',
        type: 'POST',
        data: { key: key },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                $('#row_' + key).remove();
                alert('Setting ' + key + ' berhasil dihapus!');
            } else {
                alert('Gagal: ' + res.message);
            }
        },
        error: function() {
            alert('Terjadi kesalahan koneksi.');
        }
    });
}
</script>
