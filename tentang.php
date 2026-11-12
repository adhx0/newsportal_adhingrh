<?php
include "config/koneksi.php";
include "includes/header.php";
include "includes/navbar.php";
?>

<main class="about-page">

  <!-- HEADER -->
  <section class="about-hero">

    <span class="about-eyebrow">Tentang Kami</span>

    <h1>Mengenal NewsPortal</h1>

    <p>
      Menyajikan informasi terkini, terpercaya, dan informatif
      untuk membantu masyarakat memahami berbagai peristiwa.
    </p>

  </section>

  <!-- PROFIL -->
  <section class="about-intro">

    <div class="about-section-heading">
      <span class="about-eyebrow">Siapa Kami</span>
      <h2>Media Informasi Digital</h2>
    </div>

    <p class="about-lead">
      NewsPortal adalah media informasi digital yang menyajikan
      berita terkini, terpercaya, dan informatif dari berbagai
      kategori seperti Teknologi, Pendidikan, Politik, Ekonomi,
      Olahraga, dan Hiburan.
    </p>

    <p>
      Kami berkomitmen untuk menghadirkan informasi yang akurat,
      berimbang, dan bermanfaat bagi masyarakat dengan tetap
      menjunjung tinggi kode etik jurnalistik dan prinsip-prinsip
      pemberitaan yang profesional.
    </p>

  </section>

  <!-- INFORMASI PERUSAHAAN DAN MAPS -->
  <section class="about-grid">

    <div class="about-card">

      <div class="about-card-heading">
        <div class="about-icon">
          <i class="bi bi-building"></i>
        </div>

        <div>
          <h2>Informasi Perusahaan</h2>
          <p>Identitas dan kontak resmi NewsPortal</p>
        </div>
      </div>

      <div class="company-info">

        <div class="company-info-item">
          <span>Nama Media</span>
          <strong>NewsPortal</strong>
        </div>

        <div class="company-info-item">
          <span>Penerbit</span>
          <strong>PT NewsPortal XYZ</strong>
        </div>

        <div class="company-info-item">
          <span>Alamat</span>
          <strong>Belum tersedia</strong>
        </div>

        <div class="company-info-item">
          <span>WhatsApp</span>
          <strong>+62 812-xxx-xxx</strong>
        </div>

        <div class="company-info-item">
          <span>Telepon</span>
          <strong>(024) xxxxxxxx</strong>
        </div>

        <div class="company-info-item">
          <span>Email</span>
          <strong>redaksi@newsportal.com</strong>
        </div>

      </div>

    </div>

    <!-- MAPS -->
    <div class="about-card">

      <div class="about-card-heading">
        <div class="about-icon">
          <i class="bi bi-geo-alt"></i>
        </div>

        <div>
          <h2>Lokasi Kantor</h2>
          <p>Lokasi kantor NewsPortal</p>
        </div>
      </div>

      <div class="about-map">
        <iframe
          src="https://www.google.com/maps?q=Semarang&output=embed"
          width="100%"
          height="100%"
          style="border:0;"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>

    </div>

  </section>

  <!-- MEDIA SOSIAL DAN REDAKSI -->
  <section class="about-grid">

    <!-- MEDIA SOSIAL -->
    <div class="about-card">

      <div class="about-card-heading">
        <div class="about-icon">
          <i class="bi bi-share"></i>
        </div>

        <div>
          <h2>Media Sosial</h2>
          <p>Ikuti informasi terbaru dari kami</p>
        </div>
      </div>

      <div class="about-list">

        <div class="about-list-item">
          <i class="bi bi-instagram"></i>
          <div>
            <span>Instagram</span>
            <strong>@newsportal.id</strong>
          </div>
        </div>

        <div class="about-list-item">
          <i class="bi bi-tiktok"></i>
          <div>
            <span>TikTok</span>
            <strong>@newsportal.id</strong>
          </div>
        </div>

        <div class="about-list-item">
          <i class="bi bi-threads"></i>
          <div>
            <span>Threads</span>
            <strong>@newsportal.id</strong>
          </div>
        </div>

        <div class="about-list-item">
          <i class="bi bi-youtube"></i>
          <div>
            <span>YouTube</span>
            <strong>NewsPortal TV</strong>
          </div>
        </div>

        <div class="about-list-item">
          <i class="bi bi-facebook"></i>
          <div>
            <span>Facebook</span>
            <strong>NewsPortal Indonesia</strong>
          </div>
        </div>

      </div>

    </div>

    <!-- STRUKTUR REDAKSI -->
    <div class="about-card">

      <div class="about-card-heading">
        <div class="about-icon">
          <i class="bi bi-people"></i>
        </div>

        <div>
          <h2>Struktur Redaksi</h2>
          <p>Tim di balik pemberitaan NewsPortal</p>
        </div>
      </div>

      <div class="about-list">

        <div class="editorial-item">
          <span class="editorial-number">01</span>
          <div>
            <span>Pemimpin Umum</span>
            <strong>Ahmad Nugroho</strong>
          </div>
        </div>

        <div class="editorial-item">
          <span class="editorial-number">02</span>
          <div>
            <span>Pemimpin Redaksi</span>
            <strong>Budi Santoso</strong>
          </div>
        </div>

        <div class="editorial-item">
          <span class="editorial-number">03</span>
          <div>
            <span>Redaktur Pelaksana</span>
            <strong>Siti Rahmawati</strong>
          </div>
        </div>

        <div class="editorial-item">
          <span class="editorial-number">04</span>
          <div>
            <span>Editor</span>
            <strong>Tim Editorial NewsPortal</strong>
          </div>
        </div>

        <div class="editorial-item">
          <span class="editorial-number">05</span>
          <div>
            <span>Reporter</span>
            <strong>Tim Jurnalis NewsPortal</strong>
          </div>
        </div>

      </div>

    </div>

  </section>

  <!-- KEBIJAKAN PEMBERITAAN -->
  <section class="about-policy">

    <div class="about-policy-icon">
      <i class="bi bi-shield-check"></i>
    </div>

    <div>
      <span class="about-eyebrow">Komitmen Kami</span>

      <h2>Kebijakan Pemberitaan</h2>

      <p>
        Seluruh berita yang diterbitkan NewsPortal telah melalui
        proses verifikasi dan penyuntingan oleh tim redaksi.
        NewsPortal berkomitmen menyajikan informasi yang akurat,
        berimbang, independen, dan bertanggung jawab sesuai dengan
        kode etik jurnalistik yang berlaku.
      </p>

      <p>
        Apabila terdapat kekeliruan dalam pemberitaan, pembaca
        dapat menghubungi redaksi melalui email atau WhatsApp
        resmi yang telah disediakan.
      </p>
    </div>

  </section>

</main>

<?php include "includes/footer.php"; ?>