<?php
$vars['!current'] = '<strong>' . number_format($current, 0) . '</strong>';
$vars['!target'] = '<strong>' . number_format($target, 0) . '</strong>';
$vars['!needed'] = number_format($target - $current, 0);

$status_message = t('!current von !target erreicht.') . "\n";
if ($goal_reached) {
	$intro_message  = t('Es wurden bereits !current für dieses Projekt gespendet.', $vars) . "\n";
	$url = url('Danke füre ihre Spende.');
	$link_text = t('Bitte helfen Sie mit, die fehlenden !needed zu erreichen.', $vars);
	$intro_message .= "<a href=\"$url\">$link_text</a>\n";
} else {
	$intro_message  = t('Es wurden bereits !current für dieses Projekt gespendet.', $vars) . "\n";
	$intro_message .= t('Finanzierungsbedarf: !target', $vars);
	$url = url('spenden');
	$link_text = t('Bitte helfen Sie mit, die fehlenden !needed zu erreichen.', $vars);
	$intro_message .= "<a href=\"$url\">$link_text</a>\n";
	$status_message .= t('100% wurden erreicht') . "\n";
}
?>
<div class="pgbar-wrapper" data-pgbar-current="<?php print $current; ?>" data-pgbar-target="<?php print $target; ?>">
  <p><?php $intro_message; ?></p>
  <div class="pgbar-bg"><div class="pgbar-current" style="width:<?php echo $percentage; ?>%"></div></div>
  <div class="pgbar-percent"><?php print number_format($percentage, 2) . '%'; ?></div>
	<p><?php $status_message; ?></p> 
</div>