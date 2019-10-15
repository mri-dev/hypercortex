<div class="wrapper">
  <div class="inputs">
    <div class="line">
      <div class="head">Gépjármű teljesítménye</div>
      <div class="val">
        <div class="inp-wrapper">
          <input type="number" ng-model="form.kw" value="">
          <div class="ihint">kW</div>
        </div>
      </div>
    </div>
    <div class="line">
      <div class="head">Környezetvédelmi osztály</div>
      <div class="val">
        <select class="" ng-model="form.emission">
          <option value="">-- válasszon --</option>
          <?php foreach ((array)$settings['forms']['kornyezetvedelmi_osztalyok'] as $okey => $osztraly): ?>
          <option value="<?=$okey?>"><?=$osztraly?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="line action-line" ng-if="!loading">
      <div class="val">
        <button type="button" ng-click="calculate('<?=$view?>')" name="button"><?=__('Kalkuláció indítása', 'hc')?></button>
      </div>
    </div>
  </div>
  <div class="loader" ng-if="loading">
    Eredmény kiértékelése folyamatban...
  </div>
  <div class="result-view" ng-if="loaded && result!==false">
    <div class="line-header">
      Kalkuláció eredménye
    </div>
    <div class="line">
      <div class="head">Gépjármű után fizetendő cégautóadó</div>
      <div class="val">{{result|cash:'Ft':''}}</div>
    </div>
  </div>
</div>
<br><br><br><br>
