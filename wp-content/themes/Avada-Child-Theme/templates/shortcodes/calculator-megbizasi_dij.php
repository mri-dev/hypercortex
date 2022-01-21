<div class="wrapper">
  <div class="inputs">
    <div class="header">Megbízási díj kalkulátor</div>
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

      <div class="line line-switcher" ng-class="{missing:missing.indexOf('jovedelem')!==-1, error:error_elements['mode']}">
        <div class="head">
          A ráfordítás jellege
          <div class="error-hint" ng-if="error_elements.indexOf('mode')!==-1">{{error_elements['mode']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <div class="radio-switch">
              <input type="radio" ng-model="form.mode" value="netto" id="netto"> <label for="netto">Nettó</label>
              <input type="radio" ng-model="form.mode" value="brutto" id="brutto"> <label for="brutto">Bruttó</label>
              <input type="radio" ng-model="form.mode" value="teljes" id="teljes"> <label for="teljes">Teljes költség</label>
            </div>            
          </div>
        </div>
      </div>
      
      <div class="line" ng-if="form.mode" ng-class="{missing:missing.indexOf('ceg_kisvallalati_ado_alany')!==-1, error:error_elements['ceg_kisvallalati_ado_alany']}">
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

      <div class="line" ng-if="form.mode" ng-class="{missing:missing.indexOf('megbizasi_dij')!==-1, error:error_elements['megbizasi_dij']}">
        <div class="head">
          <span ng-if="(form.mode=='brutto' || form.mode=='netto')"><span ng-if="(form.mode=='brutto')">Bruttó</span><span ng-if="(form.mode=='netto')">Nettó</span> megbízási díj *</span>
          <span ng-if="(form.mode=='teljes')">A cég teljes költsége *</span>
          <div class="error-hint" ng-if="error_elements.indexOf('megbizasi_dij')!==-1">{{error_elements['megbizasi_dij']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" ng-model="form.megbizasi_dij" input-thousand-separator="currency">
          </div>
        </div>
      </div>

      <div class="line" ng-if="form.mode" ng-class="{missing:missing.indexOf('nap')!==-1, error:error_elements['nap']}">
        <div class="head">
          Megbízott napok száma *
          <div class="error-hint" ng-if="error_elements.indexOf('nap')!==-1">{{error_elements['nap']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="number" min="1" step="1" ng-model="form.nap" input-thousand-separator="currency">
          </div>
        </div>
      </div>

      <div class="line" ng-if="form.mode" ng-class="{missing:missing.indexOf('koltseghanyad')!==-1, error:error_elements['koltseghanyad']}">
        <div class="head">
          Költséghányad %-a *
          <div class="error-hint" ng-if="error_elements.indexOf('koltseghanyad')!==-1">{{error_elements['koltseghanyad']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="number" min="0" step="5" ng-model="form.koltseghanyad">
            <div class="ihint">%</div>
          </div>
        </div>
      </div>

      <div ng-if="form.mode" class="line" ng-class="{missing:missing.indexOf('oregsegi_nyugdijas')!==-1, error:error_elements['oregsegi_nyugdijas']}">
        <div class="head">
          Öregségi nyugdíjas *
          <div class="error-hint" ng-if="error_elements.indexOf('oregsegi_nyugdijas')!==-1">{{error_elements['oregsegi_nyugdijas']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.oregsegi_nyugdijas" ng-options="item for item in settings.select_yesno"></select>
          </div>
        </div>
      </div>

      <div class="line action-line" ng-if="!loading && form.mode">
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
          <tr ng-if="form.mode=='teljes'">
            <td class="h"><strong>A cég teljes költsége</strong></td>
            <td class="v"><strong>{{result.teljes|cash:'Ft':''}}</strong></td>
          </tr>
          <tr ng-class="{hl: (form.mode == 'netto' || form.mode == 'teljes')}">
            <td class="h"><strong>Bruttó megbízási díj</strong></td>
            <td class="v"><strong>{{result.brutto|cash:'Ft':''}}</strong></td>
          </tr>
          <tr>
            <td class="h">Személyi jövedelemadó</td>
            <td class="v">{{result.ado_szja|cash:'Ft':''}}</td>
          </tr>
          <tr>
            <td class="h">TB járulék</td>
            <td class="v">{{result.ado_tb|cash:'Ft':''}}</td>
          </tr>
          <tr>
            <td class="h"><strong>Összes levonás a bruttó megbízási díjból</strong></td>
            <td class="v"><strong>{{result.levonas_bruttobol|cash:'Ft':''}}</strong></td>
          </tr>
          <tr ng-class="{hl: (form.mode == 'brutto' || form.mode == 'teljes')}">
            <td class="h"><strong>Nettó megbízási díj</strong></td>
            <td class="v"><strong>{{result.netto|cash:'Ft':''}}</strong></td>
          </tr>
          <tr>
            <td colspan="2" class="head">
              Cég által fizetendő költségek
            </td>
          </tr>
          <tr ng-if="(result.values.kiva_adoalany=='Nem')" ng-class="{lt: result.values.kiva_adoalany=='Igen'}">
            <td class="h">Fizetendő szociális hozzájárulási adó</td>
            <td class="v">{{result.ado_szocialis_hozzajarulas|cash:'Ft':''}}</td>
          </tr>
          <tr ng-if="(result.values.kiva_adoalany=='Nem' && ['2022'].indexOf(result.version) === -1)" ng-class="{lt: result.values.kiva_adoalany=='Igen'}">
            <td class="h">Fizetendő szakképzési hozzájárulás</td>
            <td class="v">{{result.ado_szakkepzesi_hozzajarulas|cash:'Ft':''}}</td>
          </tr>
          <tr ng-if="(result.values.kiva_adoalany=='Igen')" ng-class="{lt: result.values.kiva_adoalany=='Nem'}">
            <td class="h">Fizetendő kisvállalati adó (KIVA)</td>
            <td class="v">{{result.ado_kiva|cash:'Ft':''}}</td>
          </tr>
          <tr class="hl" ng-if="(result.values.kiva_adoalany=='Igen')">
            <td class="h">Összes ráfordítás KIVA alany cég esetén</td>
            <td class="v">{{result.ado_raforditas|cash:'Ft':''}}</td>
          </tr>
          <tr class="hl" ng-if="(result.values.kiva_adoalany=='Nem')">
            <td class="h">Összes ráfordítás nem KIVA alany cég esetén</td>
            <td class="v">{{result.ado_raforditas|cash:'Ft':''}}</td>
          </tr>
          <tr class="hlh" >
            <td class="h">NAV felé utalandó összes adó és járulék</td>
            <td class="v">{{result.ado_nav|cash:'Ft':''}}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
