<?php $this->load->view('global/validasi_form'); ?>
<form id="mainform" action="<?= $form_action; ?>" method="POST" enctype="multipart/form-data">
	<div class='modal-body'>
		<div class="form-group">
			<label for="file" class="control-label">File Peserta Program Bantuan : </label>
			<div class="input-group input-group-sm">
				<input type="text" class="form-control" id="file_path" name="userfile">
				<input type="file" class="hidden" id="file" name="userfile">
				<span class="input-group-btn">
					<button type="button" class="btn btn-info btn-flat" id="file_browser"><i class="fa fa-search"></i> Browse</button>
				</span>
			</div>
			<br/>
			<label class="control-label">Aturan tambahan :
				<p class="help-block">&emsp;<input type="checkbox" name="kosongkan" value="1"/> Kosongkan data peserta program bantuan</p>
				<p class="help-block">&emsp;<input type="checkbox" name="ganti" value="1"/> Ganti data lama jika data ditemukan sama</p>
				<p class="help-block">&emsp;<input type="checkbox" name="rand_kartu" value="1"/> Acak No. Kartu Peserta Jika Kosong</p>
			</label>
			<br/>
			<a href="<?= base_url("assets/import/format_import_peserta_bantuan.xlsx"); ?>" class="btn btn-social btn-flat bg-purple btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block text-center"><i class="fa fa-file-excel-o"></i> Contoh Format Import Peserta Bantuan</a>
		</div>
	</div>
	<div class="modal-footer">
		<button type="reset" class="btn btn-social btn-flat btn-danger btn-sm pull-left"><i class="fa fa-times"></i> Batal</button>
		<button type="submit" class="btn btn-social btn-flat btn-info btn-sm" id="ok"><i class="fa fa-check"></i> Simpan</button>
	</div>
</form>