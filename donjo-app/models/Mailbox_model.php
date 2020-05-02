<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Mailbox_model extends CI_Model {

	/**
	 * Gunakan model ini untuk memindahkan semua method terkait mailbox layanan mandiri.
	 * Dimana layanan mailbox memiliki perlakuan yang sepenuhnya berbeda dengan komentar web
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model('referensi_model');
		
	}

	public function autocomplete($kat=1)
	{
		$this->db->select('k.*, p.nik, p.nama, k.subjek');
		$this->list_data_sql($kat);
		$data = $this->db->get()->result_array();
		
		return autocomplete_data_ke_str($data);
	}

	public function list_data($o=0, $offset=0, $limit=500, $kat=1)
	{
		$this->db->select('k.*, p.nik, p.nama, u.nama as nama_user');
		$this->list_data_sql($kat);
		
		switch ($o)
		{
			case 1: $this->db->order_by('u.nama', DESC); break;
			case 2: $this->db->order_by('u.nama', ASC);; break;
			case 3: $this->db->order_by('p.nama', DESC); break;
			case 4: $this->db->order_by('p.nama', ASC);; break;
			case 5: $this->db->order_by('k.baca', DESC); break;
			case 6: $this->db->order_by('k.baca', ASC); break;
			case 7: $this->db->order_by('created_at', DESC); break;
			case 8: $this->db->order_by('created_at', ASC); break;

			default: $this->db->order_by('created_at', DESC); break;
		}

		$data = $this->db->limit($limit, $offset)->get()->result_array();
		$j = $offset;
		for ($i=0; $i<count($data); $i++)
		{
			$data[$i]['no'] = $j + 1;
			$j++;
		}
		return $data;
	}

	private function list_data_sql($kat=1)
	{
		/* Alur untuk dashboar/admin
			1. Inbox : Semua yg memiliki akses modul ini dpt melihat semua inbox
			2. Outbox : Selain admin (1) masing2 hanya bisa melihat pesan yg dia kirim
		*/

		if ($kat == 1) {
			$this->db->join('tweb_penduduk p','p.id = k.id_pengirim', 'left') //pengirim
				->join('user u','u.id = k.id_penerima', 'left') //penerima
				->where('k.tipe', 1); 
		}else{			
			$this->db->join('user u','u.id = k.id_pengirim', 'left') //pengirim
				->join('tweb_penduduk p','p.id = k.id_penerima', 'left') //penerima
				->where('k.tipe', 2); 
			
			$id_group = $_SESSION['grup']; // Yg mempunyai akses ke modul
			$id_user = $_SESSION['user']; // Admin (user 1) dpt melihat pesan yg dikirim operator/yg mempunyai akses ke modul ini
			if ($id_group !=1){
				$this->db->where('k.id_group', $id_group)->where('k.id_pengirim', $id_user);
			}
		}

		$this->search_sql();
		$this->filter_sql();
		$this->filter_nik_sql();
		$this->db->from('kotak_pesan k');
	}

	private function filter_nik_sql()
	{
		if (isset($_SESSION['filter_nik']))
		{
			$kf = $_SESSION['filter_nik'];
			$this->db->where('p.nik', $kf);
		}
	}

	private function search_sql()
	{
		if (isset($_SESSION['cari']))
		{
			$cari = $_SESSION['cari'];
			$kw = $this->db->escape_like_str($cari);
			$this->db->like('p.nik', $kw)->or_like('p.nama', $kw)->or_like('k.subjek', $kw);
		}
	}

	private function filter_sql()
	{		
		if (isset($_SESSION['filter']))
		{
			$kf = $_SESSION['filter'];
			if ($kf == 3) {
				$this->db->where('k.status', 1);
			}
			else 
			{
				$this->db->where('k.baca', $kf);
				$this->db->where('k.status', 0);				
			}
		}
		else
		{
			unset($_SESSION['filter']);
			$this->db->where('k.status', 0);
		}
	}

	public function paging($p=1, $o=0, $kat=1)
	{
		$this->db->select('COUNT(*) AS jml');
		$this->list_data_sql($kat);
		$row = $this->db->get()->row_array();

		$this->load->library('paging');
		$cfg['page'] = $p;
		$cfg['per_page'] = $_SESSION['per_page'];
		$cfg['num_rows'] = $jml;
		$this->paging->init($cfg);

		return $this->paging;
	}


	public function baca($id, $baca)
	{
		$outp = $this->db->where('id', $id)
			->update('kotak_pesan', array('baca' => $baca));
		
		status_sukses($outp); //Tampilkan Pesan
	}

	public function archive($id='', $semua=false)
	{
		if (!$semua) $this->session->success = 1;
		
		$archive = array(
			'status' => 1,
			'updated_at' => date('Y-m-d H:i:s')
		);
		$outp = $this->db->where('id', $id)->update('kotak_pesan', $archive);

		status_sukses($outp, $gagal_saja=true); //Tampilkan Pesan
	}

	public function archive_all()
	{
		$this->session->success = 1;

		$id_cb = $_POST['id_cb'];
		foreach ($id_cb as $id)
		{
			$this->archive($id, $semua=true);
		}
	}

	public function insert($post)
	{
		$data = array(
				'id_pengirim' => $_SESSION['user'],
				'id_penerima' => $this->input->post('id_penerima'),
				'subjek' => $this->input->post('subjek'),
				'isi_pesan' => $this->input->post('isi_pesan'),
				'tipe' => 2,
				'baca' => 2,
				'status' => 0,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
				);

		$outp = $this->db->insert('kotak_pesan', $data);
		status_sukses($outp);
	}

	public function list_menu()
	{
		return $this->referensi_model->list_kode_array(KATEGORI_MAILBOX);
	}

	public function get_kat_nama($kat)
	{
		$sub_menu = $this->list_menu();	
		$data = $sub_menu[$kat];
		return $data;
	}

	public function get_mailbox($id=0, $kat=1)
	{
		$this->db->select('k.*, p.nik, p.nama, u.nama as nama_user');
		$this->list_data_sql($kat);

		$data = $this->db->where('k.id', $id)
			->get()
			->row_array();

		return $data;
	}


	// Bagian ini untuk web
	
	/**
	 * Tipe 1: Inbox untuk admin, Outbox untuk pengguna layanan mandiri
	 * Tipe 2: Outbox untuk admin, Inbox untuk pengguna layanan mandiri
	 */

	public function get_inbox_user($id)
	{
		$outp = $this->db
			->where('id_penerima', $id)
			->where('status', 0)
			->order_by('created_at', 'DESC')
			->get('kotak_pesan')
			->result_array();
			
		$j = 1;
		for ($i=0; $i < count($outp); $i++) 
		{ 
			$outp[$i]['no'] = $j++;
		}
		return $outp;
	}

	public function get_outbox_user($id)
	{
		$outp = $this->db
			->where('id_pengirim', $id)
			->where('status', 0)
			->order_by('created_at', 'DESC')
			->get('kotak_pesan')
			->result_array();

		$j = 1;
		for ($i=0; $i < count($outp); $i++) 
		{ 
			$outp[$i]['no'] = $j++;
		}
		return $outp;
	}

	public function insert_web()
	{
		$data = array(
				'id_pengirim' => $this->session->userdata('id'),
				'id_penerima' => 1, // id_penerima selalu ditujukan ke admin namun pd inbox dpt dilihat oleh operator/yg memiliki akses ke modul
				'subjek' => $this->input->post('subjek'),
				'isi_pesan' => $this->input->post('isi_pesan'),
				'tipe' => 1,
				'baca' => 2,
				'status' => 0,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
				);
		$outp = $this->db->insert('kotak_pesan', $data);
		if ($outp) $_SESSION['success'] = 1;
		else $_SESSION['success'] = -1;
	}

	public function get_pesan($id=0)
	{
		$data = $this->db->where('id', $id)
			->get('kotak_pesan')
			->row_array();

		return $data;
	}
}
?>