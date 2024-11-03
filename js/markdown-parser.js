jQuery(document).ready(function($) {
    // Tambahkan tombol Convert di toolbar editor
    $('#publishing-action').before('<div id="convert-markdown" class="misc-pub-section"><button id="convert-to-html" class="button">Convert to HTML</button></div>');

    // Ketika tombol di klik
    $('#convert-to-html').on('click', function(e) {
        e.preventDefault();

        // Ambil konten dari editor
        var markdownText = $('#content').val();

        // Kirim konten Markdown ke server untuk dikonversi
        $.post(myPluginData.ajax_url, {
            action: 'parse_markdown',
            security: myPluginData.nonce,
            markdown: markdownText
        })
        .done(function(response) {
            if (response.success) {
                // Update konten editor dengan HTML yang dikonversi
                $('#content').val(response.data.html);
                alert("Markdown berhasil dikonversi ke HTML!");
            } else {
                alert("Gagal mengonversi Markdown: " + response.data.message);
            }
        })
        .fail(function() {
            alert("Gagal mengirim permintaan ke server.");
        });
    });
});
