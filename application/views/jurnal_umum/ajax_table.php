	<div class="table-responsive" style="max-height: 500px; overflow-y: auto; overflow-x: auto;">
	<table id="dataTable" class="table table-row-dashed table-row-gray-300 table-bordered align-middle gs-0 gy-3 fs-7 text-nowrap">
		<thead class="position-sticky top-0 z-index-1">
            <tr class="fw-bold text-muted bg-light text-nowrap">
                <th class="ps-4 rounded-start text-center">No</th>
                <th class="text-center">No Jurnal</th>
                <th class="text-center">Tanggal</th>
                <th class="text-center">No Bukti</th>
                <th class="text-center">No Rek</th>
                <th>Nama Rek</th>
                <th>Keterangan</th>
                <th class="text-end">Debet</th>
                <th class="text-end">Kredit</th>
                <th class="pe-4 rounded-end text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
		<?php
		if ($data->num_rows() > 0) {
			$jml_dr = 0;
			$jml_kr = 0;
			$no = 1 + $hal;
			foreach ($data->result_array() as $db) {
				$tgl = $this->app_model->tgl_indo($db['tgl_jurnal']);
				$nama_rek = $this->app_model->CariNamaRek($db['no_rek']);
		?>
				<tr>
					<td align="center" width="20"><?php echo $no; ?></td>
					<td align="center" width="100"><span class="badge badge-light-primary fw-bold"><?php echo $db['no_jurnal']; ?></span></td>
					<td align="center" width="100"><?php echo $tgl; ?></td>
					<td align="center"><?php echo $db['no_bukti']; ?></td>
					<td align="center" width="80"><span class="badge badge-light-danger fw-bold"><?php echo $db['no_rek']; ?></span></td>
					<td><?php echo $nama_rek; ?></td>
					<td class="text-muted"><?php echo $db['ket']; ?></td>
					<td align="right" class="fw-bold" width="100"><?php echo number_format($db['debet']); ?></td>
					<td align="right" class="fw-bold" width="100"><?php echo number_format($db['kredit']); ?></td>
					<td align="center" width="60">
						<a class="btn btn-icon btn-sm btn-light-danger h-30px w-30px" href="<?php echo base_url(); ?>jurnal_umum/hapus/<?php echo $db['no_jurnal']; ?>" onClick="return confirm('Anda yakin ingin menghapus nomor jurnal ini?')">
							<i class="ki-outline ki-trash fs-5"></i>
						</a>
					</td>
				</tr>
			<?php
				$jml_dr = $jml_dr + $db['debet'];
				$jml_kr = $jml_kr + $db['kredit'];
				$no++;
			}
		} else {
			$jml_dr = 0;
			$jml_kr = 0;
			?>
			<tr>
				<td colspan="10" align="center">Tidak Ada Data</td>
			</tr>
		<?php
		}
		?>
        </tbody>
        <tfoot class="position-sticky bottom-0 z-index-1 bg-light">
            <tr class="fw-bold border-top border-gray-300">
                <td align="right" colspan="7" class="pe-4">TOTAL JUMLAH</td>
                <td align="right" class="text-success fs-6"><?php echo number_format($jml_dr); ?></td>
                <td align="right" class="text-success fs-6"><?php echo number_format($jml_kr); ?></td>
                <td></td>
            </tr>
        </tfoot>
	</table>
    </div>
	<div class="mt-4 d-flex justify-content-center ajax-pagination">
        <?php echo $paginator; ?>
    </div>
