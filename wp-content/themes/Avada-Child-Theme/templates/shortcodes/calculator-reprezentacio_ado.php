<div class="wrapper">
  <div class="inputs">
    <div class="header">Reprezentáció adó kalkulátor</div>
    <div class="inp-body">
      <div class="version-changer">
        <div class="wrapper">
          <div class="" ng-repeat="ver in settings.versions" ng-if="['2020/2', '2020/1', '2019'].indexOf(ver) === -1">
            <div class="wrap">
              <input type="radio" id="ver_v{{ver}}" ng-value="ver" ng-model="form.version"> <label title="Számolás {{ver}}. évi jogszabályok alapján." for="ver_v{{ver}}">{{ver}}</label>
            </div>
          </div>
        </div>
      </div>
      
      <div class="line" ng-class="{missing:missing.indexOf('ceg_kisvallalati_ado_alany')!==-1, error:error_elements['ceg_kisvallalati_ado_alany']}">
        <div class="head">
          A cég Kisvállalati adó (KIVA) alanya? *
          <div class="error-hint" ng-if="error_elements.indexOf('ceg_kisvallalati_ado_alany')!==-1">{{error_elements['ceg_kisvallalati_ado_alany']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.ceg_kisvallalati_ado_alany" ng-options="item for item in settings.select_yesno"></select>
          </div>
        </div>
      </div>

      <div class="line" ng-class="{missing:missing.indexOf('szamla_brutto')!==-1, error:error_elements['szamla_brutto']}">
        <div class="head">
          Számla bruttó értéke *
          <div class="error-hint" ng-if="error_elements.indexOf('szamla_brutto')!==-1">{{error_elements['szamla_brutto']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" ng-model="form.szamla_brutto" input-thousand-separator="currency">
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
            <td class="h"><strong>Számla bruttó értéke</strong></td>
            <td class="v"><strong>{{result.szamla_brutto|cash:'Ft':''}}</strong></td>
          </tr>
          <tr>
            <td colspan="2" class="head">
              Cég által fizetendő költségek
            </td>
          </tr>          
          <tr class="sm">
            <td class="h">Fizetendő személyi jövedelem adó</td>
            <td class="v">{{result.ado_szja|cash:'Ft':''}}</td>
          </tr>
          <tr ng-if="(result.values.kiva_adoalany=='Nem')" ng-class="{lt: result.values.kiva_adoalany=='Igen'}">
            <td class="h">Fizetendő szociális hozzájárulási adó</td>
            <td class="v">+{{result.ado_szocialis_hozzajarulas|cash:'Ft':''}}</td>
          </tr>
          <tr ng-if="(result.values.kiva_adoalany=='Nem')" ng-class="{lt: result.values.kiva_adoalany=='Igen'}">
            <td class="h">Fizetendő szakképzési hozzájárulás</td>
            <td class="v">+{{result.ado_szakkepzesi_hozzajarulas|cash:'Ft':''}}</td>
          </tr>
          <tr ng-if="(result.values.kiva_adoalany=='Igen')" ng-class="{lt: result.values.kiva_adoalany=='Nem'}">
            <td class="h">Fizetendő kisvállalati adó (KIVA)</td>
            <td class="v">+{{result.ado_kisvallalati|cash:'Ft':''}}</td>
          </tr>
          <tr class="hl" >
            <td class="h">Céget terhelő összes adó</td>
            <td class="v">{{result.ado_munkaltato|cash:'Ft':''}}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
