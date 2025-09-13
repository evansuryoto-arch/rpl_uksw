<?php $this->load->view('templates/header'); ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    
    <!-- Notifikasi -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md shadow-sm" role="alert">
            <p><?php echo $this->session->flashdata('success'); ?></p>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md shadow-sm" role="alert">
            <p><?php echo $this->session->flashdata('error'); ?></p>
        </div>
    <?php endif; ?>

    <!-- Konten Utama: Manajemen MK dan Ajuan RPL -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Kolom Kiri: Manajemen Mata Kuliah -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold">Manajemen Mata Kuliah</h2>
                        <button data-action="add-course" class="bg-indigo-600 text-white font-semibold py-2 px-3 text-sm rounded-lg hover:bg-indigo-700">Tambah</button>
                    </div>
                </div>
                <div id="courses-container" class="space-y-4 p-4">
                     <?php if (!empty($courses)): ?>
                        <?php foreach($courses as $course): ?>
                        <div class="border p-3 rounded-md" id="course-<?php echo $course['id']; ?>">
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-md pr-4"><?php echo html_escape($course['name']); ?></h3>
                                <div class="flex-shrink-0">
                                    <button data-action="edit-course" data-course='<?php echo json_encode($course); ?>' class="text-blue-500 hover:text-blue-700 text-sm font-medium mr-2">Edit</button>
                                    <a href="<?php echo site_url('dashboard/delete_course/'.$course['id']); ?>" onclick="return confirm('Anda yakin ingin menghapus mata kuliah ini?')" class="text-red-500 hover:text-red-700 text-sm font-medium">Hapus</a>
                                </div>
                            </div>
                            <div class="text-sm text-gray-600 mt-2">
                                <p class="font-semibold">Capaian Pembelajaran:</p>
                                 <?php if (!empty($course['learning_outcomes'])): ?>
                                    <ul class="list-disc list-inside space-y-1 mt-1">
                                        <?php foreach ($course['learning_outcomes'] as $lo): ?>
                                            <li><?php echo html_escape($lo['description']); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-gray-500 italic">Belum ada.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center p-4">Belum ada mata kuliah.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Ajuan RPL -->
        <div class="lg:col-span-2">
             <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <h2 class="text-xl font-bold">Ajuan RPL</h2>
                </div>
                <div>
                   <table class="w-full text-sm text-left">
                      <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                         <tr>
                             <th class="p-3">Mahasiswa</th>
                             <th class="p-3">Mata Kuliah</th>
                             <th class="p-3">Status</th>
                             <th class="p-3 text-right">Aksi</th>
                         </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-200">
                         <?php if (!empty($applications)): ?>
                             <?php foreach ($applications as $app): ?>
                                 <tr class="hover:bg-gray-50">
                                     <td class="p-3 font-medium"><?php echo html_escape($app['student_name']); ?></td>
                                     <td class="p-3"><?php echo html_escape($app['course_name']); ?></td>
                                     <td class="p-3">
                                         <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $app['status'])); ?>">
                                             <?php echo html_escape($app['status']); ?>
                                         </span>
                                     </td>
                                     <td class="p-3 text-right">
                                        <button 
                                            data-action="view-details"
                                            data-application='<?php echo json_encode($app); ?>'
                                            class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            Lihat Detail
                                        </button>
                                     </td>
                                 </tr>
                             <?php endforeach; ?>
                         <?php else: ?>
                             <tr><td colspan="4" class="p-4 text-center text-gray-500">Belum ada ajuan di prodi ini.</td></tr>
                         <?php endif; ?>
                      </tbody>
                   </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Ajuan -->
<div id="details-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col">
        <div class="p-6 border-b">
            <h2 id="details-modal-title" class="text-xl font-bold">Detail Ajuan RPL</h2>
            <p id="details-modal-subtitle" class="text-sm text-gray-600"></p>
        </div>
        <div class="p-6 overflow-y-auto flex-grow" id="details-modal-content">
            <!-- Detail ajuan per CP akan dimuat di sini oleh JavaScript -->
        </div>
        <div class="p-6 bg-gray-50 border-t flex justify-end gap-4">
            <button type="button" data-action="close-modal" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Manajemen MK -->
<div id="course-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
        <form id="course-form" method="post">
            <div class="p-6 border-b">
                <h2 id="course-modal-title" class="text-xl font-bold">Tambah Mata Kuliah Baru</h2>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <label class="block text-sm font-medium">Nama Mata Kuliah <span class="text-red-500">*</span></label>
                    <input name="name" type="text" required class="w-full mt-1 p-2 border rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Capaian Pembelajaran</label>
                    <div id="learning-outcomes-container" class="space-y-2">
                        <!-- Akan diisi oleh JS -->
                    </div>
                    <button type="button" id="add-lo-btn" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 font-semibold">+ Tambah Capaian</button>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Keterangan Tambahan</label>
                    <textarea name="description" class="w-full mt-1 p-2 border rounded-md" rows="3"></textarea>
                </div>
            </div>
            <div class="p-6 bg-gray-50 border-t flex justify-end gap-4">
                <button type="button" data-action="close-modal" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
    </div>
</div>


<?php $this->load->view('templates/footer'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // === LOGIKA UNTUK MODAL DETAIL AJUAN ===
    const detailsModal = document.getElementById('details-modal');
    if (detailsModal) {
        const modalTitle = detailsModal.querySelector('#details-modal-title');
        const modalSubtitle = detailsModal.querySelector('#details-modal-subtitle');
        const modalContent = detailsModal.querySelector('#details-modal-content');

        document.body.addEventListener('click', function(e) {
            const viewButton = e.target.closest('[data-action="view-details"]');
            if (viewButton) {
                const app = JSON.parse(viewButton.dataset.application);
                
                modalTitle.textContent = `Detail Ajuan: ${app.course_name}`;
                modalSubtitle.textContent = `Mahasiswa: ${app.student_name}`;

                let detailsHtml = '<div class="space-y-6">';
                if (app.details && app.details.length > 0) {
                    app.details.forEach((detail, index) => {
                        detailsHtml += `
                            <div class="border rounded-lg p-4">
                                <p class="font-semibold text-gray-800 mb-3">
                                    <span class="text-indigo-600 font-bold">Capaian Pembelajaran ${index + 1}:</span> ${detail.lo_description}
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
                modalContent.innerHTML = detailsHtml;

                detailsModal.classList.remove('hidden');
            }
        });
    }

    // === LOGIKA UNTUK MODAL MANAJEMEN MK ===
    const courseModal = document.getElementById('course-modal');
    if (courseModal) {
        const courseForm = courseModal.querySelector('#course-form');
        const modalTitle = courseModal.querySelector('#course-modal-title');
        const loContainer = courseModal.querySelector('#learning-outcomes-container');
        
        const addLoField = (value = '') => {
            const newField = document.createElement('div');
            newField.className = 'flex items-center gap-2 mb-2';
            newField.innerHTML = `
                <input name="learning_outcomes[]" type="text" class="flex-grow p-2 border rounded-md" value="${value}" placeholder="Tulis capaian pembelajaran...">
                <button type="button" data-action="remove-lo" class="text-red-500 hover:text-red-700 font-bold p-1 rounded-full flex items-center justify-center h-6 w-6">&times;</button>
            `;
            loContainer.appendChild(newField);
        };

        document.body.addEventListener('click', e => {
            const action = e.target.dataset.action;

            if (action === 'add-course') {
                courseForm.action = "<?php echo site_url('dashboard/add_course'); ?>";
                modalTitle.textContent = "Tambah Mata Kuliah Baru";
                courseForm.reset();
                loContainer.innerHTML = '';
                addLoField(); // Tambah satu field kosong
                courseModal.classList.remove('hidden');
            }

            if (action === 'edit-course') {
                const course = JSON.parse(e.target.dataset.course);
                courseForm.action = `<?php echo site_url('dashboard/edit_course/'); ?>${course.id}`;
                modalTitle.textContent = "Edit Mata Kuliah";
                courseForm.querySelector('[name="name"]').value = course.name;
                courseForm.querySelector('[name="description"]').value = course.description;
                
                loContainer.innerHTML = '';
                if (course.learning_outcomes && course.learning_outcomes.length > 0) {
                    course.learning_outcomes.forEach(lo => addLoField(lo.description));
                } else {
                    addLoField();
                }
                courseModal.classList.remove('hidden');
            }
            
            if (action === 'remove-lo') {
                e.target.parentElement.remove();
            }

            if (e.target.closest('[data-action="close-modal"]')) {
                e.target.closest('.modal').classList.add('hidden');
            }
        });

        document.getElementById('add-lo-btn').addEventListener('click', () => addLoField());
    }
});
</script>

