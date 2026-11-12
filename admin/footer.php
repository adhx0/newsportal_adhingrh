</div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>


<script>
  document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('#editor');
    const form = document.querySelector('#formArtikel');

    if (textarea && form && typeof ClassicEditor !== 'undefined') {
      ClassicEditor
        .create(textarea)
        .then(editor => {

          form.addEventListener('submit', function(event) {
            // Ambil isi dari CKEditor
            const isi = editor.getData();

            // Masukkan isi editor ke textarea
            textarea.value = isi;

            // Validasi konten artikel
            const teksBersih = isi
              .replace(/<[^>]*>/g, '')
              .replace(/&nbsp;/g, ' ')
              .trim();

            if (teksBersih === '') {
              event.preventDefault();

              alert('Isi berita tidak boleh kosong.');

              editor.editing.view.focus();
              return;
            }
          });

          console.log('CKEditor berhasil diinisialisasi');
        })
        .catch(error => {
          console.error('CKEditor Error:', error);
        });
    }
  });
</script>


</body>

</html>