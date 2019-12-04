<div class="wrapper">
  <div class="inputs">
    <div class="header">Cafetéria adó</div>
    <div class="inp-body">
      <div class="line" ng-class="{missing:missing.indexOf('ceg_kiva')!==-1, error:error_elements['ceg_kiva']}">
        <div class="head">
          A cég KIVA alany? *
          <div class="error-hint" ng-if="error_elements.indexOf('ceg_kiva')!==-1">{{error_elements['ceg_kiva']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.ceg_kiva" ng-options="item for item in settings.select_yesno"></select>
          </div>
        </div>
      </div>
      <div class="line two-line" ng-class="{missing:missing.indexOf('juttatas')!==-1, error:error_elements['juttatas']}">
        <div class="head">
          Juttatás megnevezése *
          <div class="error-hint" ng-if="error_elements.indexOf('juttatas')!==-1">{{error_elements['juttatas']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper select-wrapper">
            <select class="" ng-model="form.juttatas" ng-options="item for item in settings.cafateria_jutattasok"></select>
          </div>
        </div>
      </div>
      <div class="line" ng-class="{missing:missing.indexOf('juttatas_osszege')!==-1, error:error_elements['juttatas_osszege']}">
        <div class="head">
          Juttatás összege az adott évre *
          <div class="error-hint" ng-if="error_elements.indexOf('juttatas_osszege')!==-1">{{error_elements['juttatas_osszege']}}</div>
        </div>
        <div class="val">
          <div class="inp-wrapper">
            <input type="text" ng-model="form.juttatas_osszege" input-thousand-separator="currency">
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
          <tr class="sm" ng-if="[2,3,4].indexOf(result.juttatas_group.ID)!==-1">
            <td class="h">Személyi jövedelemadó</td>
            <td class="v">{{result.szja|cash:'Ft':''}}</td>
          </tr>
          <tr class="sm" ng-if="[4].indexOf(result.juttatas_group.ID)!==-1">
            <td class="h">Természetbeni egészségbiztosítási járulék</td>
            <td class="v">{{result.termeszet_egeszseg_jarulek|cash:'Ft':''}}</td>
          </tr>
          <tr class="sm" ng-if="[4].indexOf(result.juttatas_group.ID)!==-1">
            <td class="h">Pénzbeli egészségbiztosítási járulék</td>
            <td class="v">{{result.penzbeli_egeszseg_jarulek|cash:'Ft':''}}</td>
          </tr>
          <tr class="sm" ng-if="[4].indexOf(result.juttatas_group.ID)!==-1">
            <td class="h">Nyugdíjjárulék</td>
            <td class="v">{{result.nyugdij_jarulek|cash:'Ft':''}}</td>
          </tr>
          <tr class="sm" ng-if="[4].indexOf(result.juttatas_group.ID)!==-1">
            <td class="h">Munkaerő piaci hozzájárulás</td>
            <td class="v">{{result.munkaeropiac_hozzajarulas|cash:'Ft':''}}</td>
          </tr>
          <tr class="hl" ng-if="[4].indexOf(result.juttatas_group.ID)!==-1">
            <td class="h">Összes munkavállaló járulék</td>
            <td class="v">{{result.munkavallalo_osszes_jarulek|cash:'Ft':''}}</td>
          </tr>
          <tr class="sm" ng-if="[2,3,4].indexOf(result.juttatas_group.ID)!==-1">
            <td class="h">Szociális hozzájárulási adó</td>
            <td class="v">{{result.szocho|cash:'Ft':''}}</td>
          </tr>
          <tr class="sm" ng-if="[4].indexOf(result.juttatas_group.ID)!==-1">
            <td class="h">Szakképzési hozzájárulás</td>
            <td class="v">{{result.szkh|cash:'Ft':''}}</td>
          </tr>
          <tr ng-if="[2,3,4].indexOf(result.juttatas_group.ID)!==-1">
            <td class="h">KIVA</td>
            <td class="v">{{result.kiva|cash:'Ft':''}}</td>
          </tr>
          <tr>
            <td colspan="2" class="head">
              Fizetendő adók megoszlása alanyonként
            </td>
          </tr>
          <tr class="hl" ng-if="[2,3,4].indexOf(result.juttatas_group.ID)!==-1">
            <td class="h"><strong>Munkavállalót terhelő adók</strong></td>
            <td class="v"><strong>{{result.ado_munkavallalo|cash:'Ft':''}}</strong></td>
          </tr>
          <tr class="hl" ng-if="[2,3,4].indexOf(result.juttatas_group.ID)!==-1">
            <td class="h"><strong>Munkáltatót terhelő adó</strong></td>
            <td class="v"><strong>{{result.ado_munkaltato|cash:'Ft':''}}</strong></td>
          </tr>
          <tr class="hl" ng-if="[1].indexOf(result.juttatas_group.ID)!==-1">
            <td class="h"><strong>Adófizetési kötelezettség</strong></td>
            <td class="v"><strong>Nincs</strong></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
