import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

window.Alpine = Alpine;
window.Swal = Swal;

Alpine.start();

// Form dengan atribut data-konfirmasi akan meminta konfirmasi dulu sebelum dikirim
document.addEventListener('submit', (e) => {
    const form = e.target;
    const pesan = form.dataset?.konfirmasi;
    if (!pesan) return;

    e.preventDefault();

    // Simpan tombol pengirim agar name/value-nya tidak hilang
    const pengirim = e.submitter;

    Swal.fire({
        title: form.dataset.judul || 'Apakah Anda yakin?',
        text: pesan,
        icon: form.dataset.ikon || 'warning',
        showCancelButton: true,
        confirmButtonText: form.dataset.tombol || 'Ya, lanjutkan',
        cancelButtonText: form.dataset.batal || 'Batal',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
        focusCancel: true, // fokus awal di "Batal" agar aman dari salah tekan
    }).then((hasil) => {
        if (!hasil.isConfirmed) return;

        // Pertahankan name/value tombol submit (jika ada)
        if (pengirim?.name) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = pengirim.name;
            hidden.value = pengirim.value;
            form.appendChild(hidden);
        }

        form.submit(); // submit() tidak memicu event submit lagi, jadi tidak berulang
    });
});