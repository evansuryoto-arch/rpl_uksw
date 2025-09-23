

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
   

    <!-- Notifikasi Flash Message -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md shadow-sm" role="alert">
            <p class="font-bold">Sukses</p>
            <p><?php echo $this->session->flashdata('success'); ?></p>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md shadow-sm" role="alert">
            <p class="font-bold">Error</p>
            <p><?php echo $this->session->flashdata('error'); ?></p>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Kolom Kiri: Mata Kuliah Tersedia -->
        <div>
            <h2 class="text-xl font-bold mb-4">Mata Kuliah Tersedia untuk RPL</h2>
            <div class="space-y-4">
                <?php if (!empty($available_courses)): ?>
                    <?php foreach ($available_courses as $course): ?>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="font-bold text-lg"><?php echo html_escape($course['name']); ?></h3>
                            <div class="text-sm text-gray-600 mt-1 mb-3">
                                <p class="font-semibold">Capaian Pembelajaran Mata Kuliah:</p>
                                <?php if (!empty($course['learning_outcomes'])): ?>
                                    <ul class="list-disc list-inside space-y-1 mt-1">
                                        <?php foreach ($course['learning_outcomes'] as $lo): ?>
                                            <li><?php echo html_escape($lo['description']); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-gray-500 italic">Tidak ada capaian pembelajaran yang terdefinisi.</p>
                                <?php endif; ?>
                            </div>
                            <button 
                                data-action="apply-rpl" 
                                data-course-id="<?php echo $course['id']; ?>"
                                data-course-name="<?php echo html_escape($course['name']); ?>"
                                data-course-description="<?php echo html_escape($course['description']); ?>"
                                data-learning-outcomes='<?php echo json_encode($course['learning_outcomes']); ?>'
                                class="w-full bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors">
                                Ajukan RPL
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <p class="text-gray-500">Tidak ada mata kuliah yang tersedia untuk diajukan di prodi Anda saat ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Kolom Kanan: Ajuan RPL Saya -->
        <div>
            <h2 class="text-xl font-bold mb-4">Status Ajuan RPL Saya</h2>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="p-3">Mata Kuliah</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($my_applications)): ?>
                            <?php foreach ($my_applications as $app): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3"><?php echo html_escape($app['course_name']); ?></td>
                                    <td class="p-3">
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $app['status'])); ?>">
                                            <?php echo html_escape($app['status']); ?>
                                        </span>
                                    </td>
                                    <td class="p-3 text-right">
                                       <button disabled class="text-indigo-600 hover:text-indigo-900 font-medium opacity-50 cursor-not-allowed">Lihat Detail</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="p-4 text-center text-gray-500">Anda belum mengajukan RPL untuk mata kuliah manapun.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Universal -->
<div id="rpl-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col">
        <div class="p-6 border-b">
            <h2 id="modal-title" class="text-xl font-bold">Formulir Ajuan RPL</h2>
        </div>
        <div class="p-6 overflow-y-auto flex-grow">
            <!-- Konten modal akan diisi oleh JavaScript -->
            <div id="modal-content-container"></div>
        </div>
        <div class="p-6 bg-gray-50 border-t flex justify-end gap-4">
             <button type="button" id="cancel-btn" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Batal</button>
             <button type="submit" form="rpl-form" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Kirim Ajuan</button>
        </div>
    </div>
</div>


<?php $this->load->view('templates/footer'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('rpl-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalContentContainer = document.getElementById('modal-content-container');

    document.body.addEventListener('click', function(e) {
        if (e.target && e.target.dataset.action === 'apply-rpl') {
            const button = e.target;
            const courseId = button.dataset.courseId;
            const courseName = button.dataset.courseName;
            const learningOutcomes = JSON.parse(button.dataset.learningOutcomes);
            const courseDescription = button.dataset.courseDescription;

            modalTitle.textContent = `Formulir Ajuan RPL: ${courseName}`;
            
            let formContent = `<form id="rpl-form" action="<?php echo site_url('dashboard/apply_rpl'); ?>" method="post">`;
            formContent += `<input type="hidden" name="course_id" value="${courseId}">`;

            if (courseDescription) {
                formContent += `
                    <div class="mb-4 p-3 bg-blue-50 border rounded">
                        <h3 class="text-sm font-semibold text-gray-700">Deskripsi Mata Kuliah</h3>
                        <p class="text-gray-600 mt-1">${courseDescription}</p>
                    </div>
                `;
            }
            
            if (learningOutcomes && learningOutcomes.length > 0) {
                formContent += '<div class="space-y-8">';
                learningOutcomes.forEach((lo, index) => {
                    formContent += `
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <p class="font-semibold text-gray-800 mb-3">
                                <span class="text-indigo-600 font-bold">CPMK ${index + 1}:</span> ${lo.description}
                            </p>

                       

                            
                            <!-- Tingkat Penguasaan Diri -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tingkat Penguasaan Diri <span class="text-red-500">*</span></label>
                                <div class="flex flex-col sm:flex-row gap-4">
                                    <label class="flex items-center"><input type="radio" name="details[${lo.id}][penguasaan]" value="Sangat Baik" class="mr-2" required> Sangat Baik</label>
                                    <label class="flex items-center"><input type="radio" name="details[${lo.id}][penguasaan]" value="Baik" class="mr-2" required> Baik</label>
                                    <label class="flex items-center"><input type="radio" name="details[${lo.id}][penguasaan]" value="Tidak Pernah" class="mr-2" required> Tidak Pernah</label>
                                </div>
                            </div>

                            <!-- Deskripsi -->
                            <div class="mb-4">
                                <label for="deskripsi_${lo.id}" class="block text-sm font-medium text-gray-700">Deskripsi & Pengetahuan Relevan <span class="text-red-500">*</span></label>
                                <textarea id="deskripsi_${lo.id}" name="details[${lo.id}][deskripsi]" required class="w-full mt-1 p-2 border rounded-md" rows="3" placeholder="Contoh: Saya menguasai ini dari pengalaman kerja sebagai..."></textarea>
                            </div>

                            <!-- Link Bukti -->
                            <div>
                                <label for="bukti_${lo.id}" class="block text-sm font-medium text-gray-700">Link Bukti Pendukung (Google Drive/etc) <span class="text-red-500">*</span></label>
                                <input id="bukti_${lo.id}" name="details[${lo.id}][bukti]" type="url" required class="w-full mt-1 p-2 border rounded-md" placeholder="https://...">
                            </div>
                        </div>
                    `;
                });
                formContent += '</div>';
            } else {
                formContent += '<p class="text-center text-gray-600">Mata kuliah ini tidak memiliki Capaian Pembelajaran yang terdefinisi. Tidak dapat mengajukan RPL.</p>';
            }
            
            formContent += `</form>`;
            modalContentContainer.innerHTML = formContent;
            
            modal.classList.remove('hidden');
        }

        // Tombol batal di modal
        if (e.target && e.target.id === 'cancel-btn') {
            modal.classList.add('hidden');
        }
    });

    // Menutup modal jika klik di luar area konten
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
});
</script>

