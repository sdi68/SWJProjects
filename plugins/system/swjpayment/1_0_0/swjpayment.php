<?php
/*
 * @package    SW JProjects Payment
 * @subpackage plugin system/swjprojects
 * @version    1.0.0
 * @author     Econsult lab - https://econsultlab.ru
 * @copyright  Copyright (c) 2022 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru/
 */

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\UserFactoryInterface;


defined('_JEXEC') or die;

$lang = Factory::getLanguage();
$lang->load('plg_system_swjpayment', JPATH_ADMINISTRATOR);

if (!class_exists('SWJPaymentPlugin'))
{
	require_once JPATH_PLUGINS . '/system/swjpayment/classes/swjpayment_plugin.php';
}

if (!class_exists('SWJPaymentOrderHelper'))
{
	require_once __DIR__ . '/helpers/order.php';
}

/**
 * The system plugin SWJPayment file
 * @package     pkg_swjprojects_payments
 * @subpackage  system/swjpayment
 * @version     1.0.0
 * @since       1.0.0
 *
 */
class PlgSystemSWJPayment extends SWJPaymentPlugin
{

	/**
	 * Номер заказа
	 * @var string
	 * @since 1.0.0
	 */
	private $_order_number = "";

	/**
	 * Имя компонента SWJProjects
	 * @since 1.0.0
	 */
	private const _SWJPROJECTS = "com_swjprojects";

	/**
	 * Имя представления, отображающего ключ
	 * @since 1.0.0
	 */
	private const _KEY_VIEW = "key";

	/**
	 * Контекст формы отображения ключа
	 * @since 1.0.0
	 */
	private const _KEY_CONTEXT = "com_swjprojects.key";

	/**
	 * Контекст формы отображения списка ключей
	 * @since 1.0.0
	 */
	private const _KEYS_CONTEXT = "com_swjprojects.keys";

	/**
	 * Контекст формы отображения формы настроек проектов
	 * @since 1.0.0
	 */
	private const _PROJECT_CONTEXT = "com_swjprojects.project";

	/**
	 * Конструктор
	 *
	 * @param          $subject
	 * @param   array  $config
	 *
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function __construct(&$subject, array $config = array())
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Добавление дополнительных данных в форму компонента
	 *
	 * @param   string  $context  Контекст формы
	 * @param   mixed   $data     Данные формы
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public final function onContentPrepareData(string $context, $data): bool
	{
		// TODO подумать, как отображать факт оплаты в списке ключей
		switch (true)
		{
			case $this->_checkContext($context, self::_KEY_CONTEXT):
				// Получаем данные по оплате для формы редактирования ключа
				$order = SWJPaymentOrderHelper::getOrderById($data->id);
				if (isset($order->extra))
				{
					$data->processor             = $this->component_params->get($order->extra->processor . '_plugin_name', $order->extra->processor);
					$data->transaction_id        = $order->extra->transaction_id;
					$data->payment_received_date = $order->extra->payment_received_date;
					$data->payment_status        = SWJPaymentStatuses::getEnumNameText($order->extra->payment_status ?? SWJPaymentStatuses::SWJPAYMENT_STATUS_CANCELED);
				}
				break;
			default:
				break;
		}

		return true;
	}


	/**
	 * Adds additional fields to the user editing form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @throws Exception
	 * @since   1.0.0
	 */
	public final function onContentPrepareForm(JForm $form, $data): bool
	{
		// Check we are manipulating a valid form.
		$name      = $form->getName();
		$jInput    = $this->app->input;
		$component = str_replace("'", '', $jInput->getString('component', ''));
		$option    = $jInput->getString('option', '');
		$view      = $jInput->getString('view', '');
		$layout    = $jInput->getString('layout', '');
		if ($this->_checkConfigForm($name, $component))
		{
			// форма настроек com_swjprojects
			// загружаем язык плагина - коннектора
			$this->_loadExtraLanguageFiles();
			// Формируем форму настроек
			$xmlElement = new SimpleXMLElement(JPATH_ROOT . '/plugins/system/swjpayment/forms/settings.xml', null, $data_is_url = true);
			$xmlElement->fieldset->addAttribute('name', 'settings_' . $this->_name);
			$xmlElement->fieldset->addAttribute('label', JText::_(mb_strtoupper('plg_' . $this->_type . '_' . $this->_name) . '_SETTINGS_FIELDSET_LABEL'));

			foreach ($xmlElement->fieldset->children() as $field)
			{
				$field['name'] = $this->_changeFieldName($field['name']);
			}
			$form->setField($xmlElement, '', true, $this->_name . '_settings');
			// Добавляем общие поля в зависимости от шлюза
			$payment_plugins = $this->component_params->get($this->_changeFieldName('payment_plugins'), array());
			$lang            = JFactory::getLanguage();
			foreach ($payment_plugins as $plugin)
			{
				$form_path = JPATH_ROOT . '/plugins/payment/' . $plugin . '/forms/settings.xml';
				if (file_exists($form_path))
				{
					$lang->load('plg_payment_' . $plugin, JPATH_ADMINISTRATOR);
					$xmlTypeSpecific = new SimpleXMLElement($form_path, null, true);
					$xmlTypeSpecific->fieldset->addAttribute('name', 'settings_' . $plugin);
					$xmlTypeSpecific->fieldset->addAttribute('label', JText::_(mb_strtoupper('plg_payment_' . $plugin) . '_SETTINGS_FIELDSET_LABEL'));
					foreach ($xmlTypeSpecific->fieldset->children() as $field)
					{
						$field['name'] = $this->_changeFieldName($field['name'], $plugin);
					}
					$form->setField($xmlTypeSpecific, '', true, $plugin . '_settings');
				}
			}

			return true;
		}
		elseif ($this->_checkKeyForm($option, $view, $layout))
		{
			// Форма редактирования ключа в административной части swjprojects
			if (isset($data->processor))
			{
				// Если была оплата добавляем доп. поля оплаты для просмотра
				$xmlElement = new SimpleXMLElement(JPATH_ROOT . '/plugins/system/swjpayment/forms/key_payment.xml', null, $data_is_url = true);
				$form->setField($xmlElement, '', true, 'plugins');
			}
		}

		return true;
	}

	/**
	 * Определяет, является ли форма - формой редактирования ключа
	 *
	 * @param   string  $option
	 * @param   string  $view
	 * @param   string  $layout
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	private function _checkKeyForm(string $option, string $view, string $layout): bool
	{
		return $option == self::_SWJPROJECTS && $view == self::_KEY_VIEW && $layout == 'edit';
	}


	/**
	 * Отображает блок оплаты при отображении проекта для посетителей сайта
	 *
	 * @param   string  $context  Контекст отображения
	 * @param   object  $item     Проект
	 * @param   string  $html     HTML вывода
	 *
	 * @return bool
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function onShowBuyBlock(string $context, object $item, string &$html): bool
	{
		switch (true)
		{
			case $this->_checkContext($context, self::_PROJECT_CONTEXT):
				$html        .= (HTMLHelper::_('uitab.addTab', 'projectTab', 'payment', Text::_('PLG_SYSTEM_SWJPAYMENT_PAY_TAB')));
				$paymentHTML = '';
				$item_layout = $this->_buildLayoutPath('payment_item');
				// Формируем номер заказа
				$this->_setOrderNumber();
				$vars               = new stdClass();
				$vars->order_number = $this->_getOrderNumber();
				PluginHelper::importPlugin('payment');
				$results = Factory::getApplication()->triggerEvent('onShowPaymentHTML', array(self::_PROJECT_CONTEXT, $item, $item_layout, $vars->order_number, &$paymentHTML));
				SWJPaymentHelper::getCurrentUserData($vars, $this->current_user);
				if (!isset($vars->error))
				{
					if (!empty($paymentHTML))
					{
						$vars->html = $paymentHTML;
					}
					else
					{
						$vars->error = new Exception(Text::_('PLG_SYSTEM_SWJPAYMENT_PAYMENT_NOT_ACTIVATED'));
					}
				}

				$vars->item = $item;
				$html       .= $this->_buildLayout($vars, 'payment_block');
				$html       .= HTMLHelper::_('uitab.endTab');

				return true;
			default:
				return false;
		}
	}

	/**
	 * Формирует доп. поле для списка ключей компонента
	 * Формат данных выходных
	 * array('th' =>'Название поля', 'td' => 'Статус оплаты', 'class' => 'класс отображения поля у ключа')
	 *
	 * @param   string                $context  Контекст вызова
	 * @param   SWJProjectsModelKeys  $item     Ключ
	 * @param   array                 $th       Заголовок поля
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function onGetKeyExtra(string $context, &$item, array &$th): bool
	{
		switch (true)
		{
			case $this->_checkContext($context, self::_KEYS_CONTEXT):
				$item->extra['payment_status'] = SWJPaymentOrderHelper::getOrderPaymentData($item->order);
				$th[]                          = $item->extra['payment_status']['th'];

				return true;
			default:
		}

		return true;
	}


	/**
	 * Обработка обращений по AJAX
	 * @return void
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function onAjaxSwjpayment()
	{
		$this->_loadExtraLanguageFiles();
		$input     = $this->app->getInput();
		$action    = $input->get('action', '');
		$processor = $input->get('processor', '');
		$this->_logging(array('action', $action, 'processor', $processor));
		switch ($action)
		{
			case 'getJoomlaVersion':
				// Получаем версию Joomla
				echo json_encode(array('data' => array(
					'action'         => $action,
					'version_suffix' => SWJPaymentHelper::_getJoomlaVersionSuffix()
				)));
				break;
			case 'request_payment_status':
				// Запрашиваем статус оплаты
				$key_id   = $input->get('key_id', '');
				$order    = SWJPaymentOrderHelper::getOrderById($key_id);
				$response = array();
				PluginHelper::importPlugin('payment');
				$results = Factory::getApplication()->triggerEvent('onGetPaymentInfo', array($order, &$response));
				$this->_logging(array("request_payment_status response", $response));
				$return = array();
				// test BOF
//                $response = array_merge($response, array(
//                    "processor" => 'swjyookassa',
//                    "payment_status" => SWJPaymentStatuses::SWJPAYMENT_STATUS_CONFIRMED,
//                    "payment_status_title" => SWJPaymentStatuses::getEnumNameText(SWJPaymentStatuses::SWJPAYMENT_STATUS_CONFIRMED),
//                    "transaction_id" => '22c5d173-000f-5000-9000-1bdf241d4651',
//                    "payment_received_date" => Factory::getDate('2022-12-09 14:53:50')->toSql(),
//                    "order_number" => '1670671993',
//                    "amount" => '1000',
//                    "extra" => ""
//                ));
				// test EOF
				switch ($response['payment_status'] ?? SWJPaymentStatuses::SWJPAYMENT_STATUS_CANCELED)
				{
					case SWJPaymentStatuses::SWJPAYMENT_STATUS_CONFIRMED:
					case SWJPaymentStatuses::SWJPAYMENT_STATUS_PENDING:
						// Удачная оплата или статус ожидания списания
						SWJPaymentOrderHelper::updateOrder($response);
						SWJPaymentOrderHelper::setOrderState($key_id, true);
						$return['data']['payment_response'] = $response;
						break;
					case SWJPaymentStatuses::SWJPAYMENT_STATUS_REFUND:
					case SWJPaymentStatuses::SWJPAYMENT_STATUS_DENIED:
					case SWJPaymentStatuses::SWJPAYMENT_STATUS_CANCELED:
					default:
						// Ошибка при оплате или неудачная оплата
						$return['data']['payment_response'] = $response;
						if (isset($response['order_number']))
						{
							// Удаляем заказ
							SWJPaymentOrderHelper::deleteOrderByOrderNumber($response['order_number']);
							$return['data']['order_deleted'] = true;
						}
						break;
				}
				$this->_logging(array("request_payment_status return", $return));
				echo json_encode($return);
				break;
			case 'payment-notification':
				// Оповещение о результатах проведенной оплаты от процессинга
				// Пример оповещения:
				// https://ВАШ_САЙТ/index.php?option=com_ajax&plugin=swjpayment&format=json&action=payment-notification&processor=swjyookassa
				$post = $input->post->getArray();
				$this->_logging(array("payment-notification post->getArray()", $post));
				if (count($post) == 0)
				{
					if (ob_get_length() > 0)
						ob_end_clean();
					$source = file_get_contents('php://input');
					$post   = json_decode($source, true);
					$this->_logging(array("payment-notification post for method file_get_contents", $post));
				}
				$response = array();
				$this->_logging(array("Calling onProcessPayment processor, post", $processor, $post));
				PluginHelper::importPlugin('payment');
				$results = Factory::getApplication()->triggerEvent('onProcessPayment', array($processor, $post, &$response));
				$this->_logging(array("after onProcessPayment response", $response));
				switch ($response['payment_status'] ?? SWJPaymentStatuses::SWJPAYMENT_STATUS_CANCELED)
				{
					case SWJPaymentStatuses::SWJPAYMENT_STATUS_CONFIRMED:
					case SWJPaymentStatuses::SWJPAYMENT_STATUS_PENDING:
						// Удачная оплата или статус ожидания списания
						SWJPaymentOrderHelper::updateOrder($response);
						$order = SWJPaymentOrderHelper::getOrderByOrderNumber($response['order_number']);
						SWJPaymentOrderHelper::setOrderState($order->id, true);
						break;
					case SWJPaymentStatuses::SWJPAYMENT_STATUS_REFUND:
					case SWJPaymentStatuses::SWJPAYMENT_STATUS_DENIED:
					case SWJPaymentStatuses::SWJPAYMENT_STATUS_CANCELED:
					default:
						// Ошибка при оплате или неудачная оплата
						if (isset($response['order_number']))
						{
							// Удаляем заказ
							SWJPaymentOrderHelper::deleteOrderByOrderNumber($response['order_number']);
						}
						break;
				}
				break;
			case 'create_order':
				// Создаем новый заказ
				$data['id']         = $input->get('id', 0);
				$data['key']        = $input->get('key', '');
				$data['note']       = $input->get('note', '');
				$data["email"]      = $input->get('email', '');
				$data["order"]      = $input->get('order', '');
				$data["user"]       = $input->get('user', '');
				$data["projects"]   = array($input->get('projects', ''));
				$data["date_start"] = $input->get('date_start', '');
				$data["date_end"]   = $input->get('date_end', '');
				$data["limit"]      = $input->get('limit', '');
				$data["state"]      = $input->get('state', 0);
				$id                 = SWJPaymentOrderHelper::createNewOrder($data);
				$order              = SWJPaymentOrderHelper::getOrderById($id);
				$out                = array();
				if (!is_object($order))
				{
					// Создание заказа прошло с ошибкой
					$order                = new stdClass();
					$order->error         = true;
					$out['data']['error'] = true;
					$this->_logging(array("create_order error by create order", ''));
				}
				else
				{
					$out['data']['order'] = $order;
					$container            = Factory::getContainer();
					$userFactory          = $container->get(UserFactoryInterface::class);
					SWJPaymentHelper::getCurrentUserData($order, $userFactory->loadUserById($order->user));
					$order->project_price = $input->get('project_price', 0);
					$order->project_title = $input->get('project_title', '');
					$this->_logging(array("create_order order", $order));
				}
				$out['data']['html'] = $this->_buildLayout($order, 'order_block');
				echo json_encode($out);
				break;
			case 'user_return':
				// Покупатель вернулся в магазин из процессинга
				// Оплачен заказ или нет - не известно
				$order_number = $input->get('order_number', 0);
				if (SWJPaymentOrderHelper::IsOrderPaid($order_number))
				{
					// Заказ оплачен
					$u = Route::_("index.php?Itemid=" . $this->getPluginParam('menuitem_return_success', ''));
				}
				else
				{
					// Заказ не оплачен
					// Перенаправляем на страницу не удачной оплаты
					$u = Route::_("index.php?Itemid=" . $this->getPluginParam('menuitem_return_fail', ''));
				}
				$this->_logging(array("user_return redirect URL", $u));
				$this->app->redirect($u, 301);
				break;
			case 'delete_order':
				// Не удачная оплата, удаление заказа
				$order_number = $input->get('order_number', 0);
				if ($order_number)
				{
					// Удаляем заказ
					SWJPaymentOrderHelper::deleteOrderByOrderNumber($order_number);
					// Перенаправляем на страницу не удачной оплаты
					$u = Route::_("index.php?Itemid=" . $this->getPluginParam('menuitem_return_fail', ''));
					$this->_logging(array("delete_order redirect URL", $u));
					$this->app->redirect($u, 301);
				}
				break;
			default:
		}
		jexit();
	}

	/**
	 * Формирует уникальный номер заказа
	 * @return void
	 * @since 1.0.0
	 */
	private function _setOrderNumber()
	{
		$this->_order_number = (new Date())->getTimestamp();
	}

	/**
	 * Получает номер заказа
	 * @return string
	 * @since 1.0.0
	 */
	private function _getOrderNumber()
	{
		return $this->_order_number;
	}

	/**
	 * Проверяет соответствие контекста требуемому
	 *
	 * @param $context
	 * @param $needle
	 *
	 * @return bool
	 * @since 1.0.0.
	 */
	private function _checkContext($context, $needle)
	{
		return $context == $needle;
	}
}