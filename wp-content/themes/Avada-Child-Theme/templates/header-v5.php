<?php
/**
 * Header-v5 template.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
<div class="fusion-header-sticky-height"></div>
<div class="fusion-sticky-header-wrapper"> <!-- start fusion sticky header wrapper -->
	<div class="fusion-header">
		<div class="site-buttons">
      <div class="site irisz">
        <a title="Írisz Office Zrt. - Adótervező és számviteli szolgáltató" href="//www.iriszoffice.hu" target="_blank"><div class="inside"><img retina_logo_url="<?=IMG?>/iriszoff-header-badge-x2.png" srcset="<?=IMG?>/iriszoff-header-badge.png 1x, <?=IMG?>/iriszoff-header-badge-x2.png 2x" alt="Írisz Office Zrt."></div></a>
      </div>
      <div class="site eco">
        <a title="EcoCreative - Vezetői döntéstámogató" href="//www.ecocreative.hu" target="_blank"><div class="inside"><img retina_logo_url="<?=IMG?>/ecologo_v2_x2.png" srcset="<?=IMG?>/ecologo_v2.png 1x, <?=IMG?>/ecologo_v2_x2.png 2x"  alt="EcoCreative - Vezetői döntéstámogató"></div></a>
      </div>
    </div>
		<div class="fusion-row">
			<?php if ( 'flyout' === Avada()->settings->get( 'mobile_menu_design' ) ) : ?>
				<div class="fusion-header-has-flyout-menu-content">
			<?php endif; ?>
			<?php avada_logo(); ?>
			<div class="searcher">
				<form class="" action="/" method="get">
					<div class="wrapper">
						<input type="text" name="s" placeholder="<?=__('Keresés','hc')?>" value="<?=$_GET['s']?>">
						<input type="submit" name="" value="Mehet">
					</div>
				</form>
			</div>
			<?php if ( 'flyout' === Avada()->settings->get( 'mobile_menu_design' ) ) : ?>
				<?php get_template_part( 'templates/menu-mobile-flyout' ); ?>
			<?php else : ?>
				<?php get_template_part( 'templates/menu-mobile-modern' ); ?>
			<?php endif; ?>

			<?php if ( 'flyout' === Avada()->settings->get( 'mobile_menu_design' ) ) : ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
