<?php
/**
 * Archives template.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
global $wp_query;
$posttype = $wp_query->query_vars['post_type'];
$posttype = (!$posttype) ? 'post' : $posttype;
$total_post = $wp_query->post_count;
?>
<?php get_header(); ?>
<section id="content" <?php Avada()->layout->add_class( 'content_class' ); ?> <?php Avada()->layout->add_style( 'content_style' ); ?>>
  <div class="bloglist-holder view-of-archive">
		<?php if (is_tag()): ?>
			<h1 class="post-title"><?php echo ucfirst(strtolower(trim(single_cat_title('', false)))); ?></h1>
			<div class="post-title-info">
				<?php echo sprintf(__('Ehhez a témakörhöz <strong>%d db</strong>. cikket tudunk ajánlani.', 'hc'), $total_post); ?>
			</div>
		<?php else: ?>
			<h1 class="post-title"><?php echo single_cat_title(); ?></h1>
		<?php endif; ?>
		<?php if ( category_description() ) : ?>
			<div id="post-<?php the_ID(); ?>" <?php post_class( 'fusion-archive-description' ); ?>>
				<div class="post-content">
					<?php echo category_description(); ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="wrapper">
	    <?php $i = 0; while(have_posts()): ?>
	      <?php
				$i++;
	      the_post();
	      $pid = get_the_ID();
				$img = get_the_post_thumbnail_url(get_the_ID());
				$img = (empty($img)) ? IMG.'/no-image.png': $img;
				$ts = get_the_tags($pid);
	      ?>
		    <div class="cikk">
		      <div class="wrapper">
		        <div class="image autocorrett-height-by-width" data-image-ratio="4:3"><a href="<?php echo the_permalink(); ?>"><img src="<?=$img?>" alt="<? echo the_title(); ?>" title="<? echo the_title(); ?>"></a></div>
		        <div class="content">
							<?php if ($ts): ?>
								<?php $tsx = ''; foreach ((array)$ts as $t): $tsx .= '<a href="/tag/'.$t->slug.'">'.ucfirst($t->name).'</a>, '; endforeach; $tsx = rtrim($tsx, ', '); ?>
								<div class="author"><?php echo $tsx; ?></div>
							<?php else: ?>
								<div class="author">&nbsp;</div>
							<?php endif; ?>
		          <div class="title"><a href="<?php echo the_permalink(); ?>"><?php echo the_title(); ?></a></div>
		          <div class="desc"><?php echo the_excerpt(); ?></div>
		          <div class="published"><?php echo get_the_date(); ?></div>
		          <div class="nextbtn">
		            <a href="<?php echo the_permalink(); ?>"><?=__('Tovább')?></a>
		          </div>
		        </div>
		      </div>
		    </div>
	    <?php endwhile; wp_reset_postdata(); ?>
			<?php if (!have_posts()): ?>
				<div class="no-posts">
			    <?php echo __('Nincs jelenleg megjeleníthető cikk.'); ?>
			  </div>
			<?php endif; ?>
		</div>
  </div>
  <div class="pagination">
    <?php echo the_posts_pagination(); ?>
  </div>
</section>
<?php do_action( 'avada_after_content' ); ?>
<div class="clr clear fusion-clear"></div>
<div class="tag-filter">
	<?php
		$tags = get_tags(array(
			'hide_empty' => false
		));
	?>
	<h1><?=__('Témakörök', 'hc')?></h1>
	<div class="wrapper">
		<div class="tags">
			<?php foreach ((array)$tags as $tag): ?>
			<div class="tag<?=($wp_query && $wp_query->query['tag'] == $tag->slug)?' active':''?>">
				<div class="wrap">
					<a href="/tag/<?=$tag->slug?>"><?=($tag->name)?></a>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<?php
get_footer();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
