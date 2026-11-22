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
        .create(textarea, {

          toolbar: {
            items: [
              'heading',
              '|',
              'bold',
              'italic',
              'underline',
              'link',
              '|',
              'bulletedList',
              'numberedList',
              '|',
              'blockQuote',
              'insertTable',
              'mediaEmbed',
              '|',
              'undo',
              'redo'
            ]
          },

          mediaEmbed: {
            previewsInData: false,

            providers: [

              // YOUTUBE
              {
                name: 'youtube',

                url: [
                  /^https?:\/\/(?:www\.)?youtube\.com\/watch\?v=([\w-]+)/,
                  /^https?:\/\/youtu\.be\/([\w-]+)/,
                  /^https?:\/\/(?:www\.)?youtube\.com\/(?:embed|shorts|live)\/([\w-]+)/
                ],

                html: match => {

                  const videoId = match[1];

                  return `
                                    <div class="media-embed-preview">
                                        <iframe
                                            src="https://www.youtube-nocookie.com/embed/${videoId}"
                                            width="100%"
                                            height="400"
                                            frameborder="0"
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                `;
                }
              },

              // MP4
              {
                name: 'mp4',

                url: /^https?:\/\/[^\s"'<>]+\.mp4(?:[?#][^\s"'<>]*)?$/i,

                html: match => {

                  const videoUrl = match[0]
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');

                  return `
                                    <div class="media-embed-preview">
                                        <video
                                            controls
                                            width="100%"
                                            preload="metadata">
                                            <source
                                                src="${videoUrl}"
                                                type="video/mp4">
                                        </video>
                                    </div>
                                `;
                }
              }

            ]
          }

        })

        .then(editor => {

          form.addEventListener('submit', function(event) {

            const isi = editor.getData();

            textarea.value = isi;

            const teksBersih = isi
              .replace(/<[^>]*>/g, '')
              .replace(/&nbsp;/g, ' ')
              .trim();

            const memilikiMedia = /<oembed\b/i.test(isi);

            if (teksBersih === '' && !memilikiMedia) {

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