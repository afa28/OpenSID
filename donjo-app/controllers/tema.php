<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Tema extends Admin_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('header_model');
		$this->load->model('theme_model');
		$this->load->library('session');
		$this->load->helper("file");

		$this->modul_ini = 11;
		$this->sub_modul_ini = 205;
	}

	public function index()
	{
		$data['list_tema'] = $this->theme_model->list_all();
		$this->setting_model->load_options();

		$header = $this->header_model->get_data();

		$this->load->view('header', $header);
		$this->load->view('nav', $nav);
		$this->load->view('tema/table', $data);
		$this->load->view('footer');
	}

	//hapus folder dan file
	public function delete($tema)
	{
		//delete_files("desa/themes/".$tema."/", TRUE);
		//rmdir("desa/themes/lupa/");
		//$this->delete_directory($tema);
		@delete_folder(FCPATH.'desa/themes/'.$tema);
		//delete_file($path, true, false, 1);

		redirect('tema');
	}

	//ganti tema
	public function change($folder, $tema = NULL)
	{
		if($tema != NULL){
			$themes = $folder.'/'.$tema;
		}
		else
		{
			$themes = $folder;
		}

		$this->db->where('key', 'web_theme')->update('setting_aplikasi', array('value' => $themes));

		redirect('tema');
	}

	public function delete_directory($folder_name)
	{
		$this->load->helper('file');

		$dir_path = 'desa/themes/'.$folder_name;
		$del_path = './desa/themes/'.$folder_name.'/';

		if(is_dir($dir_path))
		{
			delete_files($del_path, true, false, 1);
			rmdir($dir_path);

			return true;
		}

		return false;
	}

}
