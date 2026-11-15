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

  <button type="button"
    class="dashboard-date"
    id="calendarToggle"
    aria-expanded="false"
    aria-controls="dashboardCalendar">

    <i class="bi bi-calendar3"></i>
    <span><?= htmlspecialchars($tanggalHariIni, ENT_QUOTES, 'UTF-8'); ?></span>
    <i class="bi bi-chevron-down calendar-chevron"></i>

  </button>

  <div class="dashboard-calendar-popup"
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

<style>
  .dashboard-calendar-wrapper {
    position: relative;
    display: inline-block;
  }

  .dashboard-date {
    display: flex;
    align-items: center;
    gap: 9px;
    cursor: pointer;
    font: inherit;
  }

  .calendar-chevron {
    font-size: 11px;
    transition: transform 0.2s ease;
  }

  .dashboard-calendar-wrapper.calendar-open .calendar-chevron {
    transform: rotate(180deg);
  }

  .dashboard-calendar-popup {
    position: absolute;
    top: calc(100% + 12px);
    right: 0;
    z-index: 1100;
    width: 300px;
    padding: 16px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    color: #1f2937;
  }

  .dashboard-calendar-popup[hidden] {
    display: none;
  }

  .calendar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
  }

  .calendar-month-year {
    font-size: 15px;
    font-weight: 700;
  }

  .calendar-nav {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 8px;
    background: #f3f4f6;
    color: #374151;
    cursor: pointer;
  }

  .calendar-nav:hover {
    background: #e5e7eb;
  }

  .calendar-weekdays,
  .calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
    text-align: center;
  }

  .calendar-weekdays {
    margin-bottom: 8px;
  }

  .calendar-weekdays span {
    padding: 5px 0;
    color: #6b7280;
    font-size: 11px;
    font-weight: 600;
  }

  .calendar-day {
    display: flex;
    align-items: center;
    justify-content: center;
    aspect-ratio: 1;
    border: none;
    border-radius: 8px;
    background: transparent;
    color: #374151;
    font-size: 13px;
    cursor: default;
  }

  .calendar-day.today {
    background: #2563eb;
    color: #fff;
    font-weight: 700;
  }

  .calendar-day:not(.empty):not(.today):hover {
    background: #eff6ff;
    color: #2563eb;
  }

  .calendar-day.empty {
    visibility: hidden;
  }

  .calendar-footer {
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px solid #e5e7eb;
    text-align: center;
  }

  .calendar-footer button {
    border: none;
    background: transparent;
    color: #2563eb;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
  }

  @media (max-width: 480px) {
    .dashboard-calendar-popup {
      right: -5px;
      width: 280px;
    }
  }
</style>

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

      // Ruang kosong sebelum tanggal 1
      for (let i = 0; i < firstDay; i++) {
        const empty = document.createElement('span');
        empty.className = 'calendar-day empty';
        daysContainer.appendChild(empty);
      }

      // Tampilkan tanggal dalam bulan
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

    function closeCalendar() {
      calendar.hidden = true;
      toggle.setAttribute('aria-expanded', 'false');
      wrapper.classList.remove('calendar-open');
    }

    toggle.addEventListener('click', function() {
      const isOpen = !calendar.hidden;

      calendar.hidden = isOpen;
      toggle.setAttribute('aria-expanded', String(!isOpen));
      wrapper.classList.toggle('calendar-open', !isOpen);
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

    document.addEventListener('click', function(event) {
      if (!wrapper.contains(event.target)) {
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