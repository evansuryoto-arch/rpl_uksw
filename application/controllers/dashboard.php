<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('user_id')) {
            redirect('auth');
        }
        $this->load->model('Rpl_model', 'rpl');
        // TAMBAHKAN DUA BARIS INI untuk mengaktifkan form helper dan validasi
        $this->load->helper('form');
        $this->load->library('form_validation');
    }

    public function index()
    {
        // Ambil data pengguna dari sesi yang dibuat oleh Auth.php
        $user_id = $this->session->userdata('user_id');
        $role = $this->session->userdata('role');
        $prodi_id = $this->session->userdata('prodi_id');

        // == PERBAIKAN DI SINI ==
        // Ambil semua data sesi dan masukkan ke dalam variabel 'user' untuk dikirim ke view.
        $data['user'] = $this->session->userdata(); 
        
        $data['prodi'] = $this->rpl->get_prodi_by_id($prodi_id);
        
        // Baris ini tidak lagi diperlukan karena datanya sudah ada di dalam $data['user']
        // $data['user_name'] = $this->session->userdata('name');

        switch ($role) {
            case 'admin':
                $data['courses'] = $this->rpl->get_courses_by_prodi($prodi_id);
                $data['applications'] = $this->rpl->get_applications_by_prodi($prodi_id);
                $this->load->view('dashboard_admin', $data);
                break;
            case 'mahasiswa':
                $data['my_applications'] = $this->rpl->get_applications_by_student($user_id);
                $data['available_courses'] = $this->rpl->get_available_courses_for_student($user_id, $prodi_id);
                $this->load->view('dashboard_mahasiswa', $data);
                break;
            case 'asesor':
                $data['tasks'] = $this->rpl->get_tasks_for_assessor($user_id, $prodi_id);
                $this->load->view('dashboard_asesor', $data);
                break;
        }
    }

    // Aksi untuk Mahasiswa
    public function apply_rpl() {
          if ($this->session->userdata('role') != 'mahasiswa') redirect('dashboard');
        
        // Ambil data dari form yang dikirim oleh view
        $course_id = $this->input->post('course_id');
        $details = $this->input->post('details'); // Ini adalah array berisi data per capaian pembelajaran
        $student_id = $this->session->userdata('user_id');

        // Validasi sederhana untuk memastikan data tidak kosong
        if (empty($course_id) || empty($details)) {
            $this->session->set_flashdata('error', 'Terjadi kesalahan. Data ajuan tidak lengkap.');
            redirect('dashboard');
            return; // Hentikan eksekusi
        }

        // Panggil model untuk membuat aplikasi, sekarang dengan data yang lebih lengkap
        if ($this->rpl->create_application($student_id, $course_id, $details)) {
            $this->session->set_flashdata('success', 'Ajuan RPL berhasil dikirim.');
        } else {
             $this->session->set_flashdata('error', 'Gagal menyimpan ajuan RPL ke database.');
        }
        
        redirect('dashboard');
    }
    
    // Aksi untuk Asesor
    public function submit_assessment() {
        if ($this->session->userdata('role') != 'asesor') redirect('dashboard');
        
        $application_id = $this->input->post('application_id');
        $this->rpl->create_assessment();

        $this->run_decision_engine($application_id);

        $this->session->set_flashdata('success', 'Penilaian berhasil dikirim.');
        redirect('dashboard');
    }

    private function run_decision_engine($application_id) {
        $assessments = $this->rpl->get_assessments_for_application($application_id);

        if (count($assessments) >= 2) {
            $a1 = $assessments[0];
            $a2 = $assessments[1];
            $new_status = '';

            if ($a1['v'] != $a2['v'] || $a1['a'] != $a2['a']) {
                $new_status = 'Perlu Verifikasi';
            } else if ($a1['v'] && $a1['a']) {
                $new_status = 'Diakui';
            } else {
                $new_status = 'Ditolak';
            }
            $this->rpl->update_application_status($application_id, $new_status);
        }
    }

    // --- FUNGSI CRUD UNTUK ADMIN ---

    // GANTI FUNGSI add_course() ANDA DENGAN VERSI LENGKAP INI
    public function add_course()
    {
        if ($this->session->userdata('role') != 'admin') redirect('dashboard');

        $this->form_validation->set_rules('name', 'Nama Mata Kuliah', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', 'Gagal menambahkan mata kuliah. Nama tidak boleh kosong.');
        } else {
            $prodi_id = $this->session->userdata('prodi_id');
            
            $course_data = [
                'prodi_id'    => $prodi_id,
                'name'        => $this->input->post('name'),
                'description' => $this->input->post('description')
            ];

            $los_data = $this->input->post('learning_outcomes');

            if ($this->rpl->insert_course($course_data, $los_data)) {
                $this->session->set_flashdata('success', 'Mata kuliah berhasil ditambahkan!');
            } else {
                $this->session->set_flashdata('error', 'Terjadi kesalahan saat menyimpan ke database.');
            }
        }
        redirect('dashboard');
    }

    // TAMBAHKAN DUA FUNGSI BARU INI DI BAWAH FUNGSI add_course()
    public function edit_course($id)
    {
        if ($this->session->userdata('role') != 'admin') redirect('dashboard');

        $this->form_validation->set_rules('name', 'Nama Mata Kuliah', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', 'Gagal memperbarui mata kuliah. Nama tidak boleh kosong.');
        } else {
            $course_data = [
                'name'        => $this->input->post('name'),
                'description' => $this->input->post('description')
            ];
            
            $los_data = $this->input->post('learning_outcomes');

            if ($this->rpl->update_course($id, $course_data, $los_data)) {
                $this->session->set_flashdata('success', 'Mata kuliah berhasil diperbarui!');
            } else {
                $this->session->set_flashdata('error', 'Terjadi kesalahan saat memperbarui data.');
            }
        }
        redirect('dashboard');
    }

    public function delete_course($id)
    {
        if ($this->session->userdata('role') != 'admin') redirect('dashboard');

        if ($this->rpl->delete_course($id)) {
            $this->session->set_flashdata('success', 'Mata kuliah berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus mata kuliah.');
        }
        redirect('dashboard');
    }
}
