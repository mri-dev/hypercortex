<div class="wrapper">
  <div class="inputs">
    <div class="line" ng-class="{missing:missing.indexOf('iden_kezdett_dolgozni')!==-1, error:error_elements.indexOf('iden_kezdett_dolgozni')!==-1}">
      <div class="head">
        Idén kezdett el dolgozni a jelenlegi munkahelyén? *
        <div class="error-hint" ng-if="error_elements.indexOf('iden_kezdett_dolgozni')!==-1">{{error_elements['iden_kezdett_dolgozni']}}</div>
      </div>
      <div class="val">
        <div class="inp-wrapper">
          <select class="" ng-model="form.iden_kezdett_dolgozni" ng-options="item for item in settings.select_yesno"></select>
        </div>
      </div>
    </div>
    <div class="line" ng-if="form.iden_kezdett_dolgozni=='Igen'" ng-class="{missing:missing.indexOf('belepes_datuma')!==-1, error:error_elements.indexOf('belepes_datuma')!==-1}">
      <div class="head">
        Belépés dátuma? *
        <div class="head-hint">A munkába állás első napja.</div>
        <div class="error-hint" ng-if="error_elements.indexOf('belepes_datuma')!==-1">{{error_elements['belepes_datuma']}}</div>
      </div>
      <div class="val">
        <div class="inp-wrapper">
          <input type="date" ng-model="form.belepes_datuma" value="">
        </div>
      </div>
    </div>
    <div class="line" ng-class="{missing:missing.indexOf('athozott_szabadsagok')!==-1, error:error_elements.indexOf('athozott_szabadsagok')!==-1}">
      <div class="head">
        Előző évről áthozott szabadságok száma *
        <div class="error-hint" ng-if="error_elements.indexOf('athozott_szabadsagok')!==-1">{{error_elements['athozott_szabadsagok']}}</div>
      </div>
      <div class="val">
        <div class="inp-wrapper">
          <input type="number" style="width: 80px;" min="0" ng-model="form.athozott_szabadsagok">
        </div>
      </div>
    </div>

    <div class="line" ng-class="{missing:missing.indexOf('szuletesi_ev')!==-1, error:error_elements.indexOf('szuletesi_ev')!==-1}">
      <div class="head">
        Munkavállaló születési éve *
        <div class="error-hint" ng-if="error_elements.indexOf('szuletesi_ev')!==-1">{{error_elements['szuletesi_ev']}}</div>
      </div>
      <div class="val">
        <div class="inp-wrapper">
          <input type="number" style="width: 80px;" min="0" ng-model="form.szuletesi_ev">
        </div>
      </div>
    </div>
    <div class="line action-line" ng-if="!loading">
      <div class="val">
        <button type="button" ng-click="calculate('<?=$view?>')" name="button"><?=__('Kalkuláció indítása', 'hc')?></button>
      </div>
    </div>
  </div>
  <div class="result-view">
    <div class="line-header">Kalkuláció eredménye</div>
    <div class="not-resulted" ng-if="!loaded || results===false"><i class="fa fa-bell-o" aria-hidden="true"></i> Az eredmény kiértékeléséhez konfigurálja a beállításokat!</div>
    <div class="loader" ng-if="loading">Eredmény kiértékelése folyamatban...</div>
    <div class="error-msg" ng-if="error" ng-bind-html="error|unsafe"></div>
    <div class="result-body" ng-if="loaded && result!==false">
      <table class="result-table">
        <tbody>
          <tr>
            <td class="h">Gépjármű után fizetendő cégautóadó</td>
            <td class="v">{{result|cash:'Ft':''}}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
