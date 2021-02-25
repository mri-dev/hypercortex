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

<br><br>
A kapcsolat üzenet <strong><?php echo date('Y-m-d H:i:s'); ?></strong> időponttal érkezett!
