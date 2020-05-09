<?php
class Migrasi_2005_ke_2006 extends CI_model {

	public function up()
	{
		$this->grup_akses_covid19();

		// Ubah nama kode status penduduk
		$this->db->where('id', 2)
			->update('tweb_penduduk_status', array('nama' => 'TIDAK TETAP'));

		//Ganti nama folder widget menjadi widgets
		rename('desa/widget', 'desa/widgets');
		rename('desa/upload/widget', 'desa/upload/widgets');
		// Arahkan semua widget statis ubahan desa ke folder desa/widgets
		$list_widgets = $this->db->where('jenis_widget', 2)->get('widget')->result_array();
		foreach ($list_widgets as $widgets)
		{
			$ganti = str_replace('desa/widget', 'desa/widgets', $widgets['isi']); // Untuk versi 20.04-pasca ke atas
			$cek = explode('/', $ganti); // Untuk versi 20.04 ke bawah
			if ($cek[0] !== 'desa' AND $cek[1] === NULL)
			{ // agar migrasi bisa dijalankan berulang kali
				$this->db->where('id', $widgets['id'])->update('widget', array('isi' => 'desa/widgets/'.$widgets['isi']));
			}
		}
		// Sesuaikan dengan sql_mode STRICT_TRANS_TABLES
		$this->db->query("ALTER TABLE outbox MODIFY COLUMN CreatorID text NULL");

		$this->tema();
		// Hapus field sasaran
		if ($this->db->field_exists('sasaran', 'program_peserta'))
			$this->db->query('ALTER TABLE `program_peserta` DROP COLUMN `sasaran`');

	}

	private function grup_akses_covid19()
	{
		// Menambahkan menu 'Group / Hak Akses' covid19 table 'user_grup'
		$data[] = array(
			'id'=>'5',
			'nama' => 'Satgas Covid-19',
		);

		foreach ($data as $grup)
		{
			$sql = $this->db->insert_string('user_grup', $grup);
			$sql .= " ON DUPLICATE KEY UPDATE
			id = VALUES(id),
			nama = VALUES(nama)";
			$this->db->query($sql);
		}
	}

	// Pengaturan tema
	private function tema()
	{
		// Tambah Modul Pengunjung pada Admin WEB
		$data = array(
				'id' => 205,
				'modul' => 'Tema',
				'url' => 'tema',
				'aktif' => 1,
				'ikon' => 'fa-object-group',
				'urut' => 6,
				'level' => 2,
				'hidden' => 0,
				'ikon_kecil' => 'fa-object-group',
				'parent' => 11
				);
		$sql = $this->db->insert_string('setting_modul', $data);
		$sql .= " ON DUPLICATE KEY UPDATE
				modul = VALUES(modul),
				url = VALUES(url),
				aktif = VALUES(aktif),
				ikon = VALUES(ikon),
				urut = VALUES(urut),
				level = VALUES(level),
				hidden = VALUES(hidden),
				ikon_kecil = VALUES(ikon_kecil),
				parent = VALUES(parent)
				";
		$this->db->query($sql);
	}

}
