<div class="wrap">
  <h1 class="wp-heading-inline">Webgalamb Automailer</h1>
  <a class="page-title-action" href="<?=admin_url('admin.php?page=wgrss-settings-adder')?>"><?php echo __('Új automatizálás', 'wgrss'); ?></a>
  <hr class="wp-header-end">
  <br>
  <table class="wp-list-table widefat fixed striped">
    <thead>
      <tr>
        <th width="25">#</th>
        <th>Megnevezés</th>
        <th>Kategória cikkei</th>
        <th width="100">WG csoport ID</th>
        <th width="100">WG levél ID</th>
        <th width="100">Státusz</th>
        <th width="120">Létrehozva</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ((array)$list as $l): ?>
      <tr>
        <td><?=$l['ID']?></td>
        <td><a href="<?=admin_url('admin.php?page=wgrss-settings-adder&edit='.$l['ID'])?>"><strong><?=$l['title']?></strong></a></td>
        <td>
          <?php
          if (!$l['cats']) {
            echo '<em>Minden kategória cikkei esetén.</em>';
          } else {
            $incats = '';
            foreach ((array)$l['cats_obj'] as $c) {
              $incats .= $c->name.', ';
            }
            $incats = rtrim($incats, ', ');
            echo $incats;
          }
          ?>
        </td>
        <td class="center"><?=$l['wg_group_id']?></td>
        <td class="center"><?=$l['wg_mail_id']?></td>
        <td class="center"><?=($l['active'] == 1)?'<strong style="color:#3dad3d;">Aktív<strong>':'<strong style="color:#eb4c4c;">Inaktív</strong>'?></td>
        <td><?=$l['created_at']?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <br>
  <h2>Webgalamb kapcsolat beállítások</h2>
  A webgalamb szoftvernek azon a szerveren kell telepítve lennie, melyen a jelenlegi weboldal is található!
  <?php if ($this->wg->wgdberror): ?>
  <div class="notice error"><p>Wegbalamb adatbázis csatlakozási hiba: <strong><?=$this->wg->wgdberror?></strong></p></div>
  <?php endif; ?>
  <form class="" action="options.php" method="post">
    <table class="form-table">
      <tbody>
        <tr>
          <th><label for="wb_db_user">Adatbázis felh.név</label></th>
          <td>
            <input type="text" name="<?=WGRSS_DB_PREFIX.'wg_db_username'?>" value="<?=get_option(WGRSS_DB_PREFIX.'wg_db_username','')?>" class="regular-text">
          </td>
        </tr>
        <tr>
          <th><label for="wb_db_user">Adatbázis neve</label></th>
          <td>
            <input type="text" name="<?=WGRSS_DB_PREFIX.'wg_db_name'?>" value="<?=get_option(WGRSS_DB_PREFIX.'wg_db_name','')?>" class="regular-text">
          </td>
        </tr>
        <tr>
          <th><label for="wb_db_user">Adatbázis felh. jelszó</label></th>
          <td>
            <input type="text" name="<?=WGRSS_DB_PREFIX.'wg_db_pw'?>" value="<?=get_option(WGRSS_DB_PREFIX.'wg_db_pw','')?>" class="regular-text">
          </td>
        </tr>
        <tr>
          <th><label for="wb_db_user">Adatbázis tábla prefix</label></th>
          <td>
            <input type="text" name="<?=WGRSS_DB_PREFIX.'wg_db_prefix'?>" value="<?=get_option(WGRSS_DB_PREFIX.'wg_db_prefix','')?>" class="regular-text">
          </td>
        </tr>
      </tbody>
    </table>
    <?php settings_fields( 'wgrss-settings' ); ?>
    <?php submit_button('Webgalamb beállítások mentése'); ?>
  </form>
  <br><br>
  <div class="" style="font-size: 0.7rem; text-align: right;">
    Fejlesztette: <a style="text-decoration:none;" href="mailto:info@web-pro.hu">WEBPRO Solutions Bt.</a>
  </div>
</div>
