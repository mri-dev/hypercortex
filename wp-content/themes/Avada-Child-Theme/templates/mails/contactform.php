<style media="screen">
  table tr td { padding: 10px; }
  table, td { border: 1px solid #d7d7d7; }
  td { vertical-align: top; }
  table tr td:first-child { width: 60%; }
  table tr:nth-child(even) td { background: #f3f3f3; }
</style>
<h2>Új kapcsolat üzenet érkezett a <u><?php echo get_bloginfo('name'); ?></u> oldalról!</h2>
<h3>Kapcsolat adatok</h3>
<table border="0" style="border: none;">
  <tr>
    <td>Cég neve:</td>
    <td><strong><?php echo $form['cegnev']; ?></strong></td>
  </tr>
  <tr>
    <td>Kapcsolattartó neve:</td>
    <td><strong><?php echo $form['contact_name']; ?></strong></td>
  </tr>
  <tr>
    <td>Kapcsolattartó telefonszáma:</td>
    <td><strong><?php echo $form['contact_phone']; ?></strong></td>
  </tr>
  <tr>
    <td>Kapcsolattartó e-mail címe:</td>
    <td><strong><?php echo $form['contact_email']; ?></td>
  </tr>
  <tr>
    <td colspan="2">
      Üzenet: <br>
      <strong><?php echo nl2br($form['megjegyzes']); ?></strong>
    </td>
  </tr>
</table>
<br>
<h3>Beküldött űrlap változói</h3>
<table border="0" style="border: none;">
  <tr>
    <td>Mennyi a munkavállalók átlagos létszáma?</td>
    <td><strong><?php echo $form['munkavallalo_letszam']; ?></strong></td>
  </tr>
  <?php if ($form['munkavallalo_letszam']<100 && !empty($form['munkavallalo_letszam'])): ?>
  <tr>
    <td>Várható-e, hogy a közeljövőben meghaladja a létszám a 100 főt?</td>
    <td><strong><?php echo $form['munkavallalo_meghalad100']; ?></strong></td>
  </tr>
  <?php endif; ?>
  <tr>
    <td>Vannak-e alkalmi munkavállalók a cégben?</td>
    <td><strong><?php echo $form['almalmi_munkavallalok']; ?></strong></td>
  </tr>
  <tr>
    <td>Vannak-e megbízási jogviszonyban foglalkoztatott személyek?</td>
    <td><strong><?php echo $form['megbizasi_jogviszonyu_szemelyek']; ?></strong></td>
  </tr>
  <tr>
    <td>Előfordul-e béren kívüli juttatás, cafetéria, reprezentáció?</td>
    <td><strong><?php echo $form['berenkivuli_juttatas']; ?></td>
  </tr>
  <tr>
    <td>Vannak-e Önöknél speciális foglalkoztatási módozatok, pl. munkaidőkeret?</td>
    <td><strong><?php echo $form['specialis_foglalkoztatasi_modozatok']; ?></td>
  </tr>
  <tr>
    <td>Előfordul-e kiküldetés a cégnél?</td>
    <td><strong><?php echo $form['kikuldetes']; ?></td>
  </tr>
</table>

<h4>Szükség lenne az alábbi feladatok elvégzésére?</h4>
<table border="0" style="border: none;">
  <tr>
    <td>- kapcsolatfelvétel az új munkavállalóval, bekérni a szerződéshez és egyéb dokumentációhoz szükséges személyes adatokat</td>
    <td><strong><?php echo $form['feladat_kapcsolatfelvetel']; ?></strong></td>
  </tr>
  <tr>
    <td>- belépő / kilépő dolgozók jogviszony változásának bejelentése a NAV felé, illetve a szükséges aláírandó nyilatkozatok, dokumentumok elkészítése</td>
    <td><strong><?php echo $form['feladat_nav_bejelentes']; ?></strong></td>
  </tr>
  <tr>
    <td>- hóközi számfejtés készítése</td>
    <td><strong><?php echo $form['feladat_hokozi_szamfejtes']; ?></strong></td>
  </tr>
  <tr>
    <td>- feladás előkészítése a könyvelés számára</td>
    <td><strong><?php echo $form['feladat_konyveles_feladas']; ?></td>
  </tr>
  <tr>
    <td>- év elején a személyi jövedelemadóval, társadalombiztosítással kapcsolatos nyilatkozatok bekérése a munkavállalóktól és év végén az M30-as igazolások elkészítése a munkavállalók részére</td>
    <td><strong><?php echo $form['feladat_eveleji_szja_beker']; ?></td>
  </tr>
  <tr>
    <td>- jövedelemigazolások készítése</td>
    <td><strong><?php echo $form['feladat_jovedelemigazolas']; ?></td>
  </tr>
  <tr>
    <td>- munkaszerződések elkészítése, karbantartása, módosítások elkészítése</td>
    <td><strong><?php echo $form['feladat_munkaszerzodes']; ?></td>
  </tr>
  <tr>
    <td>- KSH felé adatszolgáltatás</td>
    <td><strong><?php echo $form['feladat_ksh_adatszolgaltatas']; ?></td>
  </tr>
</table>

<br>
<table border="0" style="border: none;">
  <tr>
    <td>Használnak-e olyan integrált rendszert, melynek része a munkaidő nyilvántartás / bérszámfejtő modul?</td>
    <td><strong><?php echo $form['integralt_rendszer_hasznalat']; ?></strong></td>
  </tr>
  <?php if (!empty($form['integralt_rendszer_hasznalat']) && $form['integralt_rendszer_hasznalat'] == 'igen'): ?>
  <tr>
    <td>Melyik programot használják?</td>
    <td><strong><?php echo $form['integralt_rendszer']; ?></strong></td>
  </tr>
  <tr>
    <td>Továbbra is szeretnék azt használni?</td>
    <td><strong><?php echo $form['integralt_rendszer_hasznalat_jovoben']; ?></strong></td>
  </tr>
  <tr>
    <td>Tudnak majd biztosítani hozzáférést, hogy mi is tudjunk benne dolgozni?</td>
    <td><strong><?php echo $form['integralt_rendszer_hasznalat_hozzaferes']; ?></td>
  </tr>
  <?php endif; ?>
  <tr>
    <td>Általában mikor történik Önöknél a bérek kifizetése?</td>
    <td><strong><?php echo $form['berkifizetes_datum']; ?></td>
  </tr>
</table>

<br><br>
A kapcsolat üzenet <strong><?php echo date('Y-m-d H:i:s'); ?></strong> időponttal érkezett!
