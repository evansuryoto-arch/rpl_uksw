<?php $this->load->view('templates/header'); ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header Dashboard -->
    <!-- <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard Penilai</h1>
            <p class="text-gray-600">Selamat datang, <?php echo html_escape($user['name']); ?>! (<?php echo html_escape($prodi->name); ?>)</p>
        </div>
        <a href="<?php echo site_url('auth/logout'); ?>" class="bg-red-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-red-600 transition-colors">Keluar</a>
    </div> -->

    <!-- Notifikasi -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md shadow-sm" role="alert">
            <p><?php echo $this->session->flashdata('success'); ?></p>
        </div>
    <?php endif; ?>

    <!-- Daftar Tugas Penilaian -->
    <div>
        <h2 class="text-xl font-bold mb-4">Ajuan yang Perlu Dinilai</h2>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="p-3">Calon Mahasiswa</th>
                        <th class="p-3">Mata Kuliah</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (!empty($groupedTasks)): ?>
                        <?php foreach ($groupedTasks as $studentName => $studentTasks): ?>
                            <?php $rowspan = count($studentTasks); ?>
                            <?php foreach ($studentTasks as $i => $task): ?>
                                <tr class="hover:bg-gray-50">
                                    <?php if ($i === 0): ?>
                                        <!-- Nama mahasiswa hanya muncul sekali -->
                                        <td class="p-3 font-medium align-top" rowspan="<?= $rowspan ?>">
                                            <?php echo html_escape($studentName); ?>
                                        </td>
                                    <?php endif; ?>

                                    <!-- Mata kuliah -->
                                    <td class="p-3"><?php echo html_escape($task['course_name']); ?></td>

                                    <!-- Tombol aksi -->
                                    <td class="p-3 text-right">
                                        <button 
                                            data-action="assess-rpl"
                                            data-task='<?php echo json_encode($task); ?>'
                                            class="bg-indigo-600 text-white font-semibold py-1 px-3 rounded-lg hover:bg-indigo-700">
                                            Beri Penilaian
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="p-4 text-center text-gray-500">
                                Tidak ada ajuan yang perlu dinilai saat ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

                
            </table>
        </div>
    </div>
</div>

<!-- Modal Penilaian -->
<div id="assessment-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col">
        <div class="p-6 border-b">
            <h2 id="modal-title" class="text-xl font-bold">Instrumen Penilaian RPL</h2>
            <p id="modal-subtitle" class="text-sm text-gray-600"></p>
        </div>
        <div class="p-6 overflow-y-auto flex-grow" id="modal-details-container">
            <!-- Detail ajuan per CP akan dimuat di sini oleh JavaScript -->
        </div>
        <div class="p-6 bg-gray-50 border-t">
            <form id="assessment-form" action="<?php echo site_url('dashboard/submit_assessment'); ?>" method="post">
                <input type="hidden" name="application_id" id="application_id">
                <h3 class="font-bold mb-3">Instrumen Penilaian (V-A-T-M)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-center p-3 border rounded-lg hover:bg-gray-100 cursor-pointer">
                        <input type="checkbox" name="v" class="h-5 w-5 rounded text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-3 font-medium">Valid (V)</span>
                        <span class="ml-auto text-xs text-red-600 font-bold">Syarat penerimaan</span>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg hover:bg-gray-100 cursor-pointer">
                        <input type="checkbox" name="a" class="h-5 w-5 rounded text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-3 font-medium">Asli (A)</span>
                        <span class="ml-auto text-xs text-red-600 font-bold">Syarat penerimaan</span>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg hover:bg-gray-100 cursor-pointer">
                        <input type="checkbox" name="t" class="h-5 w-5 rounded text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-3 font-medium">Terkini (T)</span>
                        <span class="ml-auto text-xs text-gray-500">Opsional</span>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg hover:bg-gray-100 cursor-pointer">
                        <input type="checkbox" name="m" class="h-5 w-5 rounded text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-3 font-medium">Memadai (M)</span>
                        <span class="ml-auto text-xs text-gray-500">Opsional</span>
                    </label>
                </div>
            </form>
        </div>
        <div class="p-6 bg-gray-50 border-t flex justify-end gap-4">
            <button type="button" data-action="close-modal" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Batal</button>
            <button type="submit" form="assessment-form" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Kirim Penilaian</button>
        </div>
    </div>
</div>

<?php $this->load->view('templates/footer'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('assessment-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalSubtitle = document.getElementById('modal-subtitle');
    const modalDetailsContainer = document.getElementById('modal-details-container');
    const applicationIdInput = document.getElementById('application_id');
    const assessmentForm = document.getElementById('assessment-form');

    document.body.addEventListener('click', function(e) {
        const button = e.target.closest('[data-action="assess-rpl"]');
        if (button) {
            const task = JSON.parse(button.dataset.task);
            
            modalTitle.textContent = `Penilaian Ajuan: ${task.course_name}`;
            modalSubtitle.textContent = `Mahasiswa: ${task.student_name}`;
            applicationIdInput.value = task.id;

            let detailsHtml = '<div class="space-y-6">';
            if (task.details && task.details.length > 0) {
                task.details.forEach((detail, index) => {
                    detailsHtml += `
                        <div class="border rounded-lg p-4">
                            <p class="font-semibold text-gray-800 mb-3">
                                <span class="text-indigo-600 font-bold">Capaian Pembelajaran Mata Kuliah ${index + 1}:</span> ${detail.lo_description}
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="bg-gray-50 p-3 rounded-md">
                                    <p class="font-semibold text-gray-500 mb-1">Tingkat Penguasaan Diri:</p>
                                    <p class="font-bold text-gray-900">${detail.self_assessment}</p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-md">
                                    <p class="font-semibold text-gray-500 mb-1">Link Bukti Pendukung:</p>
                                    <a href="${detail.evidence_link}" target="_blank" class="text-blue-600 hover:underline break-all">${detail.evidence_link}</a>
                                </div>
                            </div>
                            <div class="mt-3 bg-gray-50 p-3 rounded-md">
                                <p class="font-semibold text-gray-500 mb-1 text-sm">Deskripsi & Pengetahuan Relevan:</p>
                                <p class="text-sm text-gray-800">${detail.description}</p>
                            </div>
                        </div>
                    `;
                });
            } else {
                detailsHtml += '<p class="text-center text-gray-500">Tidak ditemukan rincian ajuan untuk ditampilkan.</p>';
            }
            detailsHtml += '</div>';
            modalDetailsContainer.innerHTML = detailsHtml;

            modal.classList.remove('hidden');
        }

        if (e.target.closest('[data-action="close-modal"]')) {
            modal.classList.add('hidden');
            assessmentForm.reset();
        }
    });
});
</script>

