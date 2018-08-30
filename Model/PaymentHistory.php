<?php
class PaymentHistory extends PaysafecardAppModel {
  public $belongsTo = array(
    'User'
  );
}
