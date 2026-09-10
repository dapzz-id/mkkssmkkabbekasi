<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar with Events</title>
    <link rel="stylesheet" href="{{asset('dist/css/main.css')}}">
</head>
<body class="bg-gray-100 p-8">
    <div class="bg-gray-100 p-8">
        <div id="calendar" class="max-w-xl mx-auto bg-white rounded-lg shadow-lg p-4">
            <div class="flex justify-between items-center mb-4">
                <button id="prev" class="text-lg font-semibold text-blue-500">&lt;</button>
                <h2 id="monthYear" class="text-xl font-bold"></h2>
                <button id="next" class="text-lg font-semibold text-blue-500">&gt;</button>
            </div>
            <div class="grid grid-cols-7 gap-2 text-center font-semibold text-gray-700">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>
            <div id="dates" class="grid grid-cols-7 gap-2 mt-2">
                <!-- Tanggal akan di-generate oleh JavaScript -->
            </div>
        </div>
    
        <!-- Modal untuk acara -->
        <div id="eventModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden">
            <div class="bg-white rounded-lg shadow-lg p-6 w-80">
                <h3 class="text-lg font-semibold mb-2">Acara</h3>
                <div id="eventDetails" class="mb-4">
                    <!-- Detail acara akan diisi oleh JavaScript -->
                </div>
                <button onclick="closeModal()" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Tutup</button>
            </div>
        </div>
    </div>
    

    <script>
        const calendarElement = document.getElementById('calendar');
        const datesElement = document.getElementById('dates');
        const monthYearElement = document.getElementById('monthYear');
        const eventModal = document.getElementById('eventModal');
        const eventDetails = document.getElementById('eventDetails');

        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();

        // Data acara sebagai contoh
        const events = {
            '2024-11-25': ['Acara A', 'Acara B'],
            '2024-11-27': ['Acara C']
        };

        function renderCalendar(month, year) {
            datesElement.innerHTML = '';
            monthYearElement.innerText = new Date(year, month).toLocaleString('default', { month: 'long', year: 'numeric' });

            const firstDay = new Date(year, month).getDay();
            const lastDate = new Date(year, month + 1, 0).getDate();

            for (let i = 0; i < firstDay; i++) {
                datesElement.innerHTML += `<div class="py-2"></div>`;
            }

            for (let date = 1; date <= lastDate; date++) {
                const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                const event = events[dateString];
                const colorClass = event ? (event.color === 'orange' ? 'bg-orange-200' : 'bg-blue-200') : '';

                datesElement.innerHTML += `
                    <div onclick="showEvents('${dateString}')" 
                        class="py-4 px-4 text-center rounded ${colorClass} ${event ? 'cursor-pointer' : ''}">
                        ${date}
                    </div>
                `;
            }
        }

        function showEvents(date) {
            if (events[date]) {
                eventDetails.innerHTML = events[date].map(event => `<p>${event}</p>`).join('');
                eventModal.classList.toggle('hidden');
            }
        }

        function closeModal() {
            eventModal.classList.toggle('hidden');
        }

        document.getElementById('prev').addEventListener('click', () => {
            currentMonth = (currentMonth === 0) ? 11 : currentMonth - 1;
            currentYear = (currentMonth === 11) ? currentYear - 1 : currentYear;
            renderCalendar(currentMonth, currentYear);
        });

        document.getElementById('next').addEventListener('click', () => {
            currentMonth = (currentMonth === 11) ? 0 : currentMonth + 1;
            currentYear = (currentMonth === 0) ? currentYear + 1 : currentYear;
            renderCalendar(currentMonth, currentYear);
        });

        renderCalendar(currentMonth, currentYear);
    </script>
</body>
</html>