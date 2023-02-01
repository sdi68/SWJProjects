/*
 * @package    SW JProjects Component
 * @subpackage    com_swjprojects
 * @version    1.6.3
 * @author Econsult Lab.
 * @based on   SW JProjects Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2023 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru
 */

document.addEventListener("DOMContentLoaded", function () {
    let popups = document.querySelectorAll('[data-popup]');
    if (popups) {
        if (!document.getElementById('sdiModal')) {
            // Инициализация модального окна

            const html = '<div class="modal fade" id="sdiModal" tabIndex="-1" aria-labelledby="sdiModalLabel" aria-hidden="true">' +
                '<div class="modal-dialog modal-dialog-centered sdi-galery">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<h5 class="modal-title" id="sdiModalLabel"></h5>' +
                '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="' + Joomla.Text._('JCLOSE') + '"></button>' +
                '</div>' +
                '<div class="modal-body"></div>' +
                '</div>' +
                '</div>' +
                '</div>';

            const sdiModal = document.createRange().createContextualFragment(html);

            if (sdiModal) {
                let bsModal = bootstrap.Modal.getInstance(sdiModal);

                if (bsModal) {
                    bsModal.dispose();
                } // Append the modal before closing body tag
                document.body.appendChild(sdiModal); // Modal was moved so it needs to be re initialised
            }
        }

        popups.forEach(function (element) {
            //  Get url
            let url = '';
            if (element.getAttribute('href')) {
                url = element.getAttribute('href');
            } else if (element.getAttribute('data-popup')) {
                url = element.getAttribute('data-popup');
            }

            // Get name
            let name = '';
            if (element.getAttribute('title')) {
                name = element.getAttribute('title');
            } else if (element.getAttribute('data-title')) {
                name = element.getAttribute('data-title');
            } else if (element.getAttribute('data-name')) {
                name = element.getAttribute('data-name');
            }

            // Open popup
            if (url) {
                element.addEventListener('click', function (e) {
                    e.preventDefault();
                    openPopup(url, name);
                });
            }
        });
    }
});

function openPopup(url, name) {
    const modal = document.getElementById('sdiModal');
    document.querySelector('#sdiModal #sdiModalLabel').innerHTML = name;
    document.querySelector('#sdiModal .modal-body').innerHTML = '<img src = "' + url + '" alt = "' + name + '" />';

    if (window.bootstrap && window.bootstrap.Modal && !window.bootstrap.Modal.getInstance(modal)) {
        Joomla.initialiseModal(modal, {
            isJoomla: true
        });
    }
    // Отображение модального окна
    window.bootstrap.Modal.getInstance(modal).show();
}
