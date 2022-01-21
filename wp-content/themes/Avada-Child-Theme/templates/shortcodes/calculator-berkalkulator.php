<div class="wrapper">
  <div class="inputs">
    <div class="header">Bérkalkulátor</div>
    <div class="inp-body">

      <div class="version-changer">
        <div class="wrapper">
          <div class="" ng-repeat="ver in settings.versions">
            <div class="wrap" ng-if="['2020/2', '2020/1', '2019'].indexOf(ver) === -1">
              <input type="radio" id="ver_v{{ver}}" ng-value="ver" ng-model="form.version"> <label title="Számolás {{ver}}. évi jogszabályok alapján." for="ver_v{{ver}}">{{ver}}</label>
            </div>
          </div>
        </div>
      </div>

      <div class="line line-switcher" ng-class="{missing:missing.indexOf('jovedelem')!==-1, error:error_elements['jovedelem']}">
        <div class="head">
          Mit szeretne kiszámolni?
          <div class="error-hint" ng-if="error_elements.indexOf('jovedelem')!==-1">{{error_elements['jovedelem']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <div class="radio-switch">
              <input type="radio" ng-model="form.mode" value="netto" id="netto"> <label for="netto">Nettó bért</label>
              <input type="radio" ng-model="form.mode" value="brutto" id="brutto"> <label for="brutto">Bruttó bért</label>
              <input type="radio" ng-model="form.mode" value="teljes" id="teljes"> <label for="teljes">Teljes bérköltséget</label>
            </div>            
          </div>
        </div>
      </div>

      <div ng-if="form.mode" class="line" ng-class="{missing:missing.indexOf('jovedelem')!==-1, error:error_elements['jovedelem']}">
        <div class="head">
         Rendszeres havi <span ng-if="(form.mode=='brutto')">nettó</span><span ng-if="(form.mode=='netto' || form.mode=='teljes')">bruttó</span> jövedelem (Ft) *
          <div class="error-hint" ng-if="error_elements.indexOf('jovedelem')!==-1">{{error_elements['jovedelem']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" ng-model="form.jovedelem" input-thousand-separator="currency">
          </div>
        </div>
      </div>

      <div ng-if="form.mode && form.mode == 'teljes'" class="line" ng-class="{missing:missing.indexOf('ceg_kisvallalati_ado_alany')!==-1, error:error_elements['ceg_kisvallalati_ado_alany']}">
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

      <div ng-if="form.mode && form.mode == 'teljes'" class="line two-line" ng-class="{missing:missing.indexOf('munkavallalo_kedvezmeny')!==-1, error:error_elements['munkavallalo_kedvezmeny']}">
        <div class="head">
          A munkavállaló után a vállalkozás jogosult kedvezményre? *
          <div class="error-hint" ng-if="error_elements.indexOf('munkavallalo_kedvezmeny')!==-1">{{error_elements['munkavallalo_kedvezmeny']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.munkavallalo_kedvezmeny">
              <?php foreach ((array)$settings['forms']['munkavallalo_kedvezmenyek'] as $i => $kedv): ?>
              <option value="<?=$i?>" ng-selected="<?=$i?>==0"><?=$kedv['title']?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div ng-if="form.mode" class="line" ng-class="{missing:missing.indexOf('anyak_4vagytobbgyermek')!==-1, error:error_elements['anyak_4vagytobbgyermek']}">
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

      <div ng-if="form.mode && ['2022'].indexOf(form.version) !== -1" class="line" ng-class="{missing:missing.indexOf('kor25ev_alatti')!==-1, error:error_elements['kor25ev_alatti']}">
        <div class="head">
          A munkavállaló 25 év alatti?
          <div class="error-hint" ng-if="error_elements.indexOf('kor25ev_alatti')!==-1">{{error_elements['kor25ev_alatti']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.kor25ev_alatti" ng-options="item for item in settings.select_yesno"></select>
          </div>
        </div>
      </div>

      <div ng-if="form.mode" class="line" ng-class="{missing:missing.indexOf('oregsegi_nyugdijas')!==-1, error:error_elements['oregsegi_nyugdijas']}">
        <div class="head">
          Öregségi nyugdíjas
          <div class="error-hint" ng-if="error_elements.indexOf('oregsegi_nyugdijas')!==-1">{{error_elements['oregsegi_nyugdijas']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.oregsegi_nyugdijas" ng-options="item for item in settings.select_yesno"></select>
          </div>
        </div>
      </div>

      <div ng-if="form.mode" class="line" ng-class="{missing:missing.indexOf('csaladkedvezmenyre_jogosult')!==-1, error:error_elements['csaladkedvezmenyre_jogosult']}">
        <div class="head">
          Családi kedvezményre jogosult
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

      <div ng-if="form.mode" class="line" ng-class="{missing:missing.indexOf('frisshazas_jogosult')!==-1, error:error_elements['frisshazas_jogosult']}">
        <div class="head">
          Friss házasok kedvezményre jogosult
          <div class="error-hint" ng-if="error_elements.indexOf('frisshazas_jogosult')!==-1">{{error_elements['frisshazas_jogosult']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.frisshazas_jogosult" ng-options="item for item in settings.select_yesno"></select>
          </div>
        </div>
      </div>

      <div ng-if="form.mode" class="line" ng-class="{missing:missing.indexOf('szemelyikedvezmeny_jogosult')!==-1, error:error_elements['szemelyikedvezmeny_jogosult']}">
        <div class="head">
          Személyi kedvezményre jogosult
          <div class="error-hint" ng-if="error_elements.indexOf('szemelyikedvezmeny_jogosult')!==-1">{{error_elements['szemelyikedvezmeny_jogosult']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.szemelyikedvezmeny_jogosult" ng-options="item for item in settings.select_yesno"></select>
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
          <tr ng-class="{hl: (result.mode=='brutto')}">
            <td class="h"><strong>Bruttó havi munkabér</strong></td>
            <td class="v"><strong>{{result.brutto_ber|cash:'Ft':''}}</strong></td>
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
            <td class="v"><strong>{{result.sum_minusbrutto|cash:'Ft':''}}</strong></td>
          </tr>
          <tr ng-class="{hl: (result.mode=='netto')}">
            <td class="h"><strong>Nettó bér</strong></td>
            <td class="v"><strong>{{result.netto_ber|cash:'Ft':''}}</td>
          </tr>
          <tr ng-if="(result.mode=='teljes')">
            <td colspan="2" class="head">
              Cég által fizetendő költségek
            </td>
          </tr>
          <tr ng-if="(result.values.kiva_adoalany=='Nem' && result.mode=='teljes')" ng-class="{lt: result.values.kiva_adoalany=='Igen'}">
            <td class="h">Fizetendő szociális hozzájárulási adó</td>
            <td class="v">{{result.ado_szocialis_hozzajarulas|cash:'Ft':''}}</td>
          </tr>
          <tr ng-if="(result.values.kiva_adoalany=='Nem' && result.mode=='teljes' && ['2022'].indexOf(result.version) === -1)" ng-class="{lt: result.values.kiva_adoalany=='Igen'}">
            <td class="h">Fizetendő szakképzési hozzájárulás</td>
            <td class="v">{{result.ado_szakkepzesi_hozzajarulas|cash:'Ft':''}}</td>
          </tr>
          <tr ng-if="(result.values.kiva_adoalany=='Igen' && result.mode=='teljes')" ng-class="{lt: result.values.kiva_adoalany=='Nem'}">
            <td class="h">Fizetendő kisvállalati adó</td>
            <td class="v">{{result.ado_kisvallalati|cash:'Ft':''}}</td>
          </tr>
          <tr ng-if="(result.values.kiva_adoalany=='Nem' && result.mode=='teljes')" ng-class="{hl: result.values.kiva_adoalany=='Nem'}">
            <td class="h"><strong>Teljes bérköltség nem KIVA alany cég esetén</strong></td>
            <td class="v">{{result.berkoltseg_nem_KIVA|cash:'Ft':''}}</td>
          </tr>
          <tr ng-if="(result.values.kiva_adoalany=='Igen' && result.mode=='teljes')" ng-class="{hl: result.values.kiva_adoalany=='Igen'}">
            <td class="h"><strong>Teljes bérköltség KIVA alany cég esetén</strong></td>
            <td class="v">{{result.berkoltseg_KIVA|cash:'Ft':''}}</td>
          </tr>
          <tr class="hlh" ng-if="(result.mode=='teljes')">
            <td class="h"><strong>NAV felé utalandó összes adó és járulék</strong></td>
            <td class="v">{{result.nav_osszes_ado|cash:'Ft':''}}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
