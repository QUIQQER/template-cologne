/**
 * Shared header/category-menu basket navigation, loaded and resolved on click.
 */
define('package/quiqqer/template-cologne/bin/javascript/BasketNavigation', [
    'qui/QUI',
    'Ajax',
    'Locale'
], function(QUI, QUIAjax, QUILocale) {
    'use strict';

    let pending = null;

    const getNavigationUrl = function(mobile) {
        if (pending) {
            return pending;
        }

        pending = new Promise(function(resolve, reject) {
            QUIAjax.get('package_quiqqer_template-cologne_ajax_getNavigationUrl', resolve, {
                'package': 'quiqqer/template-cologne',
                mobile: mobile ? '1' : '0',
                showError: false,
                onError: reject
            });
        }).then(function(url) {
            if (typeof url !== 'string' || !url) {
                throw new Error('No basket destination available.');
            }

            return url;
        }).catch(function() {
            return QUI.getMessageHandler().then(function(Messages) {
                Messages.addAttention(QUILocale.get('quiqqer/template-cologne', 'basket.navigation.unavailable'));
                return null;
            });
        }).finally(function() {
            pending = null;
        });

        return pending;
    };

    return {
        getNavigationUrl: getNavigationUrl
    };
});
