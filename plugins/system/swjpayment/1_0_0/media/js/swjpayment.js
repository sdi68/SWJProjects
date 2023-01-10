/*
 * @package    SW JProjects Component
 * @subpackage    system/SWJPayment plugin
 * @version    1.0.0
 * @author Econsult Lab.
 * @copyright  Copyright (c) 2023 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru
 */

var SWJPayment = {
    _url: '/index.php?option=com_ajax&plugin=swjpayment&format=json',
    _method: "POST",
    _headers: {
        'Cache-Control': 'no-cache',
        'Content-Type': 'application/json'
    },
    _ajaxlockPane: document.getElementById("skm_LockPane"),
    _ajaxLoader: document.getElementById("ajaxLoading"),
    _debug_mode: false,

    createNewOrder: function (data, is_debug) {
        let _data = data;
        SWJPayment._debug_mode = is_debug;
        _data.action = 'create_order';
        SWJPayment._debug('createNewOrder', '_data', _data);
        SWJPayment._sendRequest(_data, SWJPayment._renderOrderBlock);
    },

    _renderOrderBlock: function (order_data) {
        SWJPayment._debug('_renderOrderBlock', 'order_data', order_data);
        let _container = document.querySelector('.swj-order-details');
        _container.innerHTML = order_data.html;
        if (typeof order_data.error !== "undefined" && order_data.order === "undefined") {
            // Ошибка получения заказа
            return;
        } else {
            // Заказ успешно создан и получен
            _container.innerHTML = order_data.html;
            let _pb = document.querySelector('.swj-get_payment');
            if (_pb.classList.contains('hidden'))
                _pb.classList.remove('hidden');

            var _btn_pay = document.getElementById("swj-pay-btn");
            _btn_pay.addEventListener('click', (event) => {
                let _plugin = document.querySelector('input[name = "payment_gateway"]:checked').value;
                // Вызываем запуск оплаты выбранного плагина
                SWJPayment._debug('_renderOrderBlock', 'Запускаем оплату ', SWJPayment._getFnName(_plugin));
                window[SWJPayment._getFnName(_plugin)]();
            });

        }
    },
    // Формирует имя функции запуска оплаты плагина
    _getFnName: function getFnName(name) {
        return 'run_' + name;
    },

    _sendRequest: function (request_data, success_callback) {
        if (typeof success_callback === "function")
            var _callback = success_callback;

        if (SWJPayment._ajaxLoader === null) {
            SWJPayment._buildAJAXLoader();
        }

        //TODO: Почему передача через POST не работает? Передаем через GET
        let queryString = Object.keys(request_data).reduce(function (a, k) {
            a.push(k + "=" + encodeURIComponent(request_data[k]));
            return a;
        }, []).join('&');

        Joomla.request({
            url: SWJPayment._url + '&' + queryString,
            method: SWJPayment._method,
            headers: SWJPayment._headers,
            data: JSON.stringify(request_data),
            onBefore: function (xhr) {
                return SWJPayment._onBefore(xhr)
            },
            onSuccess: function (response, xhr) {
                return SWJPayment._onSuccess(response, xhr, _callback)
            },
            onError: function (xhr) {
                return SWJPayment._onError(xhr)
            },
            onComplete: function (xhr) {
                return SWJPayment._onComplete(xhr)
            },
        });
    },
    _onBefore: function (xhr) {
        SWJPayment._ajaxLock();
    },
    _onSuccess: function (response, xhr, success_callback) {
        //Проверяем пришли ли ответы
        if (typeof success_callback === "function")
            var _callback = success_callback;
        SWJPayment._debug('_onSuccess', 'доп парсинга response', response);
        if (response != '') {
            response = JSON.parse(response);
            SWJPayment._debug('_onSuccess', 'response', response);
            if (response != null && typeof response.data === "object") {
                _callback(response.data);
            }
        }
    },
    _onError: function (xhr) {
        SWJPayment._debug('_onError', 'xhr', xhr);
    },
    _onComplete: function (xhr) {
        SWJPayment._ajaxUnLock();
    },

    _buildAJAXLoader: function () {
        const _loader = document.createElement('div');
        _loader.id = "ajaxLoading";
        const _lockPane = document.createElement('div');
        _lockPane.id = "skm_LockPane";
        _lockPane.classList.add('LockOff');
        const _img = document.createElement('img');
        _img.setAttribute('src', "/media/plg_swjpayment/img/loading.gif");
        _loader.appendChild(_img);
        _loader.appendChild(_lockPane);
        let _body = document.getElementsByTagName("body")[0];
        _body.appendChild(_loader);
        SWJPayment._ajaxlockPane = document.getElementById("skm_LockPane");
        SWJPayment._ajaxLoader = document.getElementById("ajaxLoading");
    },

    _ajaxLock: function () {
        if (SWJPayment._ajaxlockPane.classList.contains('LockOff'))
            SWJPayment._ajaxlockPane.classList.remove('LockOff');
        if (!SWJPayment._ajaxlockPane.classList.contains('LockOn'))
            SWJPayment._ajaxlockPane.classList.add('LockOn');
        SWJPayment._ajaxLoader.style.display = '';
    },

    _ajaxUnLock: function () {
        if (!SWJPayment._ajaxlockPane.classList.contains('LockOff'))
            SWJPayment._ajaxlockPane.classList.add('LockOff');
        if (SWJPayment._ajaxlockPane.classList.contains('LockOn'))
            SWJPayment._ajaxlockPane.classList.remove('LockOn');
        SWJPayment._ajaxLoader.style.display = 'none';
    },
    _debug: function (method, name, value) {
        if (SWJPayment._debug_mode) {
            console.log(method + '.' + name, value);
        }
    }
};
