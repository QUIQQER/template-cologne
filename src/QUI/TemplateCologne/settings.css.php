<?php
/**
 * cologne template file
 *
 * @var QUI\Projects\Project $Project
 * @var QUI\Projects\Site $Site
 * @var QUI\Interfaces\Template\EngineInterface $Engine
 * @var QUI\Template $Template
 **/

$menuBreakPoint = $menuBreakPoint ?? 767;
$largeSpacing = $Site->getAttribute('templateCologne.largeSpacing');
$headerImagePosition = $Project->getConfig('templateCologne.settings.headerImagePosition');
$headerHeight = $Project->getConfig('templateCologne.settings.headerHeight');
$logoHeight = $Project->getConfig('templateCologne.settings.logoHeight');

ob_start();

?>
:root {
<?php if ($logoHeight) { ?>
    --qui-nav-height: <?php echo $logoHeight; ?>px;
<?php }; ?>
}

@media screen and (max-width: <?php echo $menuBreakPoint; ?>px) {
    .hide-desktop-menu-breakpoint {
        display: none;
    }
}

<?php if ($largeSpacing) { ?>
/* more spacing */
.page-content-header,
.content-body,
.content-template,
.control-template,
.template-grid-row,
.template-grid-row {
    margin-bottom: 5em;
    margin-top: 5em;
}
<?php }; ?>

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
