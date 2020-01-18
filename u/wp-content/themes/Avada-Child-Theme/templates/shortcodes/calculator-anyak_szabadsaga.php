<div class="wrapper">
  <div class="inputs">
    <div class="header">Szülésről visszatérő anyák szabadsága</div>
    <div class="inp-body">
      <div class="line" ng-class="{missing:missing.indexOf('szuletesi_ev')!==-1, error:error_elements['szuletesi_ev']}">
        <div class="head">
          Munkavállaló születési éve *
          <div class="error-hint" ng-if="error_elements.indexOf('szuletesi_ev')!==-1">{{error_elements['szuletesi_ev']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="number" min="0" ng-model="form.szuletesi_ev">
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('munkaviszony_kezedete')!==-1, error:error_elements['munkaviszony_kezedete']}">
        <div class="head">
          Munkaviszony kezdete *
          <div class="error-hint" ng-if="error_elements.indexOf('munkaviszony_kezedete')!==-1">{{error_elements['munkaviszony_kezedete']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="date" ng-model="form.munkaviszony_kezedete" value="">
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('szulev_igenybevett_szabadsag')!==-1, error:error_elements['szulev_igenybevett_szabadsag']}">
        <div class="head">
          A szülés évében igénybe vett szabadság *
          <div class="error-hint" ng-if="error_elements.indexOf('szulev_igenybevett_szabadsag')!==-1">{{error_elements['szulev_igenybevett_szabadsag']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="number" min="0" ng-model="form.szulev_igenybevett_szabadsag">
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('szul_elott_igenybevett_potszabadsag_gyermek')!==-1, error:error_elements['szul_elott_igenybevett_potszabadsag_gyermek']}">
        <div class="head">
          A szülés előtt gyermek(ek) száma, akik után pótszabadságot igényelt *
          <div class="error-hint" ng-if="error_elements.indexOf('szul_elott_igenybevett_potszabadsag_gyermek')!==-1">{{error_elements['szul_elott_igenybevett_potszabadsag_gyermek']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.szul_elott_igenybevett_potszabadsag_gyermek" ng-options="item for item in settings.potszabigyermek"></select>
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('gyerek16ev_fiatalabb_fogyatekos')!==-1, error:error_elements['gyerek16ev_fiatalabb_fogyatekos']}">
        <div class="head">
          Nevel-e 16 évesnél fiatalabb fogyatékosnak minősülő gyermeket? *
          <div class="error-hint" ng-if="error_elements.indexOf('gyerek16ev_fiatalabb_fogyatekos')!==-1">{{error_elements['gyerek16ev_fiatalabb_fogyatekos']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.gyerek16ev_fiatalabb_fogyatekos" ng-options="item for item in settings.select_yesno"></select>
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('szules_ideje')!==-1, error:error_elements['szules_ideje']}">
        <div class="head">
          Szülés időpontja *
          <div class="error-hint" ng-if="error_elements.indexOf('szules_ideje')!==-1">{{error_elements['szules_ideje']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="date" ng-model="form.szules_ideje" value="">
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('csed_kezdete')!==-1, error:error_elements['csed_kezdete']}">
        <div class="head">
          Szülési szabadság (CSED) kezdete *
          <div class="error-hint" ng-if="error_elements.indexOf('csed_kezdete')!==-1">{{error_elements['csed_kezdete']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="date" ng-model="form.csed_kezdete" value="">
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('gyedgyes_kezdete')!==-1, error:error_elements['gyedgyes_kezdete']}">
        <div class="head">
          Fizetés nélküli szabadság (GYED/GYES) kezdete *
          <div class="error-hint" ng-if="error_elements.indexOf('gyedgyes_kezdete')!==-1">{{error_elements['gyedgyes_kezdete']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="date" ng-model="form.gyedgyes_kezdete" value="">
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
      <table class="result-table">
        <tbody>
          <tr>
            <td class="h">Szülés évében járó szabadság</td>
            <td class="v">{{result.szules_eveben_szabadsag}}</td>
          </tr>
          <tr>
            <td class="h">Szülésig járó időarányos szabadság</td>
            <td class="v">{{result.szulesig_idoaranyos_szabadsag}}</td>
          </tr>
          <tr>
            <td class="h">Szülés évében ténylegesen igénybe vett szabadság</td>
            <td class="v">{{result.szules_eveben_igenybe_vett_szabadsag}}</td>
          </tr>
          <tr>
            <td class="h"><strong>Le nem töltött szabadság / túlvett (-) szabadság</strong></td>
            <td class="v"><strong>{{result.le_nem_toltott_szabadsag}}</strong></td>
          </tr>
          <tr>
            <td colspan="2" class="head">CSED</td>
          </tr>
          <tr>
            <td class="h">CSED idejére járó szabadság</td>
            <td class="v">{{result.csed_idejere_jaro_szabadsag}}</td>
          </tr>
          <tr>
            <td colspan="2" class="head">GYED</td>
          </tr>
          <tr>
            <td class="h">GYED idejére járó szabadság</td>
            <td class="v">{{result.gyedgyes_idejere_jaro_szabadsag}}</td>
          </tr>
          <tr>
            <td colspan="2" class="head">Összesített eredmény</td>
          </tr>
          <tr class="hl">
            <td class="h"><strong>Összes kivehető szabadság</strong></td>
            <td class="v"><strong>{{result.osszes_szabadsag}}</strong></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
