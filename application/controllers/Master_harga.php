<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Master_harga extends CI_Controller {

	public function index()
	{
		if (empty($this->session->userdata('logged_in'))) {
			redirect('login');
		}

		$d['judul'] = "Master Harga Jual";
		$d['title'] = "Master Harga Jual";
		
		$d['user'] = (object) [
			'nama_lengkap' => $this->session->userdata('nama_lengkap'),
			'level'        => $this->session->userdata('level'),
			'email'        => $this->session->userdata('username') . '@accounting.test'
		];

        $text = "SELECT * FROM master_harga ORDER BY id DESC";
        $d['data'] = $this->app_model->manualQuery($text);

		$d['content'] = 'master_harga/index';
		$this->load->view('templates/main', $d);
	}

    public function simpan()
    {
        if (empty($this->session->userdata('logged_in'))) {
			return $this->output->set_content_type('application/json')
				->set_status_header(401)
				->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
		}

        $id = $this->input->post('id');
        $deskripsi = $this->input->post('deskripsi');
        $harga_jual = $this->input->post('harga_jual');

        if (empty($deskripsi) || empty($harga_jual)) {
            return $this->output->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Deskripsi dan Harga Jual harus diisi']));
        }

        if (empty($id)) {
            $this->app_model->insertData("master_harga", [
                'deskripsi' => $deskripsi,
                'harga_jual' => str_replace(',', '', $harga_jual)
            ]);
        } else {
            $this->app_model->updateData("master_harga", [
                'deskripsi' => $deskripsi,
                'harga_jual' => str_replace(',', '', $harga_jual)
            ], ['id' => $id]);
        }

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'message' => 'Berhasil menyimpan harga jual']));
    }

    public function edit()
    {
        if (empty($this->session->userdata('logged_in'))) {
			return $this->output->set_content_type('application/json')
				->set_status_header(401)
				->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
		}

        $id = $this->input->post('id');
        $text = "SELECT * FROM master_harga WHERE id='$id'";
        $data = $this->app_model->manualQuery($text)->row();

        if ($data) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'data' => $data]));
        } else {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']));
        }
    }

    public function hapus()
    {
        if (empty($this->session->userdata('logged_in'))) {
			return $this->output->set_content_type('application/json')
				->set_status_header(401)
				->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
		}

        $id = $this->input->post('id');
        if ($id) {
            $this->app_model->manualQuery("DELETE FROM master_harga WHERE id='$id'");
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Berhasil menghapus data']));
        } else {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan']));
        }
    }
}
