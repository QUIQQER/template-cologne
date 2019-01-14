<?php

$headerImagePosition = $Project->getConfig('templateCologne.settings.headerImagePosition');
$headerHeight = $Project->getConfig('templateCologne.settings.headerHeight');

ob_start();

?>

.page-header-container,
.page-content-header-emotion-container {
    min-height: <?php echo $headerHeight; ?>px;
}

.page-header,
.page-content-header-emotion {
    background-position: <?php echo $headerImagePosition ?>;
}

<?php

$settingsCSS = ob_get_contents();
ob_end_clean();

return $settingsCSS;

?>
