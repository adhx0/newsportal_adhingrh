<?php
$namaBulan = [
  1 => 'Januari',
  2 => 'Februari',
  3 => 'Maret',
  4 => 'April',
  5 => 'Mei',
  6 => 'Juni',
  7 => 'Juli',
  8 => 'Agustus',
  9 => 'September',
  10 => 'Oktober',
  11 => 'November',
  12 => 'Desember'
];

$tanggalHariIni = date('d') . ' ' . $namaBulan[(int)date('n')] . ' ' . date('Y');
?>

<div class="dashboard-calendar-wrapper">

  <button
    type="button"
    class="dashboard-date"
    id="calendarToggle"
    aria-expanded="false"
    aria-controls="dashboardCalendar">

    <i class="bi bi-calendar3"></i>
    <span><?= htmlspecialchars($tanggalHariIni, ENT_QUOTES, 'UTF-8'); ?></span>
    <i class="bi bi-chevron-down calendar-chevron"></i>

  </button>

  <div
    class="dashboard-calendar-popup"
    id="dashboardCalendar"
    hidden>

    <div class="calendar-header">

      <button type="button" class="calendar-nav" id="calendarPrev" aria-label="Bulan sebelumnya">
        <i class="bi bi-chevron-left"></i>
      </button>

      <div class="calendar-month-year" id="calendarMonthYear"></div>

      <button type="button" class="calendar-nav" id="calendarNext" aria-label="Bulan berikutnya">
        <i class="bi bi-chevron-right"></i>
      </button>

    </div>

    <div class="calendar-weekdays">
      <span>Min</span>
      <span>Sen</span>
      <span>Sel</span>
      <span>Rab</span>
      <span>Kam</span>
      <span>Jum</span>
      <span>Sab</span>
    </div>

    <div class="calendar-days" id="calendarDays"></div>

    <div class="calendar-footer">
      <button type="button" id="calendarToday">
        <i class="bi bi-calendar-check"></i>
        Hari Ini
      </button>
    </div>

  </div>

</div>

<script>
  (function() {
    const wrapper = document.querySelector('.dashboard-calendar-wrapper');
    if (!wrapper) return;

    const toggle = document.getElementById('calendarToggle');
    const calendar = document.getElementById('dashboardCalendar');
    const monthYear = document.getElementById('calendarMonthYear');
    const daysContainer = document.getElementById('calendarDays');

    const prevButton = document.getElementById('calendarPrev');
    const nextButton = document.getElementById('calendarNext');
    const todayButton = document.getElementById('calendarToday');

    const today = new Date();

    let currentMonth = today.getMonth();
    let currentYear = today.getFullYear();

    const months = [
      'Januari', 'Februari', 'Maret', 'April',
      'Mei', 'Juni', 'Juli', 'Agustus',
      'September', 'Oktober', 'November', 'Desember'
    ];

    function renderCalendar() {
      monthYear.textContent = months[currentMonth] + ' ' + currentYear;
      daysContainer.innerHTML = '';

      const firstDay = new Date(currentYear, currentMonth, 1).getDay();
      const totalDays = new Date(currentYear, currentMonth + 1, 0).getDate();

      for (let i = 0; i < firstDay; i++) {
        const empty = document.createElement('span');
        empty.className = 'calendar-day empty';
        daysContainer.appendChild(empty);
      }

      for (let day = 1; day <= totalDays; day++) {
        const button = document.createElement('span');

        button.className = 'calendar-day';
        button.textContent = day;

        if (
          day === today.getDate() &&
          currentMonth === today.getMonth() &&
          currentYear === today.getFullYear()
        ) {
          button.classList.add('today');
        }

        daysContainer.appendChild(button);
      }
    }

    function positionCalendar() {
      if (window.innerWidth <= 768 && !calendar.hidden) {
        const rect = toggle.getBoundingClientRect();
        const calendarWidth = Math.min(300, window.innerWidth - 24);

        calendar.style.position = 'fixed';
        calendar.style.width = calendarWidth + 'px';
        calendar.style.left = Math.max(
          12,
          Math.min(
            rect.right - calendarWidth,
            window.innerWidth - calendarWidth - 12
          )
        ) + 'px';

        calendar.style.right = 'auto';
        calendar.style.top = (rect.bottom + 10) + 'px';
      } else {
        calendar.style.position = '';
        calendar.style.width = '';
        calendar.style.left = '';
        calendar.style.right = '';
        calendar.style.top = '';
      }
    }

    function closeCalendar() {
      calendar.hidden = true;
      toggle.setAttribute('aria-expanded', 'false');
      wrapper.classList.remove('calendar-open');

      calendar.style.position = '';
      calendar.style.width = '';
      calendar.style.left = '';
      calendar.style.right = '';
      calendar.style.top = '';
    }

    toggle.addEventListener('click', function() {
      const isOpen = !calendar.hidden;

      calendar.hidden = isOpen;
      toggle.setAttribute('aria-expanded', String(!isOpen));
      wrapper.classList.toggle('calendar-open', !isOpen);

      if (!isOpen) {
        positionCalendar();
      }
    });

    prevButton.addEventListener('click', function() {
      currentMonth--;

      if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
      }

      renderCalendar();
    });

    nextButton.addEventListener('click', function() {
      currentMonth++;

      if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
      }

      renderCalendar();
    });

    todayButton.addEventListener('click', function() {
      currentMonth = today.getMonth();
      currentYear = today.getFullYear();
      renderCalendar();
    });

    window.addEventListener('resize', positionCalendar);
    window.addEventListener('scroll', positionCalendar, true);

    document.addEventListener('click', function(event) {
      if (
        !wrapper.contains(event.target) &&
        !calendar.contains(event.target)
      ) {
        closeCalendar();
      }
    });

    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeCalendar();
      }
    });

    renderCalendar();
  })();
</script>