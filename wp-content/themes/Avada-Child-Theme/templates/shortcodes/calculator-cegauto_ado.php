<div class="wrapper">
  <form id="calculator_cegauto_ado" class="" action="" method="post" onsubmit="Calculator('<?=$view?>', jQuery(this).serialize()); return false;">
  <div class="line">
    <div class="head">Gépjármű teljesítménye</div>
    <div class="val">
      <div class="inp-wrapper">
        <input type="number" name="kw" value="">
        <div class="ihint">kW</div>
      </div>
    </div>
  </div>
  <div class="line">
    <div class="head">Környezetvédelmi osztály</div>
    <div class="val">
      <select class="" name="emission">
        <option value="">-- válasszon --</option>
        <?php foreach ((array)$settings['forms']['kornyezetvedelmi_osztalyok'] as $okey => $osztraly): ?>
        <option value="<?=$okey?>"><?=$osztraly?></option>
        <?php endforeach; ?>
      </select>
      <pre><?php print_r($settings['forms']['value']); ?></pre>
    </div>
  </div>
  <div class="line action-line">
    <div class="val">
      <button type="button" ng-click="calculate('<?=$view?>', jQuery('#calculator_<?=$view?>').serialize())" name="button"><?=__('Kalkuláció indítása', 'hc')?></button>
    </div>
  </div>
  </form>
</div>
<br><br><br><br>
