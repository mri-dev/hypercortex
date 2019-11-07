<a name="_form"></a>
<div class="contact-form" ng-app="Hypercortex" ng-controller="ContactForm" ng-init="">
  <div class="wrapper">
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl">
          <div class="h"><label for="cegnev"><?=__('Cégnév')?></label></div>
          <div class="inp"><input type="text" id="cegnev" class="form-control" value="" ng-model="form.cegnev"></div>
        </div>
      </div>
    </div>
    <div class="divider"></div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="munkavallalo_letszam"><?=__('Mennyi a munkavállalók átlagos létszáma?')?></label></div>
          <div class="inp" style="flex-basis:150px;"><input type="number" min="0" step="1" id="munkavallalo_letszam" class="form-control" value="" ng-model="form.munkavallalo_letszam"></div>
        </div>
      </div>
    </div>
    <div class="line" ng-if="(form.munkavallalo_letszam && form.munkavallalo_letszam<100)">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="munkavallalo_meghalad100"><?=__('Várható-e, hogy a közeljövőben meghaladja a létszám a 100 főt?')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" class="yes" ng-model="form.munkavallalo_meghalad100" name="munkavallalo_meghalad100" value="igen" id="munkavallalo_meghalad100_igen"><label for="munkavallalo_meghalad100_igen"><?=__('igen', 'hc')?></label></div>
              <div class=""><input type="radio" class="no" ng-model="form.munkavallalo_meghalad100" name="munkavallalo_meghalad100" value="nem" id="munkavallalo_meghalad100_nem"><label for="munkavallalo_meghalad100_nem"><?=__('nem', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="almalmi_munkavallalok"><?=__('Vannak-e alkalmi munkavállalók a cégben?')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.almalmi_munkavallalok" name="almalmi_munkavallalok" value="gyakran" id="almalmi_munkavallalok_gyakran"><label for="almalmi_munkavallalok_gyakran"><?=__('gyakran', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.almalmi_munkavallalok" name="almalmi_munkavallalok" value="ritkán" id="almalmi_munkavallalok_ritkán"><label for="almalmi_munkavallalok_ritkán"><?=__('ritkán', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.almalmi_munkavallalok" name="almalmi_munkavallalok" value="nincsen" id="almalmi_munkavallalok_nincsen"><label for="almalmi_munkavallalok_nincsen"><?=__('nincsen', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="megbizasi_jogviszonyu_szemelyek"><?=__('Vannak-e megbízási jogviszonyban foglalkoztatott személyek?')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.megbizasi_jogviszonyu_szemelyek" name="megbizasi_jogviszonyu_szemelyek" value="gyakran" id="megbizasi_jogviszonyu_szemelyek_gyakran"><label for="megbizasi_jogviszonyu_szemelyek_gyakran"><?=__('gyakran', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.megbizasi_jogviszonyu_szemelyek" name="megbizasi_jogviszonyu_szemelyek" value="ritkán" id="megbizasi_jogviszonyu_szemelyek_ritkán"><label for="megbizasi_jogviszonyu_szemelyek_ritkán"><?=__('ritkán', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.megbizasi_jogviszonyu_szemelyek" name="megbizasi_jogviszonyu_szemelyek" value="nincsen" id="megbizasi_jogviszonyu_szemelyek_nincsen"><label for="megbizasi_jogviszonyu_szemelyek_nincsen"><?=__('nincsen', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="berenkivuli_juttatas"><?=__('Előfordul-e béren kívüli juttatás, cafetéria, reprezentáció?')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.berenkivuli_juttatas" name="berenkivuli_juttatas" value="igen" id="berenkivuli_juttatas_igen"><label for="berenkivuli_juttatas_igen"><?=__('igen', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.berenkivuli_juttatas" name="berenkivuli_juttatas" value="nem" id="berenkivuli_juttatas_nem"><label for="berenkivuli_juttatas_nem"><?=__('nem', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="specialis_foglalkoztatasi_modozatok"><?=__('Vannak-e Önöknél speciális foglalkoztatási módozatok, pl. munkaidőkeret?')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.specialis_foglalkoztatasi_modozatok" name="specialis_foglalkoztatasi_modozatok" value="igen" id="specialis_foglalkoztatasi_modozatok_igen"><label for="specialis_foglalkoztatasi_modozatok_igen"><?=__('igen', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.specialis_foglalkoztatasi_modozatok" name="specialis_foglalkoztatasi_modozatok" value="nem" id="specialis_foglalkoztatasi_modozatok_nem"><label for="specialis_foglalkoztatasi_modozatok_nem"><?=__('nem', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="kikuldetes"><?=__('Előfordul-e kiküldetés a cégnél?')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.kikuldetes" name="kikuldetes" value="igen" id="kikuldetes_igen"><label for="kikuldetes_igen"><?=__('igen', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.kikuldetes" name="kikuldetes" value="nem" id="kikuldetes_nem"><label for="kikuldetes_nem"><?=__('nem', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="divider-text"><?=__('Szükség lenne az alábbi feladatok elvégzésére?', 'hc')?></div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="feladat_kapcsolatfelvetel">- <?=__('kapcsolatfelvétel az új munkavállalóval,  bekérni a szerződéshez és egyéb dokumentációhoz szükséges személyes adatokat')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.feladat_kapcsolatfelvetel" name="feladat_kapcsolatfelvetel" value="igen" id="feladat_kapcsolatfelvetel_igen"><label for="feladat_kapcsolatfelvetel_igen"><?=__('igen', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.feladat_kapcsolatfelvetel" name="feladat_kapcsolatfelvetel" value="nem" id="feladat_kapcsolatfelvetel_nem"><label for="feladat_kapcsolatfelvetel_nem"><?=__('nem', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="feladat_nav_bejelentes">- <?=__('belépő / kilépő dolgozók jogviszony változásának bejelentése a NAV felé, illetve a szükséges aláírandó nyilatkozatok, dokumentumok elkészítése')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.feladat_nav_bejelentes" name="feladat_nav_bejelentes" value="igen" id="feladat_nav_bejelentes_igen"><label for="feladat_nav_bejelentes_igen"><?=__('igen', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.feladat_nav_bejelentes" name="feladat_nav_bejelentes" value="nem" id="feladat_nav_bejelentes_nem"><label for="feladat_nav_bejelentes_nem"><?=__('nem', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="feladat_hokozi_szamfejtes">- <?=__('hóközi számfejtés készítése')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.feladat_hokozi_szamfejtes" name="feladat_hokozi_szamfejtes" value="igen" id="feladat_hokozi_szamfejtes_igen"><label for="feladat_hokozi_szamfejtes_igen"><?=__('igen', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.feladat_hokozi_szamfejtes" name="feladat_hokozi_szamfejtes" value="nem" id="feladat_hokozi_szamfejtes_nem"><label for="feladat_hokozi_szamfejtes_nem"><?=__('nem', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="feladat_konyveles_feladas">- <?=__('feladás előkészítése a könyvelés számára')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.feladat_konyveles_feladas" name="feladat_konyveles_feladas" value="igen" id="feladat_konyveles_feladas_igen"><label for="feladat_konyveles_feladas_igen"><?=__('igen', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.feladat_konyveles_feladas" name="feladat_konyveles_feladas" value="nem" id="feladat_konyveles_feladas_nem"><label for="feladat_konyveles_feladas_nem"><?=__('nem', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="feladat_eveleji_szja_beker">- <?=__('év elején a személyi jövedelemadóval, társadalombiztosítással kapcsolatos nyilatkozatok bekérése a munkavállalóktól és év végén az M30-as igazolások elkészítése a munkavállalók részére')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.feladat_eveleji_szja_beker" name="feladat_eveleji_szja_beker" value="igen" id="feladat_eveleji_szja_beker_igen"><label for="feladat_eveleji_szja_beker_igen"><?=__('igen', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.feladat_eveleji_szja_beker" name="feladat_eveleji_szja_beker" value="nem" id="feladat_eveleji_szja_beker_nem"><label for="feladat_eveleji_szja_beker_nem"><?=__('nem', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="feladat_jovedelemigazolas">- <?=__('jövedelemigazolások készítése')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.feladat_jovedelemigazolas" name="feladat_jovedelemigazolas" value="igen" id="feladat_jovedelemigazolas_igen"><label for="feladat_jovedelemigazolas_igen"><?=__('igen', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.feladat_jovedelemigazolas" name="feladat_jovedelemigazolas" value="nem" id="feladat_jovedelemigazolas_nem"><label for="feladat_jovedelemigazolas_nem"><?=__('nem', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="feladat_munkaszerzodes">- <?=__('munkaszerződések elkészítése, karbantartása, módosítások elkészítése')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.feladat_munkaszerzodes" name="feladat_munkaszerzodes" value="igen" id="feladat_munkaszerzodes_igen"><label for="feladat_munkaszerzodes_igen"><?=__('igen', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.feladat_munkaszerzodes" name="feladat_munkaszerzodes" value="nem" id="feladat_munkaszerzodes_nem"><label for="feladat_munkaszerzodes_nem"><?=__('nem', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="feladat_ksh_adatszolgaltatas">- <?=__('KSH felé adatszolgáltatás')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.feladat_ksh_adatszolgaltatas" name="feladat_ksh_adatszolgaltatas" value="igen" id="feladat_ksh_adatszolgaltatas_igen"><label for="feladat_ksh_adatszolgaltatas_igen"><?=__('igen', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.feladat_ksh_adatszolgaltatas" name="feladat_ksh_adatszolgaltatas" value="nem" id="feladat_ksh_adatszolgaltatas_nem"><label for="feladat_ksh_adatszolgaltatas_nem"><?=__('nem', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="divider"></div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="integralt_rendszer_hasznalat"><?=__('Használnak-e olyan integrált rendszert, melynek része a munkaidő nyilvántartás / bérszámfejtő modul?')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.integralt_rendszer_hasznalat" name="integralt_rendszer_hasznalat" value="igen" id="integralt_rendszer_hasznalat_igen"><label for="integralt_rendszer_hasznalat_igen"><?=__('igen', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.integralt_rendszer_hasznalat" name="integralt_rendszer_hasznalat" value="nem" id="integralt_rendszer_hasznalat_nem"><label for="integralt_rendszer_hasznalat_nem"><?=__('nem', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="line" ng-if="form.integralt_rendszer_hasznalat=='igen'">
      <div class="form-input-holder">
        <div class="flextbl">
          <div class="h"><label for="integralt_rendszer"><?=__('Melyik programot használják?')?></label></div>
          <div class="inp"><input type="text" ng-model="form.integralt_rendszer" id="integralt_rendszer" class="form-control" value=""></div>
        </div>
      </div>
    </div>
    <div class="line" ng-if="form.integralt_rendszer_hasznalat=='igen'">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="integralt_rendszer_hasznalat_jovoben"><?=__('Továbbra is szeretnék azt használni?')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.integralt_rendszer_hasznalat_jovoben" name="integralt_rendszer_hasznalat_jovoben" value="igen" id="integralt_rendszer_hasznalat_jovoben_igen"><label for="integralt_rendszer_hasznalat_jovoben_igen"><?=__('igen, nem szeretnénk váltani', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.integralt_rendszer_hasznalat_jovoben" name="integralt_rendszer_hasznalat_jovoben" value="nem" id="integralt_rendszer_hasznalat_jovoben_nem"><label for="integralt_rendszer_hasznalat_jovoben_nem"><?=__('nem, váltani szeretnénk', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.integralt_rendszer_hasznalat_jovoben" name="integralt_rendszer_hasznalat_jovoben" value="nemf" id="integralt_rendszer_hasznalat_jovoben_nemf"><label for="integralt_rendszer_hasznalat_jovoben_nemf"><?=__('nem feltétlen ragaszkodunk a programhoz', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="line" ng-if="form.integralt_rendszer_hasznalat=='igen'">
      <div class="form-input-holder">
        <div class="flextbl auto-width">
          <div class="h"><label for="integralt_rendszer_hasznalat_hozzaferes"><?=__('Tudnak majd biztosítani hozzáférést, hogy mi is tudjunk benne dolgozni?')?></label></div>
          <div class="inp" style="flex-basis:150px;">
            <div class="radio-selectors">
              <div class=""><input type="radio" ng-model="form.integralt_rendszer_hasznalat_hozzaferes" name="integralt_rendszer_hasznalat_hozzaferes" value="igen" id="integralt_rendszer_hasznalat_hozzaferes_igen"><label for="integralt_rendszer_hasznalat_hozzaferes_igen"><?=__('igen', 'hc')?></label></div>
              <div class=""><input type="radio" ng-model="form.integralt_rendszer_hasznalat_hozzaferes" name="integralt_rendszer_hasznalat_hozzaferes" value="nem" id="integralt_rendszer_hasznalat_hozzaferes_nem"><label for="integralt_rendszer_hasznalat_hozzaferes_nem"><?=__('nem', 'hc')?></label></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="divider"></div>
    <div class="line">
      <div class="form-input-holder">
        <label for="berkifizetes_datum"><?=__('Általában mikor történik Önöknél a bérek kifizetése?')?></label>
        <input type="text" ng-model="form.berkifizetes_datum" id="berkifizetes_datum" class="form-control" value="">
      </div>
    </div>
    <div class="divider"></div>
    <div class="line">
      <div class="form-input-holder">
        <label for="megjegyzes"><?=__('Megjegyzés')?></label>
        <textarea class="form-control" id="megjegyzes" ng-model="form.megjegyzes"></textarea>
      </div>
    </div>
    <div class="divider"></div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl">
          <div class="h"><label for="contact_name"><?=__('Kapcsolattartó neve')?></label></div>
          <div class="inp"><input type="text" id="contact_name" ng-model="form.contact_name" class="form-control" value=""></div>
        </div>
      </div>
    </div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl">
          <div class="h"><label for="contact_phone"><?=__('Kapcsolattartó telefonszáma')?></label></div>
          <div class="inp"><input type="text" id="contact_phone" ng-model="form.contact_phone" class="form-control" value=""></div>
        </div>
      </div>
    </div>
    <div class="line">
      <div class="form-input-holder">
        <div class="flextbl">
          <div class="h"><label for="contact_email"><?=__('Kapcsolattartó e-mail címe')?></label></div>
          <div class="inp"><input type="text" id="contact_email" ng-model="form.contact_email" class="form-control" value=""></div>
        </div>
      </div>
    </div>
    <div class="divider"></div>
    <div class="line">
      <div class="accepts">
        <div class=""><input type="checkbox" name="adatvedelem" ng-model="form.cb_adatvedelem" id="adatvedelem" value="1"> <label for="adatvedelem">* Az üzenet elküldésével elfogadom az <a href="/adavedelmi-nyilatkozat/" target="_blank">Adatvédelmi Nyilatkozatot</a> és hozzájárulok az adataim kezeléséhez.</label></div>
      </div>
    </div>

    <div class="btns">
      <div class="error-msg" ng-if="error">{{error}}</div>
      <div class="loader" ng-if="loading" style="text-align:center;"><?=__('Üzenet küldése folyamatban...', 'hc')?></div>
      <button type="button" ng-if="!loading" class="{{button_class}}" ng-click="send()">{{button_text}}</button>
    </div>
  </div>
</div>
