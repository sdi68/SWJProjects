<?php
/*
 * @package    SW JProjects Component
 * @subpackage    payment/SWJYOOKassa plugin
 * @version    1.0.0
 * @author Econsult Lab.
 * @copyright  Copyright (c) 2023 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru
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


