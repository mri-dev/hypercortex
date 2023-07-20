<div class="wrapper">
  <div class="inputs">
    <div class="header">Cégautóadó kalkulátor</div>
    <div class="inp-body">
      <div class="version-changer">
        <div class="wrapper">
          <div class="" ng-repeat="ver in settings.versions">
            <div class="wrap">
              <input type="radio" id="ver_v{{ver}}" ng-value="ver" ng-model="form.version"> <label title="Számolás {{ver}}. évi jogszabályok alapján." for="ver_v{{ver}}">{{ver}}</label>
            </div>
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('kw')!==-1, error:error_elements['kw']}">
        <div class="head">
          Gépjármű teljesítménye *
          <div class="error-hint" ng-if="error_elements.indexOf('kw')!==-1">{{error_elements['kw']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="number" step="1" min="0" style="width: 80px; text-align:center;" ng-model="form.kw" value="">
            <div class="ihint">kW</div>
          </div>
        </div>
      </div>
      <div style="text-align: center;font-size: 0.8rem;letter-spacing: 2px;margin: 0 0 10px 0;">vagy</div>
      <div class="line" ng-class="{missing:missing.indexOf('hp')!==-1, error:error_elements['hp']}">
        <div class="head">
          Gépjármű teljesítménye *
          <div class="error-hint" ng-if="error_elements.indexOf('hp')!==-1">{{error_elements['hp']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="number" step="1" min="0" style="width: 80px; text-align:center;" ng-model="form.hp" value="">
            <div class="ihint">lóerő</div>
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('emission')!==-1, error:error_elements['emission']}">
        <div class="head">
          Környezetvédelmi osztály *
          <div class="error-hint" ng-if="error_elements.indexOf('emission')!==-1">{{error_elements['emission']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.emission">
              <option value="">-- válasszon --</option>
              <?php foreach ((array)$settings['forms']['kornyezetvedelmi_osztalyok'] as $okey => $osztraly): ?>
              <option value="<?=$okey?>"><?=$osztraly?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="line action-line" ng-if="!loading">
        <div class="val">
          <button type="button" ng-click="calculate('<?=$view?>')" name="button"><?=__('Kalkuláció indítása', 'hc')?></button>
        </div>
      </div>
    </div>
  </div>
  <div class="result-view">
    <div class="line-header">Kalkuláció eredménye</div>
    <div class="not-resulted" ng-if="!loaded || results===false"><i class="fa fa-bell-o" aria-hidden="true"></i> Az eredmény megjelenítéséhez kérjük töltse ki a táblázatot!</div>
    <div class="loader" ng-if="loading">Eredmény kiértékelése folyamatban...</div>
    <div class="error-msg" ng-if="error" ng-bind-html="error|unsafe"></div>
    <div class="result-body" ng-if="loaded && result!==false">
    <div class="result-jog-text">{{result.result_comment}}</div>
      <table class="result-table">
        <tbody>
          <tr>
            <td class="h">Gépjármű után havonta fizetendő cégautóadó</td>
            <td class="v">{{result|cash:'Ft':''}}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
