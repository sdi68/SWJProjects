/*
 * @package    SW JProjects Payment
 * @subpackage module mod_swjprojects_downloads
 * @version    1.0.0
 * @author     Econsult lab - https://econsultlab.ru
 * @copyright  Copyright (c) 2022 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru/
 */

var SWJPD = {
    _url: '',
    _method: "POST",
    _headers: {
        'Cache-Control': 'no-cache',
        'Content-Type': 'application/json'
    },
    _jversion: '',
    _ajaxlockPane: document.getElementById("skm_LockPane"),
    _ajaxLoader: document.getElementById("ajaxLoading"),
    _row: '',
    _module_id:'',

    getPaymentStatus: function (o,row,module_id) {
        SWJPD._row = row;
        SWJPD._module_id = module_id;
        SWJPD._url = '/index.php?option=com_ajax&plugin=swjpayment&format=json';
        SWJPD._checkJoomlaVersion();
        const _order = document.querySelector('#row-'+row+' td[data-item="order"]');
        let _data = {
            action: 'request_payment_status',
            row: row,
            order: _order.textContent,
            key_id: _order.getAttribute('data-id')
        };
        console.log(_data);
        SWJPD._sendRequest(_data, SWJPD._updateDownloadRow);
    },

    _checkJoomlaVersion: function () {
        SWJPD._url = '/index.php?option=com_ajax&plugin=swjpayment&format=json';
        SWJPD._sendRequest({'action': 'getJoomlaVersion'}, function (data) {
            SWJPD._jversion = data.version_suffix;
            console.log("Joomla version is:", SWJPD._jversion);
        });
    },

    _sendRequest: function (request_data, success_callback) {
        if (typeof success_callback === "function")
            var _callback = success_callback;

        if (SWJPD._ajaxLoader === null) {
            SWJPD._buildAJAXLoader();
        }
        //const _module_id = (new URL(window.location.href)).searchParams.get('id');
        request_data.module_id = SWJPD._module_id;
        //TODO: Почему передача через POST не работает? Передаем через GET
        let queryString = Object.keys(request_data).reduce(function (a, k) {
            a.push(k + "=" + encodeURIComponent(request_data[k]));
            return a;
        }, []).join('&');
        Joomla.request({
            url: SWJPD._url + '&' + queryString,
            method: SWJPD._method,
            headers: SWJPD._headers,
            data: JSON.stringify(request_data),
            onBefore: function (xhr) {
                return SWJPD._onBefore(xhr)
            },
            onSuccess: function (response, xhr) {
                return SWJPD._onSuccess(response, xhr, _callback)
            },
            onError: function (xhr) {
                return SWJPD._onError(xhr)
            },
            onComplete: function (xhr) {
                return SWJPD._onComplete(xhr)
            },
        });
    },
    _onBefore: function (xhr) {
        //console.log('onBefore',xhr);
        SWJPD._ajaxLock();
    },
    _onSuccess: function (response, xhr, success_callback) {
        //Проверяем пришли ли ответы
        if (typeof success_callback === "function")
            var _callback = success_callback;
        console.log('onSuccess before check response', response);
        if (response !== '') {
            response = JSON.parse(response);
            console.log('onSuccess', response);
            if (response != null && typeof response.data === "object") {
                _callback(response.data);
            }
        }
    },
    _onError: function (xhr) {
        console.log('onError', xhr);
    },
    _onComplete: function (xhr) {
        //console.log(' onComplete',xhr);
        SWJPD._ajaxUnLock();
    },
    _updateDownloadRow: function (data) {
        var _data = data;
        console.log('_updateDownloadRow', _data);
        if(typeof _data.order_deleted !== "undefined"){
            // Заказ был удален. Удаляем строку с заказом
            SWJPD._deleteRow();
            return;
        }

        // Получаем обновленную строку заказа по текущему шаблону строки
        SWJPD._url = '/index.php?option=com_ajax&module=swjprojects_downloads&format=json';
        let _request_data = {
            action: 'update_downloads_row',
            order_number: _data.payment_response.order_number,
            row: SWJPD._row
        };

        SWJPD._sendRequest(_request_data, function(data){
            console.log("_updateDownloadRow callback _row, html",SWJPD._row, data.html);
            document.getElementById("row-" + SWJPD._row).outerHTML = data.html;
            SWJPD._showModal(Joomla.Text._("MOD_SWJPROJECTS_DOWNLOADS_UPDATE_ORDER_MODAL_HEADER"),Joomla.Text._("MOD_SWJPROJECTS_DOWNLOADS_UPDATE_ORDER_MODAL_BODY")+"<strong>"+ _data.payment_response.payment_status_title +"</strong>");
        });

    },

    _deleteRow: function(){
        console.log('_deleteRow deleted row', SWJPD._row);
        document.querySelector('#row-'+SWJPD._row).remove();
        SWJPD._showModal(Joomla.Text._("MOD_SWJPROJECTS_DOWNLOADS_UPDATE_ORDER_MODAL_HEADER"),Joomla.Text._("MOD_SWJPROJECTS_DOWNLOADS_DELETE_ORDER_MODAL_BODY"));
    },

    _showModal: function(modal_header,modal_body) {
        // Инициализация модального окна
        let _body = document.getElementsByTagName("body")[0];

        const html = '<div class="modal fade" id="sdiModal" tabIndex="-1" aria-labelledby="sdiModalLabel" aria-hidden="true">'+
            '<div class="modal-dialog modal-dialog-centered">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<h5 class="modal-title" id="adiModalLabel">'+ modal_header +'</h5>'+
            '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="'+ Joomla.Text._('JCLOSE') + '"></button>'+
            '</div>' +
            '<div class="modal-body">'+ modal_body +'</div>' +
            '</div>'+
            '</div>'+
            '</div>';

        const sdiModal = document.createRange().createContextualFragment(html);
        if (sdiModal) {
            let bsModal = bootstrap.Modal.getInstance(sdiModal);

            if (bsModal) {
                bsModal.dispose();
            } // Append the modal before closing body tag
            document.body.appendChild(sdiModal); // Modal was moved so it needs to be re initialised

            const modal = document.getElementById('sdiModal')

            if (window.bootstrap && window.bootstrap.Modal && ! window.bootstrap.Modal.getInstance(modal)) {
                Joomla.initialiseModal(modal, {
                    isJoomla: true
                });
            }
            // Отображение модального окна
            window.bootstrap.Modal.getInstance(modal).show();
        }

    },

    _buildAJAXLoader: function () {
        const _loader = document.createElement('div');
        _loader.id = "ajaxLoading";
        const _lockPane = document.createElement('div');
        _lockPane.id = "skm_LockPane";
        _lockPane.classList.add('LockOff');
        const _img = document.createElement('img');
        _img.setAttribute('src', "/media/mod_swjprojects_downloads/img/loading.gif");
        _loader.appendChild(_img);
        _loader.appendChild(_lockPane);
        let _body = document.getElementsByTagName("body")[0];
        _body.appendChild(_loader);
        SWJPD._ajaxlockPane = document.getElementById("skm_LockPane");
        SWJPD._ajaxLoader = document.getElementById("ajaxLoading");
    },

    _ajaxLock: function () {
        if (SWJPD._ajaxlockPane.classList.contains('LockOff'))
            SWJPD._ajaxlockPane.classList.remove('LockOff');
        if (!SWJPD._ajaxlockPane.classList.contains('LockOn'))
            SWJPD._ajaxlockPane.classList.add('LockOn');
        SWJPD._ajaxLoader.style.display = '';
    },

    _ajaxUnLock: function () {
        if (!SWJPD._ajaxlockPane.classList.contains('LockOff'))
            SWJPD._ajaxlockPane.classList.add('LockOff');
        if (SWJPD._ajaxlockPane.classList.contains('LockOn'))
            SWJPD._ajaxlockPane.classList.remove('LockOn');
        SWJPD._ajaxLoader.style.display = 'none';
    }

};

function getPaymentStatus(o,row,module_id) {
    console.log(o,row,module_id);
    SWJPD.getPaymentStatus(o,row,module_id);
}