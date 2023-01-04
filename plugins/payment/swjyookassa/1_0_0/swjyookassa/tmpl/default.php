<?php
/*
 * @package    SW JProjects Payment
 * @subpackage plugin payment/swjyookassa
 * @version    1.0.0
 * @author     Econsult lab - https://econsultlab.ru
 * @copyright  Copyright (c) 2022 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru/
 */

defined('_JEXEC') or die('Restricted access');

/**
 * @var Object $vars
 **/
?>
<script type="text/javascript">
    // Запуск оплаты плагином
    function run_swjyookassa() {
        console.log('run_swjyookassa started...');
        let _action_url = "<?php echo $vars->action_url; ?>";
        if (typeof _action_url !== "undefined" && _action_url !== "") {
            window.location.href = _action_url;
        }
    }
</script>


