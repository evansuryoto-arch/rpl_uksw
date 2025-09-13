<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {



    public function __construct() {
        parent::__construct();
        $this->load->model('Rpl_model', 'rpl');
        //  $this->load->helper('form');
        $this->load->helper(array('form', 'url'));
    }

    public function index() {
        if ($this->session->userdata('user_id')) {
            redirect('dashboard');
        }
        $this->load->view('auth/login');
    }

    public function register() {
        if ($this->session->userdata('user_id')) {
            redirect('dashboard');
        }
        $data['program_studies'] = $this->rpl->get_all_prodi();
        $this->load->view('auth/register', $data);
    }

    public function process_login() {
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        $user = $this->rpl->get_user_by_username($username);

        if ($user && password_verify($password, $user->password)) {
            $session_data = [
                'user_id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'role' => $user->role,
                'prodi_id' => $user->prodi_id,
            ];
            $this->session->set_userdata($session_data);
            redirect('dashboard');
        } else {
            $this->session->set_flashdata('error', 'Username atau password salah!');
            redirect('auth');
        }
    }

    public function process_register() {
        // Aturan validasi
        $this->form_validation->set_rules('name', 'Nama Lengkap', 'required');
        $this->form_validation->set_rules('username', 'Username', 'required|is_unique[users.username]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('prodi_id', 'Program Studi', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->register(); // Kembali ke form registrasi jika validasi gagal
        } else {
            $this->rpl->create_mahasiswa();
            $this->session->set_flashdata('success', 'Registrasi berhasil! Silakan login.');
            redirect('auth');
        }
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('auth');
    }
}
