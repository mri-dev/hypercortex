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

    <?php wp_nonce_field( 'wgrss-settings-adder', 'wgrss-settings-adder' ); ?>
    <?php submit_button(($editor)?'':'Új automatizálás hozzáadása'); ?>
  </form>
</div>
