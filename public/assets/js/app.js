document.addEventListener('DOMContentLoaded', function () {
    var hasAOS = typeof AOS !== 'undefined';
    if (hasAOS) {
        AOS.init({
            duration: 700,
            easing: 'ease-out-cubic',
            once: true,
            offset: 50
        });
    }

    // Protection lecteur PDF
    document.querySelectorAll('.pdf-viewer-container, .pdf-viewer').forEach(function (el) {
        el.addEventListener('contextmenu', function (e) {
            e.preventDefault();
            return false;
        });
        el.addEventListener('dragstart', function (e) {
            e.preventDefault();
        });
    });

    // Désactiver clic droit sur iframe PDF
    var pdfModal = document.getElementById('pdfModal');
    if (pdfModal) {
        pdfModal.addEventListener('shown.bs.modal', function () {
            var iframe = document.getElementById('pdfViewer');
            if (iframe) {
                iframe.addEventListener('load', function () {
                    try {
                        iframe.contentDocument.addEventListener('contextmenu', function (e) {
                            e.preventDefault();
                        });
                    } catch (e) {
                        // Cross-origin restriction expected for some viewers
                    }
                });
            }
        });
    }

    // Animation des lignes de tableau uniquement si AOS est bien chargé.
    // Sinon, ajouter data-aos rendrait les lignes invisibles (opacity:0 via CSS AOS).
    if (hasAOS) {
        document.querySelectorAll('.table tbody tr').forEach(function (row, i) {
            row.setAttribute('data-aos', 'fade-up');
            row.setAttribute('data-aos-delay', Math.min(i * 30, 300));
        });
        AOS.refreshHard();
    }
});
