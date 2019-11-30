<div class="wrapper">
  <div class="soon">
    <h3>Hamarosan elkészül ez a kalkulátor is!</h3>
    Nézzen vissza később.
  </div>
  <?php

  function find_target( $netto, $calc = array() )
  {
    $brutto = 0;
    $allcalc = 0;
    $kill = false;
    $step = 0;

    while ($allcalc == 0 || !$kill )
    {
      $step++;
      $eachcalc = 0;
      foreach ( (array)$calc as $c ) {
        $eachcalc += $brutto * ($c/100);
      }
      $allcalc = $eachcalc;

      if ($brutto == 0) {
        $brutto = 1;
      }

      //echo "step: ".$step." - brutto: ".$brutto." - netto: ".($brutto - $allcalc)." - levon: ".$allcalc."<br>";

      if ( (($brutto - $allcalc) * 2) >= $netto ) {
        $kill = true;
      }

      if (!$kill) {
        $brutto = $brutto * 2;
      }
    }

    $calced_net = ($brutto - $allcalc);

    while( $calced_net < $netto )
    {
      $brutto += 1;
      $eachcalc = 0;
      foreach ( (array)$calc as $c ) {
        $eachcalc += $brutto * ($c/100);
      }
      $allcalc = $eachcalc;
      $calced_net = $brutto - $allcalc;
    }

    return $brutto;
  }

  echo find_target( 295925, array(15, 4, 3, 10, 1.5) );
  ?>
</div>
