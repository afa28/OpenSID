<?php

/**
 * File ini:
 *
 * File ini controller utama yg mengatur controller lain
 *
 * donjo-app/core/MY_Controller.php
 *
 */

/**
 *
 * File ini bagian dari:
 *
 * OpenSID
 *
 * Sistem informasi desa sumber terbuka untuk memajukan desa
 *
 * Aplikasi dan source code ini dirilis berdasarkan lisensi GPL V3
 *
 * Hak Cipta 2009 - 2015 Combine Resource Institution (http://lumbungkomunitas.net/)
 * Hak Cipta 2016 - 2020 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 *
 * Dengan ini diberikan izin, secara gratis, kepada siapa pun yang mendapatkan salinan
 * dari perangkat lunak ini dan file dokumentasi terkait ("Aplikasi Ini"), untuk diperlakukan
 * tanpa batasan, termasuk hak untuk menggunakan, menyalin, mengubah dan/atau mendistribusikan,
 * asal tunduk pada syarat berikut:

 * Pemberitahuan hak cipta di atas dan pemberitahuan izin ini harus disertakan dalam
 * setiap salinan atau bagian penting Aplikasi Ini. Barang siapa yang menghapus atau menghilangkan
 * pemberitahuan ini melanggar ketentuan lisensi Aplikasi Ini.

 * PERANGKAT LUNAK INI DISEDIAKAN "SEBAGAIMANA ADANYA", TANPA JAMINAN APA PUN, BAIK TERSURAT MAUPUN
 * TERSIRAT. PENULIS ATAU PEMEGANG HAK CIPTA SAMA SEKALI TIDAK BERTANGGUNG JAWAB ATAS KLAIM, KERUSAKAN ATAU
 * KEWAJIBAN APAPUN ATAS PENGGUNAAN ATAU LAINNYA TERKAIT APLIKASI INI.
 *
 * @package	OpenSID
 * @author	Tim Pengembang OpenDesa
 * @copyright	Hak Cipta 2009 - 2015 Combine Resource Institution (http://lumbungkomunitas.net/)
 * @copyright	Hak Cipta 2016 - 2020 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license	http://www.gnu.org/licenses/gpl.html	GPL V3
 * @link 	https://github.com/OpenSID/OpenSID
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

	/*
	 * Common data
	 */
	public $user;
	public $settings;
	public $includes;
	public $current_uri;
	public $theme;
	public $template;
	public $error;

	/*
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->model('database_model');
		$this->database_model->cek_migrasi();
		// Gunakan tema klasik kalau setting tema kosong atau folder di desa/themes untuk tema pilihan tidak ada
		// if (empty($this->setting->web_theme) OR !is_dir(FCPATH.'desa/themes/'.$this->setting->web_theme))
		$theme = preg_replace("/desa\//","",strtolower($this->setting->web_theme)) ;
		$theme_folder = preg_match("/desa\//", strtolower($this->setting->web_theme)) ? "desa/themes" : "themes";
		if (empty($this->setting->web_theme) OR !is_dir(FCPATH.$theme_folder.'/'.$theme))
		{
			$this->theme = 'klasik';
			$this->theme_folder = 'themes';
		}
		else
		{
			$this->theme = $theme;
			$this->theme_folder = $theme_folder;
		}
		// Variabel untuk tema
		$this->template = "../../{$this->theme_folder}/{$this->theme}/template.php";
	}

	function set_title($page_title)
	{
		$this->includes[ 'page_title' ] = $page_title;

		/*
		 * check wether page_header has been set or has a value
		 * if not, then set page_title as page_header
		 */
		$this->includes[ 'page_header' ] = isset( $this->includes[ 'page_header' ] ) ? $this->includes[ 'page_header' ] : $page_title;
		return $this;
	}

	/*
	 * Set Page Header
	 * sometime, we want to have page header different from page title
	 * so, use this function
	 * --------------------------------------
	 * @author	Arif Rahman Hakim
	 * @since	Version 3.0.5
	 * @access	public
	 * @param	string
	 * @return	chained object
	 */
	function set_page_header($page_header)
	{
		$this->includes[ 'page_header' ] = $page_header;
		return $this;
	}

	/*
	 * Set Template
	 * sometime, we want to use different template for different page
	 * for example, 404 template, login template, full-width template, sidebar template, etc.
	 * so, use this function
	 * --------------------------------------
	 * @author	Arif Rahman Hakim
	 * @since	Version 3.1.0
	 * @access	public
	 * @param	string, template file name
	 * @return	chained object
	 */
	function set_template($template_file = 'template.php')
	{
		// make sure that $template_file has .php extension
		$template_file = substr( $template_file, -4 ) == '.php' ? $template_file : ( $template_file . ".php" );

		$template_file_path = FCPATH . $this->theme_folder . '/' . $this->theme . "/" . $template_file;
		if (is_file($template_file_path))
			$this->template = "../../{$this->theme_folder}/{$this->theme}/{$template_file}";
		else
			$this->template = '../../themes/klasik/' . $template_file;
	}

	/*
	 * Bersihkan session cluster wilayah
	 */
	public function clear_cluster_session()
	{
		$cluster_session = array('dusun', 'rw', 'rt');
		foreach ($cluster_session as $session)
		{
			$this->session->unset_userdata($session);
		}
	}

}

class Web_Controller extends MY_Controller {

	/*
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->includes['folder_themes'] = '../../'.$this->theme_folder.'/'.$this->theme;
		$this->controller = strtolower($this->router->fetch_class());

		mandiri_timeout();
		$this->load->model('config_model');
		$this->load->model('first_m');
		$this->load->model('first_artikel_m');
		$this->load->model('teks_berjalan_model');
		$this->load->model('first_gallery_m');
		$this->load->model('first_menu_m');
		$this->load->model('web_menu_model');
		$this->load->model('first_penduduk_m');
		$this->load->model('penduduk_model');
		$this->load->model('surat_model');
		$this->load->model('keluarga_model');
		$this->load->model('web_widget_model');
		$this->load->model('web_gallery_model');
		$this->load->model('laporan_penduduk_model');
		$this->load->model('track_model');
		$this->load->model('keluar_model');
		$this->load->model('referensi_model');
		$this->load->model('keuangan_model');
		$this->load->model('keuangan_manual_model');
		$this->load->model('web_dokumen_model');
		$this->load->model('mailbox_model');
		$this->load->model('lapor_model');
		$this->load->model('program_bantuan_model');
		$this->load->model('keuangan_manual_model');
		$this->load->model('keuangan_grafik_model');
		$this->load->model('keuangan_grafik_manual_model');
		$this->load->model('plan_lokasi_model');
		$this->load->model('plan_area_model');
		$this->load->model('plan_garis_model');

		// Jika offline_mode dalam level yang menyembunyikan website,
		// tidak perlu menampilkan halaman website
		if ($this->setting->offline_mode == 2)
		{
			redirect('main');
		}
		elseif ($this->setting->offline_mode == 1)
		{
			// Hanya tampilkan website jika user mempunyai akses ke menu admin/web
			// Tampilkan 'maintenance mode' bagi pengunjung website
			$this->load->model('user_model');
			$grup	= $this->user_model->sesi_grup($_SESSION['sesi']);
			if ( ! $this->user_model->hak_akses($grup, 'web', 'b'))
			{
				redirect('main/maintenance_mode');
			}
		}
	}

	/*
	 * Jika file theme/view tidak ada, gunakan file klasik/view
	 * Supaya tidak semua layout atau partials harus diulangi untuk setiap tema
	 */
	public static function fallback_default($theme, $view)
	{
		$view = trim($view, '/');
		$theme_folder = self::get_instance()->theme_folder;
		$theme_view = "../../$theme_folder/$theme/$view";

		if (!is_file(APPPATH .'views/'. $theme_view))
		{
			$theme_view = "../../themes/klasik/$view";
		}

		return $theme_view;
	}

	public function _get_common_data(&$data)
	{
		$data['desa'] = $this->config_model->get_data();
		$data['menu_atas'] = $this->first_menu_m->list_menu_atas();
		$data['menu_atas'] = $this->first_menu_m->list_menu_atas();
		$data['menu_kiri'] = $this->first_menu_m->list_menu_kiri();
		$data['teks_berjalan'] = $this->teks_berjalan_model->list_data(TRUE);
		$data['slide_artikel'] = $this->first_artikel_m->slide_show();
		$data['slider_gambar'] = $this->first_artikel_m->slider_gambar();
		$data['w_cos'] = $this->web_widget_model->get_widget_aktif();

		$this->web_widget_model->get_widget_data($data);
		$data['data_config'] = $this->config_model->get_data();
		$data['flash_message'] = $this->session->flashdata('flash_message');
		if ($this->setting->apbdes_footer AND $this->setting->apbdes_footer_all)
		{
			$data['transparansi'] = $this->setting->apbdes_manual_input
			? $this->keuangan_grafik_manual_model->grafik_keuangan_tema()
			: $this->keuangan_grafik_model->grafik_keuangan_tema();
		}
		// Pembersihan tidak dilakukan global, karena artikel yang dibuat oleh
		// petugas terpecaya diperbolehkan menampilkan <iframe> dsbnya..
		$list_kolom = array(
			'arsip',
			'w_cos'
		);
		foreach ($list_kolom as $kolom)
		{
			$data[$kolom] = $this->security->xss_clean($data[$kolom]);
		}
	}

}

/*
 * Untuk API read-only, seperti Api_informasi_publik
 */
class Api_Controller extends MY_Controller {

	/*
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	protected function log_request()
	{
		$message = 'API Request '.$this->input->server('REQUEST_URI').' dari '.$this->input->ip_address();
		log_message('error', $message);
	}

}

class Admin_Controller extends MY_Controller {

	public $grup;
	public $CI = NULL;
	public $pengumuman = NULL;
	public $header;
	protected $nav = 'nav';
	protected $minsidebar = 0;
	public function __construct()
	{
		parent::__construct();
		$this->CI = CI_Controller::get_instance();
		$this->controller = strtolower($this->router->fetch_class());
		$this->load->model(['header_model', 'user_model', 'notif_model']);
		$this->grup	= $this->user_model->sesi_grup($_SESSION['sesi']);

		$this->load->model('modul_model');
		if (!$this->modul_model->modul_aktif($this->controller))
		{
			session_error("Fitur ini tidak aktif");
			redirect('/');
		}
		if (!$this->user_model->hak_akses($this->grup, $this->controller, 'b'))
		{
			if (empty($this->grup))
			{
				$_SESSION['request_uri'] = $_SERVER['REQUEST_URI'];
				redirect('siteman');
			}
			else
			{
				session_error("Anda tidak mempunyai akses pada fitur ini");
				unset($_SESSION['request_uri']);
				redirect('/');
			}
		}
		$this->cek_pengumuman();
		$this->header = $this->header_model->get_data();
	}

	private function cek_pengumuman()
	{
		if ($this->grup == 1) // hanya utk user administrator
		{
			$notifikasi = $this->notif_model->get_semua_notif();
			foreach($notifikasi as $notif)
			{
				$this->pengumuman = $this->notif_model->notifikasi($notif);
				if ($notif['jenis'] == 'persetujuan') break;
			}
		}
	}

	protected function redirect_hak_akses($akses, $redirect = '', $controller = '')
	{
		$kembali = $_SERVER['HTTP_REFERER'];

		if (empty($controller))
			$controller = $this->controller;
		if ( ! $this->user_model->hak_akses($this->grup, $controller, $akses))
		{
			session_error("Anda tidak mempunyai akses pada fitur ini");
			if (empty($this->grup)) redirect('siteman');
			empty($redirect) ? redirect($kembali) : redirect($redirect);
		}
	}

	public function cek_hak_akses($akses, $controller = '')
	{
		if (empty($controller))
			$controller = $this->controller;
		return $this->user_model->hak_akses($this->grup, $controller, $akses);
	}

	public function render($view, Array $data = [])
	{
		$this->header['minsidebar'] = $this->get_minsidebar();
		$this->load->view('header', $this->header);
		$this->load->view('nav');
		$this->load->view($view, $data);
		$this->load->view('footer');
	}

	/**
	 * Get the value of minsidebar
	 */
	public function get_minsidebar()
	{
		return $this->minsidebar;
	}

	/**
	 * Set the value of minsidebar
	 *
	 * @return  self
	 */
	public function set_minsidebar($minsidebar)
	{
		$this->minsidebar = $minsidebar;
		$this->header['minsidebar'] = $this->get_minsidebar();

		return $this;
	}

}
