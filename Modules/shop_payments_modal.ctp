<?php if ($paysafecard_currency) { ?>
  <a class="btn btn-info btn-block" data-toggle="collapse" href="#psc">PaySafeCard</a>
  <br>
  <div class="collapse" id="psc">
    <form method="POST" action="/shop/paysafecard/pay/">
      <div class="form-group">
        <label for="amount"><?= $Lang->get('PAYSAFECARD__AMOUNT') ?>:</label>
        <input type="number" step="0.1" name="amount" class="form-control" value="10">
      </div>
      <div class="form-group">
        <label for="currency"><?= $Lang->get('PAYSAFECARD__CURRENCY') ?>:</label>
        <input type="text" name="currency" class="form-control" value="<?= $paysafecard_currency ?>" disabled>
      </div>
      <input type="hidden" name="customer_id" class="form-control" value="<?= $this->controller->User->getKey('id') ?>">
      <input type="hidden" name="data[_Token][key]" value="<?= $csrfToken ?>">
      <button type="submit" name="action" value="payment" class="btn btn-success pull-right">
        <?= $Lang->get('PAYSAFECARD__PAY_BTN') ?>
      </button>
      <?= $this->Html->image('Paysafecard.logo_paysafecard.png', array('height' => '40px')) ?>
      <div class="clearfix"></div>
    </form>
  </div>
<?php } ?>
