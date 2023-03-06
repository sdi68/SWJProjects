/*
 * @package    SW JProjects Payment
 * @subpackage module mod_swjprojects_favorites_projects
 * @version    1.0.0
 * @author     Econsult lab - https://econsultlab.ru
 * @copyright  Copyright (c) 2022 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 *  @link       https://econsultlab.ru/
 */

var MSFP = {
    _url: '/index.php?option=com_ajax&module=swjprojects_favorites_projects&format=json',
    _method: "POST",
    _headers: {
        'Cache-Control': 'no-cache',
        'Content-Type': 'application/json'
    },
    _target: '',
    _jversion: '',
    _ajaxlockPane: document.getElementById("skm_LockPane"),
    _ajaxLoader: document.getElementById("ajaxLoading"),

    UpdateProjectList: function (catid, target) {
        MSFP._checkJoomlaVersion();
        MSFP._target = target;
        let _data = {
            'catid': catid,
            'action': 'update_p_list'
        };
        MSFP._sendRequest(_data, MSFP._updateProjects);
    },

    _checkJoomlaVersion: function () {
        MSFP._sendRequest({'action': 'getJoomlaVersion'}, function (data) {
            MSFP._jversion = data.version_suffix;
            console.log("Joomla version is:", MSFP._jversion);
        });
    },

    _sendRequest: function (request_data, success_callback) {
        if (typeof success_callback === "function")
            var _callback = success_callback;

        if (MSFP._ajaxLoader === null) {
            MSFP._buildAJAXLoader();
        }
        const _module_id = (new URL(window.location.href)).searchParams.get('id');
        request_data.module_id = _module_id;
        //TODO: Почему передача через POST не работает? Передаем через GET
        let queryString = Object.keys(request_data).reduce(function (a, k) {
            a.push(k + "=" + encodeURIComponent(request_data[k]));
            return a;
        }, []).join('&');
        Joomla.request({
            url: MSFP._url + '&' + queryString,
            method: MSFP._method,
            headers: MSFP._headers,
            data: JSON.stringify(request_data),
            onBefore: function (xhr) {
                return MSFP._onBefore(xhr)
            },
            onSuccess: function (response, xhr) {
                return MSFP._onSuccess(response, xhr, _callback)
            },
            onError: function (xhr) {
                return MSFP._onError(xhr)
            },
            onComplete: function (xhr) {
                return MSFP._onComplete(xhr)
            },
        });
    },
    _onBefore: function (xhr) {
        //console.log('onBefore',xhr);
        MSFP._ajaxLock();
    },
    _onSuccess: function (response, xhr, success_callback) {
        //Проверяем пришли ли ответы
        if (typeof success_callback === "function")
            var _callback = success_callback;

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
        MSFP._ajaxUnLock();
    },
    _updateProjects: function (data) {
        var _data = data;
        console.log('_updateProjects', _data);
        let _p = document.getElementById('jform_params_' + MSFP._target);
        console.log('_updateProjects p', _p);
        _p.innerHTML = _data.options;
    },

    _buildAJAXLoader: function () {
        const _loader = document.createElement('div');
        _loader.id = "ajaxLoading";
        const _lockPane = document.createElement('div');
        _lockPane.id = "skm_LockPane";
        _lockPane.classList.add('LockOff');
        const _img = document.createElement('img');
        _img.setAttribute('src', "/media/mod_swjprojects_favorites_projects/img/loading.gif");
        _loader.appendChild(_img);
        _loader.appendChild(_lockPane);
        let _body = document.getElementsByTagName("body")[0];
        _body.appendChild(_loader);
        MSFP._ajaxlockPane = document.getElementById("skm_LockPane");
        MSFP._ajaxLoader = document.getElementById("ajaxLoading");
    },

    _ajaxLock: function () {
        if (MSFP._ajaxlockPane.classList.contains('LockOff'))
            MSFP._ajaxlockPane.classList.remove('LockOff');
        if (!MSFP._ajaxlockPane.classList.contains('LockOn'))
            MSFP._ajaxlockPane.classList.add('LockOn');
        MSFP._ajaxLoader.style.display = '';
    },

    _ajaxUnLock: function () {
        if (!MSFP._ajaxlockPane.classList.contains('LockOff'))
            MSFP._ajaxlockPane.classList.add('LockOff');
        if (MSFP._ajaxlockPane.classList.contains('LockOn'))
            MSFP._ajaxlockPane.classList.remove('LockOn');
        MSFP._ajaxLoader.style.display = 'none';
    }

};

document.addEventListener('DOMContentLoaded', (event) => {
    console.log('Инициализация формы настроек модуля mod_swjprojects_favorites_projects ...');

    const target = 'projects';
    const catid = document.getElementById('jform_params_category');
    update_projects(document.getElementById('jform_params_use_cat_filter').checked, catid.value, target);

    function update_projects(use_cat_filter, catid, target) {
        let _catid = use_cat_filter ? catid : '';
        MSFP.UpdateProjectList(_catid, 'projects');
    }

    document.getElementById('jform_params_use_cat_filter').addEventListener('change', (event) => {

        update_projects(event.currentTarget.checked, catid.value, target);
    });
});

function update_projects_list(o, ctrl) {
    console.log(o.value, ctrl);
    let _data = {
        'catid': o.value,
        '_target': ctrl,
    };
    MSFP.UpdateProjectList(o.value, ctrl);
}