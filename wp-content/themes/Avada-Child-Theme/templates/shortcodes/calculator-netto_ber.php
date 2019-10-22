<div class="wrapper">
  <div class="inputs">

    <div class="line" ng-class="{missing:missing.indexOf('brutto_ber')!==-1, error:error_elements['brutto_ber']}">
      <div class="head">
        Bruttó havi bér (Ft) *
        <div class="error-hint" ng-if="error_elements.indexOf('brutto_ber')!==-1">{{error_elements['brutto_ber']}}</div>
      </div>
      <div class="val">
        <div class="inp-wrapper">
          <input type="number" min="0" ng-model="form.brutto_ber">
        </div>
      </div>
    </div>

    <div class="line" ng-class="{missing:missing.indexOf('csaladkedvezmenyre_jogosult')!==-1, error:error_elements['csaladkedvezmenyre_jogosult']}">
      <div class="head">
        Családi kedvezményre jogosult
        <div class="error-hint" ng-if="error_elements.indexOf('csaladkedvezmenyre_jogosult')!==-1">{{error_elements['csaladkedvezmenyre_jogosult']}}</div>
      </div>
      <div class="val">
        <div class="inp-wrapper">
          <select class="" ng-model="form.csaladkedvezmenyre_jogosult" ng-options="item for item in settings.select_yesno"></select>
        </div>
      </div>
    </div>

    <div class="line" ng-if="form.csaladkedvezmenyre_jogosult=='Igen'"  ng-class="{missing:missing.indexOf('csalad_eltartott_gyermek')!==-1, error:error_elements['csalad_eltartott_gyermek']}">
      <div class="head">
        - Eltartott gyermekek száma *
        <div class="error-hint" ng-if="error_elements.indexOf('csalad_eltartott_gyermek')!==-1">{{error_elements['csalad_eltartott_gyermek']}}</div>
      </div>
      <div class="val">
        <div class="inp-wrapper">
          <select class="" ng-model="form.csalad_eltartott_gyermek" ng-options="n for n in [] | range:1:6"></select>
        </div>
      </div>
    </div>

    <div class="line" ng-if="form.csaladkedvezmenyre_jogosult=='Igen'" ng-class="{missing:missing.indexOf('csalad_eltartott_gyermek_kedvezmenyezett')!==-1, error:error_elements['csalad_eltartott_gyermek_kedvezmenyezett']}">
      <div class="head">
        - Ebből kedvezményezett eltartottak száma *
        <div class="error-hint" ng-if="error_elements.indexOf('csalad_eltartott_gyermek_kedvezmenyezett')!==-1">{{error_elements['csalad_eltartott_gyermek_kedvezmenyezett']}}</div>
      </div>
      <div class="val">
        <div class="inp-wrapper">
          <select class="" ng-model="form.csalad_eltartott_gyermek_kedvezmenyezett" ng-options="n for n in [] | range:0:6"></select>
        </div>
      </div>
    </div>

    <div class="line" ng-class="{missing:missing.indexOf('frisshazas_jogosult')!==-1, error:error_elements['frisshazas_jogosult']}">
      <div class="head">
        Friss házasok kedvezményre jogosult
        <div class="error-hint" ng-if="error_elements.indexOf('frisshazas_jogosult')!==-1">{{error_elements['frisshazas_jogosult']}}</div>
      </div>
      <div class="val">
        <div class="inp-wrapper">
          <select class="" ng-model="form.frisshazas_jogosult" ng-options="item for item in settings.select_yesno"></select>
        </div>
      </div>
    </div>

    <div class="line" ng-class="{missing:missing.indexOf('szemelyikedvezmeny_jogosult')!==-1, error:error_elements['szemelyikedvezmeny_jogosult']}">
      <div class="head">
        Személyi kedvezményre jogosult
        <div class="error-hint" ng-if="error_elements.indexOf('szemelyikedvezmeny_jogosult')!==-1">{{error_elements['szemelyikedvezmeny_jogosult']}}</div>
      </div>
      <div class="val">
        <div class="inp-wrapper">
          <select class="" ng-model="form.szemelyikedvezmeny_jogosult" ng-options="item for item in settings.select_yesno"></select>
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
            <td class="h"><strong>Bruttó havi munkabér</strong></td>
            <td class="v"><strong>{{result.brutto_ber|cash:'Ft':''}}</strong></td>
          </tr>
          <tr class="sm">
            <td class="h">- Személyi jövedelemadó</td>
            <td class="v">{{result.ado_szja|cash:'Ft':''}}</td>
          </tr>
          <tr class="sm">
            <td class="h">- Természetbeni egészségbiztosítási járulék</td>
            <td class="v">{{result.ado_termeszetegeszseg|cash:'Ft':''}}</td>
          </tr>
          <tr class="sm">
            <td class="h">- Pénzbeli egészségbiztosítási járulék</td>
            <td class="v">{{result.ado_penzbeli_egeszseg|cash:'Ft':''}}</td>
          </tr class="sm">
          <tr>
            <td class="h">- Nyugdíjjárulék</td>
            <td class="v">{{result.ado_nyugdij|cash:'Ft':''}}</td>
          </tr>
          <tr class="sm">
            <td class="h">- Munkaerő piaci hozzájárulás</td>
            <td class="v">{{result.ado_munkaerppiac|cash:'Ft':''}}</td>
          </tr>
          <tr>
            <td class="h"><strong>Összes levonás bruttó bérből</strong></td>
            <td class="v"><strong>-{{result.sum_minusbrutto|cash:'Ft':''}}</strong></td>
          </tr>
          <tr class="hl">
            <td class="h"><strong>Nettó bér</strong></td>
            <td class="v"><strong>{{result.netto_ber|cash:'Ft':''}}</strong></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
