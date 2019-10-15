<?php
class Setup_General_Settings {
  function Setup_General_Settings( ) {
      add_filter( 'admin_init' , array( &$this , 'register_fields' ) );
  }
  function register_fields() {
    register_setting( 'general', 'phone', 'esc_attr' );
    add_settings_field('phone', '<label for="phone">'.__('Kapcsolat telefonszám' , 'phone' ).'</label>' , array(&$this, 'phone_cb') , 'general' );

    register_setting( 'general', 'address', 'esc_attr' );
    add_settings_field('address', '<label for="address">'.__('Cím' , 'address' ).'</label>' , array(&$this, 'address_cb') , 'general' );

    register_setting( 'general', 'busojaras_ideje', 'esc_attr' );
    add_settings_field('busojaras_ideje', '<label for="busojaras_ideje">'.__('Busójárás következő időpontja' , 'buso' ).'</label>' , array(&$this, 'busojaras_ideje_cb') , 'general' );

    register_setting( 'general', 'busojaras_title', 'esc_attr' );
    add_settings_field('busojaras_title', '<label for="busojaras_title">'.__('Busójárás kiemelt oldal cím' , 'buso' ).'</label>' , array(&$this, 'busojaras_title_cb') , 'general' );

    register_setting( 'general', 'highlight_page_id', 'esc_attr' );
    add_settings_field('highlight_page_id', '<label for="highlight_page_id">'.__('Főoldal kiemelt oldal' , 'buso' ).'</label>' , array(&$this, 'highlight_page_id_cb') , 'general' );
  }

  function highlight_page_id_cb()
  {
    $value = get_option( 'highlight_page_id', '' );
    $pages = get_pages();
    $select = '<select id="highlight_page_id" name="highlight_page_id">';
    $select .= '<option>--</option>';
    foreach ($pages as $p) {
      $select .= '<option value="'.$p->ID.'" '. ( ($value == $p->ID) ? 'selected="selected"':'' ) .'>'.$p->post_title.'</option>';
    }
    $select .= '</select>';

    echo $select;
  }

  function busojaras_ideje_cb() {
    $value = get_option( 'busojaras_ideje', '' );
    echo '<input class="regular-text" type="text" id="busojaras_ideje" name="busojaras_ideje" value="' . $value . '" />';
  }

  function busojaras_title_cb() {
    $value = get_option( 'busojaras_title', '' );
    echo '<input class="regular-text" type="text" id="busojaras_title" name="busojaras_title" value="' . $value . '" />';
  }

  function phone_cb() {
      $value = get_option( 'phone', '' );
      echo '<input class="regular-text" type="text" id="phone" name="phone" value="' . $value . '" />';
  }
  function address_cb() {
      $value = get_option( 'address', '' );
      echo '<input class="regular-text" type="text" id="address" name="address" value="' . $value . '" />';
  }
}

?>
