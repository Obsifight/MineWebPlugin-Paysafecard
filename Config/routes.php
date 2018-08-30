<?php
Router::connect('/shop/paysafecard/pay', array('controller' => 'pay', 'action' => 'send', 'plugin' => 'paysafecard', '[method]' => 'POST'));
Router::connect('/shop/paysafecard/success', array('controller' => 'pay', 'action' => 'success', 'plugin' => 'paysafecard'));
Router::connect('/shop/paysafecard/failure', array('controller' => 'pay', 'action' => 'failure', 'plugin' => 'paysafecard'));
Router::connect('/shop/paysafecard/ipn', array('controller' => 'pay', 'action' => 'ipn', 'plugin' => 'paysafecard', '[method]' => 'POST'));

Router::connect('/admin/shop/paysafecard/get_histories', array('controller' => 'pay', 'action' => 'get_histories', 'plugin' => 'paysafecard', 'admin' => true));
Router::connect('/admin/shop/paysafecard/config', array('controller' => 'pay', 'action' => 'save_config', 'plugin' => 'paysafecard', 'admin' => true));
