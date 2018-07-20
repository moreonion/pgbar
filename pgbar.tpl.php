<?php
/**
 * @file
 * Default template for progressbars.
 */

$vars['!current'] = '<strong>' . $format_fn($current, 0) . '</strong>';
$vars['!current-animated'] = '<strong class="pgbar-counter">' . $format_fn($current, 0) . '</strong>';
$vars['!target'] = '<strong>' . $format_fn($target, 0) . '</strong>';
$vars['!needed'] = $format_fn($target - $current, 0);

$intro_message  = format_string($goal_reached ? $texts['full_intro_message'] : $texts['intro_message'], $vars);
$status_message = format_string($goal_reached ? $texts['full_status_message'] : $texts['status_message'], $vars) . "\n";
?>
<div id="<?php print $html_id; ?>" class="pgbar-wrapper" data-pgbar-current="<?php print $current; ?>" data-pgbar-target="<?php print $target; ?>">
  <p><?php print $intro_message; ?></p>
  <div class="pgbar-bg"><div class="pgbar-current" style="width:<?php echo $percentage; ?>%"></div></div>
  <div class="pgbar-percent"><?php print $format_fn($percentage, 2) . '%'; ?></div>
	<p><?php print $status_message; ?></p> 
</div>
