<?php
/**
 * @file
 * Default template for progressbars.
 */

$vars['!current'] = '<strong>' . number_format($current, 0) . '</strong>';
$vars['!target'] = '<strong>' . number_format($target, 0) . '</strong>';
$vars['!needed'] = number_format($target - $current, 0);

$intro_message  = t('We need !target signatures.', $vars);
$status_message = ($goal_reached ? t("We've reached our goal!") : t('Already !current of !target signed the petition!')) . "\n";
?>
<div class="pgbar-wrapper" data-pgbar-current="<?php print $current; ?>" data-pgbar-target="<?php print $target; ?>">
  <p><?php print $intro_message; ?></p>
  <div class="pgbar-bg"><div class="pgbar-current" style="width:<?php echo $percentage; ?>%"></div></div>
  <div class="pgbar-percent"><?php print number_format($percentage, 2) . '%'; ?></div>
	<p><?php print $status_message; ?></p> 
</div>
