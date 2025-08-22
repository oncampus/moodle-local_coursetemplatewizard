// local/ocbsbcoursecreation/amd/src/imagepicker.js

/**
 * Image zoom/picker modal for course list.
 *
 * Initialisiert Klick-Handler auf Bildern und öffnet ein Modal
 * zum Vergrößern bzw. (später) Auswählen.
 *
 * @module     local_ocbsbcoursecreation/imagepicker
 * @copyright  2025 oncampus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    'use strict';

    /**
     * Selektoren, die im Modul verwendet werden.
     * @type {{modal: string, modalImg: string, closeBtn: string, zoomables: string}}
     */
    const SELECTOR = {
        modal: '#imageModal',
        modalImg: '#modalImage',
        closeBtn: '.image-modal-close',
        zoomables: '.zoomable-image img'
    };

    /**
     * Öffnet das Modal mit dem übergebenen Bild.
     *
     * @param {string} src - Bild-URL, die im Modal angezeigt werden soll.
     * @param {string} [alt] - Alternativtext für das Bild.
     * @returns {void}
     */
    function openModal(src, alt) {
        const modal = document.querySelector(SELECTOR.modal);
        const modalImg = document.querySelector(SELECTOR.modalImg);
        if (!modal || !modalImg) {
            return;
        }
        modalImg.src = src;
        modalImg.alt = alt || '';
        modal.style.display = 'block';
        modal.setAttribute('aria-hidden', 'false');

        const closeBtn = modal.querySelector(SELECTOR.closeBtn);
        if (typeof closeBtn?.focus === 'function') {
            closeBtn.focus();
        }

        const onEsc = (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        };
        document.addEventListener('keydown', onEsc, {once: true});
    }

    /**
     * Schließt das Modal.
     *
     * @returns {void}
     */
    function closeModal() {
        const modal = document.querySelector(SELECTOR.modal);
        if (!modal) {
            return;
        }
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }

    /**
     * Initialisiert das Modul:
     * - setzt Cursor auf zoomable Images
     * - öffnet Modal bei Klick
     * - registriert Close-Handler (Button, Overlay)
     *
     * @returns {void}
     */
    function init() {
        document.querySelectorAll(SELECTOR.zoomables).forEach(img => {
            img.style.cursor = 'zoom-in';
            img.addEventListener('click', (e) => {
                e.preventDefault();
                if (img.closest('a')) {
                    e.stopPropagation();
                }
                openModal(img.currentSrc || img.src, img.alt);
            });
        });

        const closeBtn = document.querySelector(SELECTOR.closeBtn);
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                closeModal();
            });
        }

        const modal = document.querySelector(SELECTOR.modal);
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });
        }
    }

    return {init};
});
