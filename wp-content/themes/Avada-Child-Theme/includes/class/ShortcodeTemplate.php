<?php
class ShortcodeTemplates
{
  public $template_root = '/templates/shortcodes/';
  public $template = null;

  public function __construct($template)
  {
    $this->template_root = get_stylesheet_directory() . $this->template_root;
    $this->template = $template;

    return $this;
  }

  public function load_template( $params = array() )
  {
    $template_file = $this->template_root.$this->template.'.php';

    if( !$this->template || !file_exists($template_file))
    {
      return '(!) A template fájl nem elérhető: '.$template_file;
    }

    if(is_object($params))
    {
      $params = (array)$params;
    }

    if($params) extract($params);

    ob_start();
    include($template_file);
    $temp = ob_get_contents();
    ob_end_clean();

    return $temp;
  }
}

?>
