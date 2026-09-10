document.addEventListener('DOMContentLoaded', function() {
    const months = [
        "JANUARI", "FEBRUARI", "MARET", "APRIL", "MEI", "JUNI",
        "JULI", "AGUSTUS", "SEPTEMBER", "OKTOBER", "NOVEMBER", "DESEMBER"
    ];
    const days = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];
    let currentDate = new Date();

    function renderCalendar() {
        const datesElement = document.getElementById('dates');
        const monthYearElement = document.getElementById('monthYear');

        // Set bulan dan tahun
        monthYearElement.textContent = `${months[currentDate.getMonth()]} ${currentDate.getFullYear()}`;

        // Bersihkan konten sebelumnya
        datesElement.innerHTML = '';

        // Dapatkan tanggal pertama bulan ini
        const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
        // Dapatkan jumlah hari dalam bulan ini
        const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);

        // Buat baris pertama
        let currentRow = document.createElement('div');
        currentRow.className = 'row g-0';

        // Tambahkan sel kosong untuk hari-hari sebelum tanggal 1
        for (let i = 0; i < firstDay.getDay(); i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'p-2 border col';
            currentRow.appendChild(emptyCell);
        }

        // Tambahkan tanggal-tanggal
        for (let day = 1; day <= lastDay.getDate(); day++) {
            // Jika sudah 7 kolom, buat baris baru
            if ((firstDay.getDay() + day - 1) % 7 === 0 && day !== 1) {
                datesElement.appendChild(currentRow);
                currentRow = document.createElement('div');
                currentRow.className = 'row g-0';
            }

            const dateCell = document.createElement('div');
            dateCell.className = 'p-2 text-center border col';

            // Format tanggal untuk pengecekan event
            const dateString = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

            // Cek apakah ada event
            if (events[dateString]) {
                dateCell.classList.add('has-event');
                dateCell.style.backgroundColor = '#cce5ff';
                dateCell.style.cursor = 'pointer';
                dateCell.onclick = () => showEvents(dateString);
            }

            // Tandai hari ini
            if (day === new Date().getDate() &&
                currentDate.getMonth() === new Date().getMonth() &&
                currentDate.getFullYear() === new Date().getFullYear()) {
                dateCell.classList.add('today');
                dateCell.style.backgroundColor = '#e9ecef';
                dateCell.style.fontWeight = 'bold';
            }

            // Tandai hari Minggu
            if ((firstDay.getDay() + day - 1) % 7 === 0) {
                dateCell.style.color = '#ff4444';
            }

            dateCell.textContent = day;
            currentRow.appendChild(dateCell);
        }

        // Tambahkan sel kosong untuk sisa hari dalam minggu terakhir
        while (currentRow.children.length < 7) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'p-2 border col';
            currentRow.appendChild(emptyCell);
        }

        // Tambahkan baris terakhir
        datesElement.appendChild(currentRow);
    }

    function showEvents(date) {
        const eventDetails = document.getElementById('eventDetails');
        if (events[date]) {
            eventDetails.innerHTML = events[date].map(event => `<p>${event}</p>`).join('');
            const modal = new bootstrap.Modal(document.getElementById('eventModal'));
            modal.show();
        }
    }

    function closeModal() {
        const modalElement = document.getElementById('eventModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }

        // Hapus backdrop secara manual jika masih ada
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }

        // Bersihkan properti body
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }

    // Tambahkan event listener untuk tombol close
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', closeModal);
    });

    // Event listeners untuk navigasi bulan
    document.getElementById('prev').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    document.getElementById('next').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    // Render kalender saat halaman dimuat
    renderCalendar();
});
