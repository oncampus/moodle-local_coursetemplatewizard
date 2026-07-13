// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Image zoom/picker modal for course list.
 *
 * Initializes click handlers on images and opens a modal for zooming in or (later) selecting.
 *
 * @module     local_coursetemplatewizard/imagepicker
 * @copyright  2025 oncampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function () {
    'use strict';

    /**
     * Selectors used in the module.
     * @type {{modal: string, modalImg: string, closeBtn: string, zoomables: string}}
     */
    const SELECTOR = {
        modal: '#imageModal',
        modalImg: '#modalImage',
        closeBtn: '.image-modal-close',
        zoomables: '.zoomable-image img'
    };

    /**
     * Opens the modal with the specified image.
     *
     * @param {string} src - Image URL to be displayed in the modal.
     * @param {string} [alt] - Alternative text for the image.
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
     * Closes the modal.
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
     * Initializes the module:
     * - Places the cursor on zoomable images
     * - Opens a modal on click
     * - Registers close handler (button, overlay)
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
