<div class="wrapper">
  <div class="inputs">
    <div class="header">Osztalékadó kalkulátor</div>

      <div class="version-changer">
        <div class="wrapper">
          <div class="" ng-repeat="ver in settings.versions">
            <div class="wrap">
              <input type="radio" id="ver_v{{ver}}" ng-value="ver" ng-model="form.version"> <label title="Számolás {{ver}}. évi jogszabályok alapján." for="ver_v{{ver}}">{{ver}}</label>
            </div>
          </div>
        </div>
      </div>
      
    <div class="inp-body">
      <div class="line" ng-class="{missing:missing.indexOf('osztalek_kifizetes')!==-1, error:error_elements['osztalek_kifizetes']}">
        <div class="head">
          Osztalék kifizetését tervezi *
          <div class="error-hint" ng-if="error_elements.indexOf('osztalek_kifizetes')!==-1">{{error_elements['osztalek_kifizetes']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.osztalek_kifizetes" ng-options="item for item in settings.select_yesno"></select>
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('osztalekeloleg_kifizetes')!==-1, error:error_elements['osztalekeloleg_kifizetes']}">
        <div class="head">
          Osztalékelőleg kifizetését tervezi *
          <div class="error-hint" ng-if="error_elements.indexOf('osztalekeloleg_kifizetes')!==-1">{{error_elements['osztalekeloleg_kifizetes']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.osztalekeloleg_kifizetes" ng-options="item for item in settings.select_yesno"></select>
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('brutto_alap')!==-1, error:error_elements['brutto_alap']}">
        <div class="head">
          <span ng-if="form.osztalek_kifizetes=='Igen'">Bruttó osztalék összege *</span>
          <span ng-if="form.osztalekeloleg_kifizetes=='Igen'">Bruttó osztalékelőleg összege *</span>
          <div class="error-hint" ng-if="error_elements.indexOf('brutto_alap')!==-1">{{error_elements['brutto_alap']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" input-thousand-separator="currency" min="0" ng-model="form.brutto_alap">
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('teljes_targyev_brutto_munkaber')!==-1, error:error_elements['teljes_targyev_brutto_munkaber']}">
        <div class="head">
          Teljes tárgyévi bruttó munkabér összege
          <div class="error-hint" ng-if="error_elements.indexOf('teljes_targyev_brutto_munkaber')!==-1">{{error_elements['teljes_targyev_brutto_munkaber']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" input-thousand-separator="currency" min="0" ng-model="form.teljes_targyev_brutto_munkaber">
          </div>
        </div>
      </div>

      <div class="line" ng-class="{missing:missing.indexOf('teljes_targyev_brutto_tarasvall_kivet')!==-1, error:error_elements['teljes_targyev_brutto_tarasvall_kivet']}">
        <div class="head">
          Teljes tárgyévi bruttó társas vállalkozói kivét összege
          <div class="error-hint" ng-if="error_elements.indexOf('teljes_targyev_brutto_tarasvall_kivet')!==-1">{{error_elements['teljes_targyev_brutto_tarasvall_kivet']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" input-thousand-separator="currency" min="0" ng-model="form.teljes_targyev_brutto_tarasvall_kivet">
          </div>
        </div>
      </div>

      <div class="line" ng-class="{missing:missing.indexOf('targyev_megszerzett_brutto_osztalek')!==-1, error:error_elements['targyev_megszerzett_brutto_osztalek']}">
        <div class="head">
          A tárgyévben megszerzett bruttó osztalék összege
          <div class="error-hint" ng-if="error_elements.indexOf('targyev_megszerzett_brutto_osztalek')!==-1">{{error_elements['targyev_megszerzett_brutto_osztalek']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" input-thousand-separator="currency" min="0" ng-model="form.targyev_megszerzett_brutto_osztalek">
          </div>
        </div>
      </div>

      <div class="line" ng-class="{missing:missing.indexOf('targyev_vall_kivont_jovedelem')!==-1, error:error_elements['targyev_vall_kivont_jovedelem']}">
        <div class="head">
          Tárgyévi vállalkozásból kivont jövedelem összege
          <div class="error-hint" ng-if="error_elements.indexOf('targyev_vall_kivont_jovedelem')!==-1">{{error_elements['targyev_vall_kivont_jovedelem']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" input-thousand-separator="currency" min="0" ng-model="form.targyev_vall_kivont_jovedelem">
          </div>
        </div>
      </div>

      <div class="line" ng-class="{missing:missing.indexOf('targyev_ertekpapkolcson_jovedelem')!==-1, error:error_elements['targyev_ertekpapkolcson_jovedelem']}">
        <div class="head">
          Tárgyévi értékpapír-kölcsönzésből származó jövedelem összege
          <div class="error-hint" ng-if="error_elements.indexOf('targyev_ertekpapkolcson_jovedelem')!==-1">{{error_elements['targyev_ertekpapkolcson_jovedelem']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" input-thousand-separator="currency" min="0" ng-model="form.targyev_ertekpapkolcson_jovedelem">
          </div>
        </div>
      </div>

      <div class="line" ng-class="{missing:missing.indexOf('targyev_arfolyamnyereseg_jovedelem')!==-1, error:error_elements['targyev_arfolyamnyereseg_jovedelem']}">
        <div class="head">
          Tárgyévi árfolyamnyereségből származó jövedelem összege
          <div class="error-hint" ng-if="error_elements.indexOf('targyev_arfolyamnyereseg_jovedelem')!==-1">{{error_elements['targyev_arfolyamnyereseg_jovedelem']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" input-thousand-separator="currency" min="0" ng-model="form.targyev_arfolyamnyereseg_jovedelem">
          </div>
        </div>
      </div>

      <div class="line" ng-class="{missing:missing.indexOf('egyeb_szja_jovedelem')!==-1, error:error_elements['egyeb_szja_jovedelem']}">
        <div class="head">
          Egyéb SZJA tv. szerinti összevont adóalapba tartozó jövedelmek összege
          <div class="error-hint" ng-if="error_elements.indexOf('egyeb_szja_jovedelem')!==-1">{{error_elements['egyeb_szja_jovedelem']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" input-thousand-separator="currency" min="0" ng-model="form.egyeb_szja_jovedelem">
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
          <tr class="">
            <td class="h">
              <strong>
                <span ng-if="form.osztalek_kifizetes=='Igen'">Bruttó osztalék alap:</span>
                <span ng-if="form.osztalekeloleg_kifizetes=='Igen'">Bruttó osztalékelőleg alap:</span>
            </strong></td>
            <td class="v"><strong>{{result.brutto_alap|cash:'Ft':''}}</strong></td>
          </tr>
          <tr class="sm">
            <td class="h">Tárgyévben megszerzett összes jövedelem</td>
            <td class="v">{{result.jovedelem|cash:'Ft':''}}</td>
          </tr>
          <tr>
            <td colspan="2" class="head">
              Fizetendő adók
            </td>
          </tr>
          <tr class="sm">
            <td class="h">Fizetendő személyi jövedelemadó</td>
            <td class="v">{{result.fizetendo_szja|cash:'Ft':''}}</td>
          </tr>
          <tr>
            <td class="h">Fizetendő szociális hozzájárulási adó</td>
            <td class="v">{{result.fizetendo_szocho|cash:'Ft':''}}</td>
          </tr>
          <tr class="hl">
            <td class="h"><strong>Összesen fizetendő osztalékadó</strong></td>
            <td class="v"><strong>{{result.fizetendo|cash:'Ft':''}}</strong></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
