<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rpl_model extends CI_Model {

    // --- FUNGSI OTENTIKASI & PENGGUNA ---

    public function get_user_by_username($username) {
        return $this->db->get_where('users', ['username' => $username])->row();
        
    }

    public function create_mahasiswa() {
        $data = [
            'name' => $this->input->post('name'),
            'username' => $this->input->post('username'),
            'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            'role' => 'mahasiswa',
            'prodi_id' => $this->input->post('prodi_id')
        ];
        return $this->db->insert('users', $data);
    }

    // --- FUNGSI PROGRAM STUDI ---

    public function get_all_prodi() {
        return $this->db->get('program_studies')->result();
    }

    public function get_prodi_by_id($id) {
        return $this->db->get_where('program_studies', ['id' => $id])->row();
    }

    // --- FUNGSI MATA KULIAH (CRUD) ---

    public function get_courses_by_prodi($prodi_id) {
        $courses = $this->db->get_where('courses', ['prodi_id' => $prodi_id])->result_array();
        // Untuk setiap mata kuliah, ambil juga capaian pembelajarannya
        foreach ($courses as $key => $course) {
            $courses[$key]['learning_outcomes'] = $this->get_learning_outcomes_by_course($course['id']);
        }
        return $courses;
    }

    public function get_learning_outcomes_by_course($course_id) {
        return $this->db->get_where('learning_outcomes', ['course_id' => $course_id])->result_array();
    }

    public function insert_course($course_data, $los_data) {
        $this->db->trans_start();
        $this->db->insert('courses', $course_data);
        $course_id = $this->db->insert_id();

        if (!empty($los_data)) {
            $batch_data = [];
            foreach ($los_data as $lo_desc) {
                if (!empty(trim($lo_desc))) {
                    $batch_data[] = [
                        'course_id' => $course_id,
                        'description' => $lo_desc
                    ];
                }
            }
            if (!empty($batch_data)) {
                $this->db->insert_batch('learning_outcomes', $batch_data);
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function update_course($course_id, $course_data, $los_data) {
        $this->db->trans_start();
        $this->db->where('id', $course_id)->update('courses', $course_data);
        $this->db->where('course_id', $course_id)->delete('learning_outcomes');

        if (!empty($los_data)) {
            $batch_data = [];
            foreach ($los_data as $lo_desc) {
                if (!empty(trim($lo_desc))) {
                    $batch_data[] = [
                        'course_id' => $course_id,
                        'description' => $lo_desc
                    ];
                }
            }
            if (!empty($batch_data)) {
                $this->db->insert_batch('learning_outcomes', $batch_data);
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function delete_course($course_id) {
        $this->db->trans_start();
        // Hapus juga semua yang terkait (capaian pembelajaran, ajuan, dll)
        $this->db->where('course_id', $course_id)->delete('learning_outcomes');
        $this->db->where('course_id', $course_id)->delete('applications');
        $this->db->where('id', $course_id)->delete('courses');
        $this->db->trans_complete();
        return $this->db->trans_status();
    }


    // --- FUNGSI AJUAN RPL ---
    
    public function get_available_courses_for_student($student_id, $prodi_id) {
        // Ambil ID semua mata kuliah yang sudah diajukan oleh mahasiswa
        $subquery = $this->db->select('course_id')->where('student_id', $student_id)->get_compiled_select('applications');
        
        // Ambil semua mata kuliah di prodi tersebut yang ID-nya TIDAK ADA di dalam subquery
        $this->db->where("id NOT IN ($subquery)", NULL, FALSE);
        $this->db->where('prodi_id', $prodi_id);
        $courses = $this->db->get('courses')->result_array();

        foreach ($courses as $key => $course) {
            $courses[$key]['learning_outcomes'] = $this->get_learning_outcomes_by_course($course['id']);
        }
        return $courses;
    }

    public function get_applications_by_student($student_id) {
        $this->db->select('applications.*, courses.name as course_name');
        $this->db->from('applications');
        $this->db->join('courses', 'courses.id = applications.course_id');
        $this->db->where('applications.student_id', $student_id);
        return $this->db->get()->result_array();
    }

    public function create_application($student_id, $course_id, $details) {
        $this->db->trans_start();

        $application_data = [
            'student_id' => $student_id,
            'course_id'  => $course_id,
            'status'     => 'Menunggu Penilaian'
        ];
        $this->db->insert('applications', $application_data);
        $application_id = $this->db->insert_id();

        $batch_details_data = [];
        foreach ($details as $lo_id => $data) {
            $batch_details_data[] = [
                'application_id'  => $application_id,
                'lo_id'           => $lo_id,
                'self_assessment' => $data['penguasaan'],
                'description'     => $data['deskripsi'],
                'evidence_link'   => $data['bukti']
            ];
        }

        if (!empty($batch_details_data)) {
            $this->db->insert_batch('application_details', $batch_details_data);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_applications_by_prodi($prodi_id) {
        $this->db->select('applications.*, users.name as student_name, courses.name as course_name');
        $this->db->from('applications');
        $this->db->join('users', 'users.id = applications.student_id');
        $this->db->join('courses', 'courses.id = applications.course_id');
        $this->db->where('courses.prodi_id', $prodi_id);
        return $this->db->get()->result_array();

        // INI untuk mengambil data rincian
        foreach ($applications as $key => $app) {
            $applications[$key]['details'] = $this->get_application_details($app['id']);
        }
        
        return $applications;
    }

    public function get_application_details($application_id) {
        $this->db->select('ad.*, lo.description as lo_description');
        $this->db->from('application_details as ad');
        $this->db->join('learning_outcomes as lo', 'lo.id = ad.lo_id');
        $this->db->where('ad.application_id', $application_id);
        return $this->db->get()->result_array();
    }


    // --- FUNGSI ASESMEN ---

    public function get_tasks_for_assessor($assessor_id, $prodi_id) {
        $subquery = $this->db->select('application_id')
                             ->from('assessments')
                             ->where('assessor_id', $assessor_id)
                             ->get_compiled_select();

        $this->db->select('applications.*, users.name as student_name, courses.name as course_name');
        $this->db->from('applications');
        $this->db->join('users', 'users.id = applications.student_id');
        $this->db->join('courses', 'courses.id = applications.course_id');
        $this->db->where('courses.prodi_id', $prodi_id);
        $this->db->where('applications.status', 'Menunggu Penilaian');
        $this->db->where("applications.id NOT IN ($subquery)", NULL, FALSE);
        
        $applications = $this->db->get()->result_array();

        foreach ($applications as $key => $app) {
            $applications[$key]['details'] = $this->get_application_details($app['id']);
        }
        
        return $applications;
    }
    
    public function create_assessment() {
        $data = [
            'application_id' => $this->input->post('application_id'),
            'assessor_id'    => $this->session->userdata('user_id'),
            'v'              => $this->input->post('v') ? 1 : 0,
            'a'              => $this->input->post('a') ? 1 : 0,
            't'              => $this->input->post('t') ? 1 : 0,
            'm'              => $this->input->post('m') ? 1 : 0,
        ];
        return $this->db->insert('assessments', $data);
    }

    public function get_assessments_for_application($application_id) {
        $this->db->where('application_id', $application_id);
        return $this->db->get('assessments')->result_array();
    }

    public function update_application_status($application_id, $status) {
        $this->db->where('id', $application_id);
        return $this->db->update('applications', ['status' => $status]);
    }
}

