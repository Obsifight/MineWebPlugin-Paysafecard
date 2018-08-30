<?php
class PayController extends PaysafecardAppController {

  private $defaultConfig = array(
    'api_key' => '',
    'api_env' => 'PRODUCTION',
    'success_url' => '',
    'failure_url' => '',
    'notification_url' => '',
    'default_credits_gived_for_1_as_amount' => '80',
    'currency' => 'EUR',
  );
  private $defaultOffers = array(
    array(
      'amount' => '10',
      'credits' => '800'
    )
  );

  private function findOffer($amount) {
    foreach ($this->offers as $key => $offer) {
      if (floatval($offer['amount']) === floatval($amount)) {
        return array('credits' => floatval($offer['credits']));
      }
    }
    return array(
      'credits' => floatval($this->config['default_credits_gived_for_1_as_amount']*$amount)
    );
  }

  public function beforeFilter() {
    parent::beforeFilter();
    $this->Security->unlockedActions = array('ipn', 'success', 'failure');

    // Setup default url
    $this->defaultConfig['success_url'] = Router::url('/shop/paysafecard/success', true);
    $this->defaultConfig['failure_url'] = Router::url('/shop/paysafecard/failure', true);
    $this->defaultConfig['notification_url'] = Router::url('/shop/paysafecard/ipn', true);

    // Find config
    $this->loadModel('Paysafecard.Config');
    $getConfig = $this->Config->find('first');
    if (is_array($getConfig) && isset($getConfig['Config']))
      $config = $getConfig['Config'];
    else
      $config = array();
    // Merge with default
    $this->config = array_merge($this->defaultConfig, $config);

    // setup offers
    $this->loadModel('Paysafecard.Offer');
    $getOffers = $this->Offer->find('all');
    if (is_array($getOffers) && isset($getOffers['Offer']))
      $this->offers = $getOffers['Offer'];
    else
      $this->offers = $this->defaultOffers;

    // setup url
    $this->endpoint = 'https://api'. ($this->config['api_env'] == "TEST" ? 'test' : '') .'.paysafecard.com/v1';
  }

  public function send() {
    $this->autoRender = false;

    // Handle errors/user

    if (!$this->isConnected)
      throw new NotFoundException('Not logged');

    if (!$this->request->is('post'))
      throw new NotFoundException('Not post');

    if (empty($this->request->data) || !isset($this->request->data['amount']) || !isset($this->request->data['customer_id']))
      throw new NotFoundException('Missing params');

    $customer = array( // setup custom data to retrieve after with notification
      "id" => $this->request->data['customer_id'],
      "ip" => $this->Util->getIP(),
    );

    $jsonarray = array( // setup data for api
      "currency"         => $this->config['currency'],
      "amount"           => str_replace(',', '.', $this->request->data['amount']),
      "customer"         => $customer,
      "redirect"         => array(
        "success_url" => $this->config['success_url'],
        "failure_url" => $this->config['failure_url'],
      ),
      "type"             => "PAYSAFECARD",
      "notification_url" => $this->config['notification_url'],
      "shop_id"          => 'shop1',
    );

    $curl = $this->doRequest($this->endpoint . '/payments/', $jsonarray, 'POST'); // do request and save into $curl

    if ($this->config['api_env'] === 'TEST') { // log for tests
      $this->log('INIT PAYSAFECARD TEST PAYEMENT - ID: '.$curl['response']['id']);
      $this->HistoryC = $this->Components->load('History');
      $this->HistoryC->set('INIT_PAYSAFECARD_TEST_PAYEMENT', 'shop', $curl['response']['id'], $customer['id']);
    } else {
      $this->HistoryC = $this->Components->load('History');
      $this->HistoryC->set('INIT_PAYSAFECARD_PAYEMENT', 'shop', $curl['response']['id'], $customer['id']);
    }

    // handle error & redirect
    if (($curl["error_nr"] == 0) && ($curl["http_status"] < 300)) // check if an error as encountered
      return $this->redirect($curl['response']['redirect']['auth_url']); // redirect user to auth url
    else
      throw new InternalErrorException($curl["error_text"] . ':' . $curl["http_status"]); // error
  }

  public function success() {
    $this->set('title_for_layout', $this->Lang->get('PAYSAFECARD__SUCCESS_TITLE'));

    // Retrieve payement (last init by user)
    $HistoryModel = ClassRegistry::init('History');
    $findLastPaymentInit = $HistoryModel->find('first', array('conditions' => array(
      'user_id' => $this->User->getKey('id'),
      'action' => 'INIT_PAYSAFECARD_'.($this->config['api_env'] === 'TEST' ? 'TEST_' : '').'PAYEMENT'
    ), 'order' => 'id DESC'));
    if (empty($findLastPaymentInit) || !isset($findLastPaymentInit['History']))
      return $this->set('error', 'Payment not found.');

    // retrieve payment
    $curl = $this->doRequest($this->endpoint . '/payments/'.$findLastPaymentInit['History']['other'], array(), 'GET');
    if (($curl["error_nr"] != 0) || ($curl["http_status"] === 500)) // check if an error as encountered
      throw new InternalErrorException($curl["error_text"] . ':' . $curl["http_status"]); // error
    if ($curl["http_status"] === 400)
      return $this->set('error', 'Payment status invalid.');

    if ($curl['response']['status'] === "AUTHORIZED") {
      // try to find payment in history
      $this->loadModel('Paysafecard.PaymentHistory');
      $findHistory = $this->PaymentHistory->find('count', array('conditions' => array('payment_id' => $curl['response']['id'])));
      if ($findHistory === 0) // not credited yet
        return $this->set($this->capture($curl['response']['id'])); // capture payment
    }

    $this->set(array('status' => true));
  }

  public function failure() {
    $this->set('title_for_layout', $this->Lang->get('PAYSAFECARD__FAILURE_TITLE'));
  }

  public function ipn() { // called by PaySafeCard API after user validation
    $this->autoRender = false;
    $this->response->type('json');

    if (!$this->request->is('post'))
      throw new NotFoundException('Not post');

    if (empty($this->request->data) || !isset($this->request->data['mtid']))
      throw new NotFoundException('Missing params');

    // setup payment id
    $id = $this->request->data['mtid'];

    // render
    $this->response->send($this->capture($id));
  }

  private function capture($id) {
    // request to capture payment
    $curl = $this->doRequest($this->endpoint . '/payments/'.$id.'/capture', array(), 'POST'); // do request and save into $curl

    if (($curl["error_nr"] != 0) || ($curl["http_status"] === 500)) // check if an error as encountered
      throw new InternalErrorException($curl["error_text"] . ':' . $curl["http_status"]); // error
    if ($curl["http_status"] === 400)
      return array('status' => false, 'error' => 'Payment status invalid.');

    // Valid request
    if ($curl['response']['status'] !== 'SUCCESS')
      return array('status' => false, 'error' => 'Payment status invalid.');
    if ($curl['response']['currency'] !== $this->config['currency'])
      return array('status' => false, 'error' => 'Payment currency invalid.');

    // Setup vars
    $user_id = $curl['response']['customer']['id'];
    $amount = $curl['response']['amount'];

    // Find user
    $user = $this->User->find('first', array('conditions' => array('id' => $user_id)));
    if (empty($user) || !isset($user['User']))
      return array('status' => false, 'error' => 'User not found.');

    // Find offer
    $offer = $this->findOffer($amount);

    // Calculate new sold
    $new_sold = floatval($user['User']['money']) + floatval($offer['credits']);

    // Set new sold
    $this->User->id = $user_id;
    $this->User->saveField('money', $new_sold);

    // set into history
    $this->HistoryC = $this->Components->load('History');
    $this->HistoryC->set('BUY_MONEY', 'shop', null, $user_id);

    $this->loadModel('Paysafecard.PaymentHistory');
    $this->PaymentHistory->create();
    $this->PaymentHistory->set(array(
      'payment_id' => $id,
      'amount' => $amount,
      'credits_gived' => floatval($offer['credits']),
      'user_id' => $user_id
    ));
    $this->PaymentHistory->save();

    // notify user
    $this->loadModel('Notification');
    $this->Notification->setToUser($this->Lang->get('NOTIFICATION__PAYSAFECARD_CREDITED', array('{CREDITS}' => $offer['credits'], '{MONEY_NAME}' => $this->Configuration->getMoneyName(), '{AMOUNT}' => $amount)), $user_id);

    return array('status' => true, 'success' => 'User credited.');
  }

  private function doRequest($url, $curlparam, $method, $headers = array()) {
    $ch = curl_init();

    $header = array(
      "Authorization: Basic " . base64_encode($this->config['api_key']),
      "Content-Type: application/json",
    );

    $header = array_merge($header, $headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    if ($method == 'POST') {
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlparam));
      curl_setopt($ch, CURLOPT_POST, true);
    } elseif ($method == 'GET') {
      if (!empty($curlparam)) {
        curl_setopt($ch, CURLOPT_URL, $url . $curlparam);
        curl_setopt($ch, CURLOPT_POST, false);
      } else {
        curl_setopt($ch, CURLOPT_URL, $url);
      }
    }
    curl_setopt($ch, CURLOPT_PORT, 443);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);

    if (is_array($curlparam)) {
      $curlparam['request_url'] = $url;

    } else {
      $requestURL               = $url . $curlparam;
      $curlparam                = array();
      $curlparam['request_url'] = $requestURL;
    }
    $request  = $curlparam;
    $response = json_decode(curl_exec($ch), true);

    $info        = curl_getinfo($ch);
    $error_nr    = curl_errno($ch);
    $error_text  = curl_error($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return compact('request', 'response', 'info', 'error_nr', 'error_text', 'http_status');
  }

  public function admin_get_histories() {
    if($this->isConnected && $this->Permissions->can('SHOP__ADMIN_MANAGE_PAYMENT')) {

      $this->autoRender = false;
      $this->response->type('json');

      $this->DataTable = $this->Components->load('DataTable');
      $this->modelClass = 'PaymentHistory';
      $this->DataTable->initialize($this);
      $this->paginate = array(
        'fields' => array($this->modelClass.'.payment_id',$this->modelClass.'.amount','User.pseudo',$this->modelClass.'.credits_gived',$this->modelClass.'.created'),
        'recursive' => 1
      );
      $this->DataTable->mDataProp = true;

      $response = $this->DataTable->getResponse();

      $this->response->body(json_encode($response));

    } else {
      throw new ForbiddenException();
    }
  }

  public function admin_save_config() {
    $this->autoRender = false;
    $this->response->type('json');

    if(!$this->isConnected || !$this->Permissions->can('SHOP__ADMIN_MANAGE_PAYMENT'))
      throw new ForbiddenException();
    if(!$this->request->is('ajax'))
      throw new NotFoundException();

    if(empty($this->request->data['api_key']) || empty($this->request->data['api_env']) || empty($this->request->data['currency']) || empty($this->request->data['default_credits_gived_for_1_as_amount']))
      return $this->response->body(json_encode(array('statut' => false, 'msg' => $this->Lang->get('ERROR__FILL_ALL_FIELDS'))));

    // Save
    $this->Config->read(null, 1);
    $this->Config->set(array(
      'api_key' => $this->request->data['api_key'],
      'api_env' => $this->request->data['api_env'],
      'currency' => $this->request->data['currency'],
      'default_credits_gived_for_1_as_amount' => $this->request->data['default_credits_gived_for_1_as_amount']
    ));
    $this->Config->save();

    $this->response->body(json_encode(array('statut' => true, 'msg' => $this->Lang->get('PAYSAFECARD__ADMIN_CONFIG_SAVED'))));
  }

}
