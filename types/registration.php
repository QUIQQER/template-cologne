<?php

/**
 * Registration Sign Up Site Type
 */

use QUI\Utils\Security\Orthos;

$Site->setAttribute('nocache', 1);

// Determine what happens if the user is already logged in
if (QUI::getUserBySession()->getId()) {
    try {
        $FrontendUsersHandler = QUI\FrontendUsers\Handler::getInstance();
        $registrationSettings = $FrontendUsersHandler->getRegistrationSettings();

        if ($registrationSettings['visitRegistrationSiteBehaviour'] === 'showProfile') {
            $ProfileSite = $FrontendUsersHandler->getProfileSite($Site->getProject());

            if ($ProfileSite) {
                header('Location: ' . $ProfileSite->getUrlRewritten());
                exit;
            }
        }

    } catch (QUI\Exception $Exception) {
        QUI\System\Log::writeDebugException($Exception);
    }
}

$siteContent = false;

if ($Site->getAttribute('quiqqer.settings.registration.contentPos') == 'nextToRegistrationForm' && $Site->getAttribute('content')) {
    $siteContent = $Site->getAttribute('content');
}

/**
 * Registration / Sign up
 */
$Registration = new QUI\FrontendUsers\Controls\RegistrationSignUp([
    'content' => $siteContent
]);

$Engine->assign([
    'Registration' => $Registration,
]);
