<div class="wrapper">
  <div class="inputs">
    <div class="header">Bruttó bér</div>
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

      <div class="line" ng-class="{missing:missing.indexOf('anyak_4vagytobbgyermek')!==-1, error:error_elements['anyak_4vagytobbgyermek']}">
        <div class="head">
          A munkavállaló négy vagy több gyermeket nevelő anyák kedvezményére jogosult
          <div class="error-hint" ng-if="error_elements.indexOf('anyak_4vagytobbgyermek')!==-1">{{error_elements['anyak_4vagytobbgyermek']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.anyak_4vagytobbgyermek" ng-options="item for item in settings.select_yesno"></select>
          </div>
        </div>
      </div>

      <div class="line" ng-class="{missing:missing.indexOf('csaladkedvezmenyre_jogosult')!==-1, error:error_elements['csaladkedvezmenyre_jogosult']}">
        <div class="head">
          Családi kedvezményre jogosult *
          <div class="error-hint" ng-if="error_elements.indexOf('csaladkedvezmenyre_jogosult')!==-1">{{error_elements['csaladkedvezmenyre_jogosult']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.csaladkedvezmenyre_jogosult" ng-options="item for item in settings.select_yesno"></select>
          </div>
        </div>
      </div>

      <div class="line" ng-if="form.csaladkedvezmenyre_jogosult=='Igen'" ng-class="{missing:missing.indexOf('csalad_eltartott_gyermek')!==-1, error:error_elements['csalad_eltartott_gyermek']}">
        <div class="head">
          - Eltartott gyermekek száma *
          <div class="error-hint" ng-if="error_elements.indexOf('csalad_eltartott_gyermek')!==-1">{{error_elements['csalad_eltartott_gyermek']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
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
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.csalad_eltartott_gyermek_kedvezmenyezett" ng-options="n for n in [] | range:0:6"></select>
          </div>
        </div>
      </div>

      <div class="line" ng-class="{missing:missing.indexOf('frisshazas_jogosult')!==-1, error:error_elements['frisshazas_jogosult']}">
        <div class="head">
          Friss házasok kedvezményre jogosult *
          <div class="error-hint" ng-if="error_elements.indexOf('frisshazas_jogosult')!==-1">{{error_elements['frisshazas_jogosult']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.frisshazas_jogosult" ng-options="item for item in settings.select_yesno"></select>
          </div>
        </div>
      </div>

      <div class="line" ng-class="{missing:missing.indexOf('szemelyikedvezmeny_jogosult')!==-1, error:error_elements['szemelyikedvezmeny_jogosult']}">
        <div class="head">
          Személyi kedvezményre jogosult *
          <div class="error-hint" ng-if="error_elements.indexOf('szemelyikedvezmeny_jogosult')!==-1">{{error_elements['szemelyikedvezmeny_jogosult']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.szemelyikedvezmeny_jogosult" ng-options="item for item in settings.select_yesno"></select>
          </div>
        </div>
      </div>

      <div class="line" ng-class="{missing:missing.indexOf('netto_ber')!==-1, error:error_elements['netto_ber']}">
        <div class="head">
          Nettó bér (Ft) *
          <div class="error-hint" ng-if="error_elements.indexOf('netto_ber')!==-1">{{error_elements['netto_ber']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" ng-model="form.netto_ber" input-thousand-separator="currency">
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
          <tr class="hl">
            <td class="h"><strong>Bruttó bér</strong></td>
            <td class="v"><strong>{{result.brutto_ber|cash:'Ft':''}}</td>
          </tr>
          <tr class="sm">
            <td class="h">- Személyi jövedelemadó</td>
            <td class="v">{{result.ado_szja|cash:'Ft':''}}</td>
          </tr>
          <tr class="sm" ng-if="result.vi < 3">
            <td class="h">- Természetbeni egészségbiztosítási járulék</td>
            <td class="v">{{result.ado_termeszetegeszseg|cash:'Ft':''}}</td>
          </tr>
          <tr class="sm" ng-if="result.vi < 3">
            <td class="h">- Pénzbeli egészségbiztosítási járulék</td>
            <td class="v">{{result.ado_penzbeli_egeszseg|cash:'Ft':''}}</td>
          </tr>
          <tr class="sm" ng-if="result.vi < 3">
            <td class="h">- Nyugdíjjárulék</td>
            <td class="v">{{result.ado_nyugdij|cash:'Ft':''}}</td>
          </tr>
          <tr class="sm" ng-if="result.vi < 3">
            <td class="h">- Munkaerő piaci hozzájárulás</td>
            <td class="v">{{result.ado_munkaerppiac|cash:'Ft':''}}</td>
          </tr>          
          <tr class="sm" ng-if="result.vi >= 3">
            <td class="h">- TB járulék</td>
            <td class="v">{{result.ado_tb|cash:'Ft':''}}</td>
          </tr>
          <tr>
            <td class="h"><strong>Összes levonás bruttó bérből</strong></td>
            <td class="v"><strong>-{{result.sum_minusbrutto|cash:'Ft':''}}</strong></td>
          </tr>
          <tr>
            <td class="h">Nettó munkabér</td>
            <td class="v">{{result.netto_ber|cash:'Ft':''}}</td>
          </tr>        
        </tbody>
      </table>
    </div>
  </div>
</div>
