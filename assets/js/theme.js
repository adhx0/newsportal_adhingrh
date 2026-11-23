
/* =========================================================
   NEWS PORTAL - THEME SWITCH
   ========================================================= */

(function () {
  'use strict';

  const STORAGE_KEY = 'newsportal-theme';
  const root = document.documentElement;

  function getTheme() {
    try {
      return localStorage.getItem(STORAGE_KEY) === 'dark'
        ? 'dark'
        : 'light';
    } catch (error) {
      return root.getAttribute('data-theme') === 'dark'
        ? 'dark'
        : 'light';
    }
  }

  function createSwitch() {
    const button = document.createElement('button');

    button.type = 'button';
    button.className = 'theme-switch';
    button.setAttribute('data-theme-toggle', '');
    button.setAttribute('role', 'switch');

    button.innerHTML = `
      <span class="theme-switch-track">
        <span class="theme-switch-thumb">
          <i class="bi bi-sun-fill" data-theme-icon></i>
        </span>
      </span>
      <span class="theme-switch-label" data-theme-label>
        Light Mode
      </span>
    `;

    // Navbar halaman publik
    const publicNav = document.querySelector(
      '.main-navbar .navbar-nav'
    );

    if (publicNav) {
      const item = document.createElement('li');
      item.className = 'nav-item theme-switch-item';
      item.appendChild(button.cloneNode(true));
      publicNav.appendChild(item);
    }

    // Sidebar admin
    const sidebarFooter = document.querySelector(
      '.admin-sidebar .sidebar-footer'
    );

    if (sidebarFooter) {
      const adminSwitch = button.cloneNode(true);
      adminSwitch.classList.add('admin-theme-switch');

      const logout = sidebarFooter.querySelector('.sidebar-logout');

      if (logout) {
        sidebarFooter.insertBefore(adminSwitch, logout);
      } else {
        sidebarFooter.appendChild(adminSwitch);
      }
    }

    // Halaman login
    const loginPage = document.querySelector('.login-page');

    if (loginPage) {
      const loginSwitch = button.cloneNode(true);
      loginSwitch.classList.add('login-theme-switch');
      loginPage.insertBefore(loginSwitch, loginPage.firstChild);
    }
  }

  function applyTheme(theme) {
    const selected = theme === 'dark' ? 'dark' : 'light';
    const isDark = selected === 'dark';

    root.setAttribute('data-theme', selected);

    try {
      localStorage.setItem(STORAGE_KEY, selected);
    } catch (error) {
      // Tetap gunakan tema pada halaman saat ini.
    }

    document.querySelectorAll('[data-theme-toggle]').forEach(button => {
      const icon = button.querySelector('[data-theme-icon]');
      const label = button.querySelector('[data-theme-label]');

      button.setAttribute('aria-checked', isDark ? 'true' : 'false');
      button.setAttribute(
        'aria-label',
        isDark ? 'Dark Mode aktif' : 'Light Mode aktif'
      );

      button.title = isDark
        ? 'Ganti ke Light Mode'
        : 'Ganti ke Dark Mode';

      if (icon) {
        icon.classList.toggle('bi-sun-fill', !isDark);
        icon.classList.toggle('bi-moon-stars-fill', isDark);
      }

      if (label) {
        label.textContent = isDark ? 'Dark Mode' : 'Light Mode';
      }
    });
  }

  // Terapkan tema sebelum halaman selesai dimuat.
  applyTheme(getTheme());

  document.addEventListener('DOMContentLoaded', function () {

    createSwitch();

    applyTheme(getTheme());

    document.querySelectorAll('[data-theme-toggle]').forEach(button => {
      button.addEventListener('click', function () {
        const currentTheme = root.getAttribute('data-theme');

        applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
      });
    });

  });

})();
