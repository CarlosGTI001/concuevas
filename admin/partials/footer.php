<?php
declare(strict_types=1);
?>
    </main>
  </div>

  <!-- Core -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/headroom.js@0.11.0/dist/headroom.min.js"></script>

  <!-- Summernote JS -->
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

  <!-- Dropzone & Sortable -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

  <!-- Vendor JS -->
  <script src="https://cdn.jsdelivr.net/npm/on-screen@1.3.4/dist/on-screen.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/nouislider@14.6.3/distribute/nouislider.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jarallax@1.12.7/dist/jarallax.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery.counterup@2.1.0/jquery.counterup.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.countdown/2.2.0/jquery.countdown.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/smooth-scroll@16.1.3/dist/smooth-scroll.polyfills.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.23.0/prism.min.js"></script>

  <!-- Neumorphism JS -->
  <script src="<?= e(app_url('assets/js/neumorphism.js')) ?>?v=<?= APP_VERSION ?>"></script>

  <script>
    $(document).ready(function() {
      const uploadUrl = '<?= e(app_url('admin/upload_handler')) ?>';
      const csrfToken = '<?= generate_csrf_token() ?>';

      function sendFile(file, editor, welEditable, folder, itemName) {
          data = new FormData();
          data.append("image", file);
          data.append("folder", folder);
          data.append("name", itemName);
          data.append("csrf_token", csrfToken);
          
          $.ajax({
              data: data,
              type: "POST",
              url: uploadUrl,
              cache: false,
              contentType: false,
              processData: false,
              success: function(response) {
                  $(editor).summernote('insertImage', response.url);
              },
              error: function(err) {
                  console.error('Upload error:', err);
                  alert('Error al subir la imagen al servidor.');
              }
          });
      }

      $('.js-summernote').each(function() {
          const $editor = $(this);
          const folder = $editor.data('folder') || 'editor';
          const itemName = $editor.data('item-name') || 'image';

          $editor.summernote({
            height: 350,
            tabsize: 2,
            lang: 'es-ES',
            toolbar: [
              ['style', ['style']],
              ['font', ['bold', 'underline', 'clear']],
              ['color', ['color']],
              ['para', ['ul', 'ol', 'paragraph']],
              ['table', ['table']],
              ['insert', ['link', 'picture', 'video']],
              ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onImageUpload: function(files) {
                    sendFile(files[0], $editor, null, folder, itemName);
                }
            }
          });
      });
    });
  </script>
</body>
</html>
