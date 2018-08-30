<div class="container">
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><?= $Lang->get('PAYSAFECARD__SUCCESS_TITLE') ?></h3>
        </div>
        <div class="panel-body">
          <?php if ($status) { ?>
            <div class="alert alert-success"><?= $Lang->get('PAYSAFECARD__SUCCESS_CONTENT') ?></div>
          <?php } else { ?>
            <div class="alert alert-danger"><b>Error:</b> <?= $error ?></div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</div>
