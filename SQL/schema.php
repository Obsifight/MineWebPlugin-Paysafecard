<?php
class PaysafecardAppSchema extends CakeSchema {

	public $file = 'schema.php';

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $paysafecard__configs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 20, 'unsigned' => false, 'key' => 'primary'),
		'api_key' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 150, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'api_env' => array('type' => 'string', 'null' => false, 'default' => 'PRODUCTION', 'length' => 10, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'currency' => array('type' => 'string', 'null' => false, 'default' => 'EUR', 'length' => 3, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'default_credits_gived_for_1_as_amount' => array('type' => 'float', 'null' => false, 'default' => '80', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

	public $paysafecard__payment_histories = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
    'payment_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 150, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'amount' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
		'credits_gived' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

	public $paysafecard__offers = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 20, 'unsigned' => false, 'key' => 'primary'),
		'amount' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
		'credits' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

}
