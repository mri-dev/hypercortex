<div class="wrapper">
  <div class="inputs">
    <div class="header">Ingatlan értékesítés</div>
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
      
      <div class="line" ng-class="{missing:missing.indexOf('atruhazas_eve')!==-1, error:error_elements['atruhazas_eve']}">
        <div class="head">
          Ingatlan ártuházásának éve *
          <div class="error-hint" ng-if="error_elements.indexOf('atruhazas_eve')!==-1">{{error_elements['atruhazas_eve']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="number" min="0" ng-model="form.atruhazas_eve">
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('atruhazasbol_bevetel')!==-1, error:error_elements['atruhazasbol_bevetel']}">
        <div class="head">
          Ingatlan átruházásából származó bevétel *
          <div class="error-hint" ng-if="error_elements.indexOf('atruhazasbol_bevetel')!==-1">{{error_elements['atruhazasbol_bevetel']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" input-thousand-separator="currency" min="0" ng-model="form.atruhazasbol_bevetel">
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('szerzes_eve')!==-1, error:error_elements['szerzes_eve']}">
        <div class="head">
          Szerzési éve *
          <div class="error-hint" ng-if="error_elements.indexOf('szerzes_eve')!==-1">{{error_elements['szerzes_eve']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="number" min="0" ng-model="form.szerzes_eve">
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('megszerzes_osszeg')!==-1, error:error_elements['megszerzes_osszeg']}">
        <div class="head">
          Megszerzésre fordított összeg *
          <div class="error-hint" ng-if="error_elements.indexOf('megszerzes_osszeg')!==-1">{{error_elements['megszerzes_osszeg']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" input-thousand-separator="currency" min="0" ng-model="form.megszerzes_osszeg">
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('megszerzes_egyeb_kiadas')!==-1, error:error_elements['megszerzes_egyeb_kiadas']}">
        <div class="head">
          Megszerzésre fordított egyéb kiadások *
          <div class="error-hint" ng-if="error_elements.indexOf('megszerzes_egyeb_kiadas')!==-1">{{error_elements['megszerzes_egyeb_kiadas']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" input-thousand-separator="currency" min="0" ng-model="form.megszerzes_egyeb_kiadas">
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('erteknovelo_beruhazasok')!==-1, error:error_elements['erteknovelo_beruhazasok']}">
        <div class="head">
          Értéknövelő beruházások *
          <div class="error-hint" ng-if="error_elements.indexOf('erteknovelo_beruhazasok')!==-1">{{error_elements['erteknovelo_beruhazasok']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" input-thousand-separator="currency" min="0" ng-model="form.erteknovelo_beruhazasok">
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('erteknovelo_beruhazasok_allammegovas')!==-1, error:error_elements['erteknovelo_beruhazasok_allammegovas']}">
        <div class="head">
          Értéknövelő beruházásból az állagmegóvás költsége *
          <div class="error-hint" ng-if="error_elements.indexOf('erteknovelo_beruhazasok_allammegovas')!==-1">{{error_elements['erteknovelo_beruhazasok_allammegovas']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" input-thousand-separator="currency" min="0" ng-model="form.erteknovelo_beruhazasok_allammegovas">
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('atruhazas_koltsegei')!==-1, error:error_elements['atruhazas_koltsegei']}">
        <div class="head">
          Átruházás költségei *
          <div class="error-hint" ng-if="error_elements.indexOf('atruhazas_koltsegei')!==-1">{{error_elements['atruhazas_koltsegei']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" input-thousand-separator="currency" min="0" ng-model="form.atruhazas_koltsegei">
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
      <div class="result-jog-text">
        Az eredmény kiszámítása a(z) {{form.version}}. évi jogszabályok alkalmazásával történt.
      </div>
      <table class="result-table">
        <tbody>
          <tr class="sm">
            <td class="h">Bevétel</td>
            <td class="v">{{result.bevetel|cash:'Ft':''}}</td>
          </tr class="sm">
          <tr>
            <td class="h">Költség</td>
            <td class="v">{{result.koltseg|cash:'Ft':''}}</td>
          </tr>
          <tr class="sm">
            <td class="h">Jövedelem</td>
            <td class="v">{{result.jovedelem|cash:'Ft':''}}</td>
          </tr>
          <tr class="hl">
            <td class="h"><strong>Fizetendő személyi jövedelemadó</strong></td>
            <td class="v"><strong>{{result.fizetendo_szja|cash:'Ft':''}}</strong></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
