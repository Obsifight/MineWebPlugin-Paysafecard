<?php
App::uses('CakeEventListener', 'Event');

class PaysafecardModuleEventListener implements CakeEventListener {

  private $controller;

  public function __construct($request, $response, $controller) {
    $this->controller = $controller;
  }

  public function implementedEvents() {
    return array(
      'onLoadPage' => 'setupVarsForModule',
    );
  }

  public function setupVarsForModule($event) {
    if($this->controller->params['controller'] == "shop" && $this->controller->params['action'] == "index") {

      $this->controller->loadModel('Paysafecard.Config'); // find config
      $getConfig = $this->controller->Config->find('first');
      if (is_array($getConfig) && isset($getConfig['Config']) && !empty($getConfig['Config']['api_key'])) // if psc is enabled and configured
        $currency = $getConfig['Config']['currency']; // setup configured currency
      else
        $currency = false;

      ModuleComponent::$vars['paysafecard_currency'] = $currency;

    }
    if($this->controller->params['controller'] == "payment" && $this->controller->params['action'] == "admin_index") {

      $this->controller->loadModel('Paysafecard.Config'); // find config
      $getConfig = $this->controller->Config->find('first');
      if (!is_array($getConfig) || !isset($getConfig['Config']))
        $getConfig = array('Config' => array('api_key' => null, 'api_env' => null, 'currency' => null, 'default_credits_gived_for_1_as_amount' => null));

      ModuleComponent::$vars['paysafecardConfig'] = $getConfig;

    }
  }

}
