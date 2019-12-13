<div class="wrap">
  <h1><?=($editor)?'Webgalamb levél automatizálás szerkesztése':'Új webgalamb levél automatizálás'?></h1>
  <br>
  <form class="" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>" method="post">
    <input name='action' type="hidden" value='wgrss_add_automation'>
    <input type="hidden" name="return" value="<?php echo esc_html( admin_url( 'admin.php?page=wgrss-settings' ) ); ?>">
    <?php if (!$editor): ?>
      <input type="hidden" name="active" value="1">
    <?php else: ?>
      <input type="hidden" name="edit_id" value="<?=$edit['ID']?>">
    <?php endif; ?>
    <table class="form-table">
      <tbody>
        <tr>
          <th><label for="title"><?=__('Megnevezés','wgrss')?> *</label></th>
          <td>
            <input name="title" type="text" id="title" value="<?=($editor && $edit && isset($edit['title']))?$edit['title']:$_POST['title']?>" class="regular-text">
          </td>
        </tr>
        <tr>
          <th><label for="wg_group_id"><?=__('Webgalamb feliratkozó csoport ID','wgrss')?> *</label></th>
          <td>
            <input name="wg_group_id" type="number" id="wg_group_id" value="<?=($editor && $edit && isset($edit['wg_group_id']))?$edit['wg_group_id']:$_POST['wg_group_id']?>" class="regular-number">
          </td>
        </tr>
        <tr>
          <th><label for="wg_mail_id"><?=__('Webgalamb levél ID','wgrss')?> *</label></th>
          <td>
            <input name="wg_mail_id" type="number" id="wg_mail_id" value="<?=($editor && $edit && isset($edit['wg_mail_id']))?$edit['wg_mail_id']:$_POST['wg_mail_id']?>" class="regular-number">
          </td>
        </tr>
        <tr>
          <th>
            <label for="cats"><?=__('Bejegyzés típus szűkítés','wgrss')?></label><br>
            <em style="font-weight: 300;">Válassza ki, hogy melyik bejegyzés típusnál szeretné kiküldetni a bejegyzéseket Webgalambon keresztül. Ha nincs kiválasztva, akkor csak a sima standard bejegyzésekre reagál a kiküldő!</em>
          </th>
          <td>
            <div class="overf-checkbox-list">
              <div class="wrapper">
                <?php foreach ((array)$posttypes as $pt): ?>
                  <div class="cat">
                    <label for="pt<?=$pt->name?>"><input type="checkbox" <?=(in_array($pt->name, (array)$edit['post_types']))?'checked="checked"':''?> name="post_types[]" id="pt<?=$pt->name?>" value="<?=$pt->name?>"> <?=$pt->label?></label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="clearfix"></div>
          </td>
        </tr>
        <tr>
          <th>
            <label for="cats"><?=__('Kategória szűkítés','wgrss')?></label><br>
            <em style="font-weight: 300;">Az itt kiválasztott kategóriákba létrehozott cikkek esetén történik meg az automatikus levélkiküldés. Ha nincs kiválasztva, akkor bármely cikk létrehozásánál.</em>
          </th>
          <td>
            <div class="overf-checkbox-list">
              <div class="wrapper">
                <?php foreach ((array)$categories as $cgroup => $group): ?>
                  <div class="group-title"><?php echo $group['name']; ?> - kategóriák:</div>
                  <?php foreach ((array)$group['items'] as $c): ?>
                    <div class="cat">
                      <label for="ct<?=$c->term_id?>"><input type="checkbox" <?=(in_array($c->term_id, (array)$edit['cats']))?'checked="checked"':''?> name="cats[]" id="ct<?=$c->term_id?>" value="<?=$c->term_id?>"> <?=$c->name?></label>
                    </div>
                  <?php endforeach; ?>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="clearfix"></div>
          </td>
        </tr>
        <?php if ($editor): ?>
        <tr>
          <th><label for="active"><?=__('Állapot','wgrss')?> *</label></th>
          <td>
            <select class="" id="active" name="active">
              <option value="0" <?=($editor && $edit && isset($edit['active']) && $edit['active'] == '01')?'selected="selected"':''?>>Inaktív</option>
              <option value="1" <?=($editor && $edit && isset($edit['active']) && $edit['active'] == '1')?'selected="selected"':''?>>Aktív</option>
            </select>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <h3>Mező-érték párosítás</h3>
    <p>A Webgalamb levélben szerkesztett mezők (feliratkozási mezők) párosítása a Wordpress értékekkel. A feliratkozási mezőket előre kell definiálni a Webgalamb szoftverben!</p>
    <table class="form-table">
      <tbody>
        <tr>
          <th><label for="mez_post_title"><?=__('Cikk címe','wgrss')?></label></th>
          <td><input name="mezoxref[post_title]" type="text" id="mez_post_title" value="<?=($editor && $edit && isset($edit['mezok']['post_title']))?$edit['mezok']['post_title']:$_POST['mezok']['post_title']?>" class="regular-text"></td>
        </tr>
        <tr>
          <th><label for="mez_post_excerpt"><?=__('Cikk kivonata','wgrss')?></label></th>
          <td><input name="mezoxref[post_excerpt]" type="text" id="mez_post_excerpt" value="<?=($editor && $edit && isset($edit['mezok']['post_excerpt']))?$edit['mezok']['post_excerpt']:$_POST['mezok']['post_excerpt']?>" class="regular-text"></td>
        </tr>
        <tr>
          <th><label for="mez_post_date"><?=__('Cikk közzététele','wgrss')?></label></th>
          <td><input name="mezoxref[post_date]" type="text" id="mez_post_date" value="<?=($editor && $edit && isset($edit['mezok']['post_date']))?$edit['mezok']['post_date']:$_POST['mezok']['post_date']?>" class="regular-text"></td>
        </tr>
        <tr>
          <th><label for="mez_permalink"><?=__('Cikk URL','wgrss')?></label></th>
          <td><input name="mezoxref[permalink]" type="text" id="mez_permalink" value="<?=($editor && $edit && isset($edit['mezok']['permalink']))?$edit['mezok']['permalink']:$_POST['mezok']['permalink']?>" class="regular-text"></td>
        </tr>
      </tbody>
    </table>
    <?php wp_nonce_field( 'wgrss-settings-adder', 'wgrss-settings-adder' ); ?>
    <?php submit_button(($editor)?'':'Új automatizálás hozzáadása'); ?>
  </form>
</div>
<style media="screen">
  .overf-checkbox-list{
    background: white;
    float: left;
    /*max-height: 20px;*/
    overflow: auto;
    height: 250px;
  }
  .overf-checkbox-list .cat label{
    padding: 10px 12px;
    display: block;
  }
  .overf-checkbox-list .cat label:hover{
    background: #0073aa;
    color: white;
  }
  .overf-checkbox-list .group-title{
    padding: 5px;
    font-size: 0.7rem;
    text-align: center;
    font-style: italic;
    background: #e8e8e8;
  }
</style>
