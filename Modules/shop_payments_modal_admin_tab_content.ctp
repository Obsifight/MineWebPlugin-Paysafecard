<div class="tab-pane" id="tab_psc_official">

  <h3><?= $Lang->get('PAYSAFECARD__ADMIN_CONFIG') ?></h3>

  <br><br>

  <form action="<?= $this->Html->url(array('controller' => 'pay', 'action' => 'save_config', 'plugin' => 'paysafecard', 'admin' => true)) ?>" data-ajax="true">

    <div class="form-group">
      <label><?= $Lang->get('PAYSAFECARD__ADMIN_CONFIG_API_KEY') ?></label>
      <input type="text" class="form-control" name="api_key" placeholder="Ex: psc_XkJJWe2CiEpt0ZeWQwnSkt27WWdOA-2"<?= (isset($paysafecardConfig['Config']['api_key'])) ? ' value="'.$paysafecardConfig['Config']['api_key'].'"' : '' ?>>
    </div>

    <div class="form-group">
      <label><?= $Lang->get('PAYSAFECARD__ADMIN_CONFIG_API_ENV') ?></label>
      <select class="form-control" name="api_env">
        <option value="PRODUCTION"<?= ($paysafecardConfig['Config']['api_env'] === 'PRODUCTION') ? ' selected' : '' ?>><?= $Lang->get('PAYSAFECARD__ADMIN_CONFIG_API_ENV_PRODUCTION') ?></option>
        <option value="TEST"<?= ($paysafecardConfig['Config']['api_env'] === 'TEST') ? ' selected' : '' ?>><?= $Lang->get('PAYSAFECARD__ADMIN_CONFIG_API_ENV_TEST') ?></option>
      </select>
    </div>

    <div class="form-group">
      <label><?= $Lang->get('PAYSAFECARD__ADMIN_CONFIG_CURRENCY') ?></label>
      <select class="form-control" name="currency">
        <option value="EUR"<?= ($paysafecardConfig['Config']['currency'] === 'EUR') ? ' selected' : '' ?>>Euro – EUR</option>
        <option value="GBP"<?= ($paysafecardConfig['Config']['currency'] === 'GBP') ? ' selected' : '' ?>>United Kingdom Pounds – GBP</option>
        <option value="NOK"<?= ($paysafecardConfig['Config']['currency'] === 'NOK') ? ' selected' : '' ?>>Norway Kroner – NOK</option>
        <option value="RON"<?= ($paysafecardConfig['Config']['currency'] === 'RON') ? ' selected' : '' ?>>Romania New Lei – RON</option>
        <option value="SKK"<?= ($paysafecardConfig['Config']['currency'] === 'SKK') ? ' selected' : '' ?>>Slovakia Koruny – SKK</option>
        <option value="TRY"<?= ($paysafecardConfig['Config']['currency'] === 'TRY') ? ' selected' : '' ?>>Turkey New Lira – TRY</option>
        <option value="USD"<?= ($paysafecardConfig['Config']['currency'] === 'USD') ? ' selected' : '' ?>>United States Dollars – USD</option>
      </select>
    </div>

    <div class="form-group">
      <label><?= $Lang->get('PAYSAFECARD__ADMIN_CONFIG_DEFAULT_CREDITS_GIVED_FOR_1_AS_AMOUNT', array('{MONEY_NAME}' => ucfirst($Configuration->getMoneyName()))) ?></label>
      <input type="text" class="form-control" name="default_credits_gived_for_1_as_amount" placeholder="Ex: 80"<?= (isset($paysafecardConfig['Config']['default_credits_gived_for_1_as_amount'])) ? ' value="'.$paysafecardConfig['Config']['default_credits_gived_for_1_as_amount'].'"' : '' ?>>
    </div>

    <div class="form-group">
      <button type="submit" class="btn btn-primary"><?= $Lang->get('GLOBAL__SUBMIT') ?></button>
    </div>

  </form>

  <hr>

  <h3><?= $Lang->get('SHOP__PAYSAFECARD_HISTORIES') ?></h3>

  <table class="table table-bordered dataTable" id="histories_paysafecard_official">
    <thead>
      <tr>
        <th><?= $Lang->get('PAYSAFECARD__HISTORY_PAYMENT_ID') ?></th>
        <th><?= $Lang->get('USER__USERNAME') ?></th>
        <th><?= $Lang->get('SHOP__GLOBAL_AMOUNT') ?></th>
        <th><?= ucfirst($Configuration->getMoneyName()) ?></th>
        <th><?= $Lang->get('GLOBAL__CREATED') ?></th>
      </tr>
    </thead>
    <tbody>
    </tbody>
  </table>
  <script type="text/javascript">
  $(document).ready(function() {
    $('#histories_paysafecard_official').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": false,
      "info": false,
      "autoWidth": false,
      'searching': true,
      "bProcessing": true,
      "bServerSide": true,
      "sAjaxSource": "<?= $this->Html->url(array('controller' => 'pay', 'action' => 'get_histories', 'plugin' => 'paysafecard', 'admin' => true)) ?>",
      "aoColumns": [
          {mData:"PaymentHistory.payment_id"},
          {mData:"User.pseudo"},
          {mData:"PaymentHistory.amount"},
          {mData:"PaymentHistory.credits_gived"},
          {mData:"PaymentHistory.created"}
      ],
    });
  });
  </script>
  <hr>

</div>
