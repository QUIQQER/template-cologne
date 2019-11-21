/**
 * @module package/quiqqer/template-cologne/bin/javascript/controls/Menu/Categories
 *
 * This modul provides the functionality for slide out menu of product categories.
 *
 * @author www.pcsg.de (Michael Danielczok)
 */
define('package/quiqqer/template-cologne/bin/javascript/controls/Menu/Categories', [

    'qui/QUI',
    'qui/controls/Control',
    'package/quiqqer/menu/bin/SlideOut'

], function (QUI, QUIControl, SlideOut) {
    "use strict";

    return new Class({

        Extends: SlideOut,
        Type   : 'package/quiqqer/template-cologne/bin/javascript/controls/Menu/Categories',

        Binds: [
            '$onImport',
            '$onResize',
            '$openNextLevel',
            '$closeCurrentLevel',
            'resetMenu'
        ],

        options: {
            'menu-button': false,
            'menu-width' : 450
        },

        initialize: function (options) {
            this.parent(options);

            this.Wrapper        = null; // Control wrapper
            this.menuWidht      = null;
            this.animate        = false; // is the animation is still going on?
            this.FirstLevel     = null;
            this.openNextLevels = null; // button to open next menu level (depth)
            this.menuDepth      = 0; // how depth is menu opened

            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * event : on import
         */
        $onImport: function () {
            this.menuWidht = this.getAttribute('menu-width');

            this.parent();

            var Elm  = this.getElm(),
                self = this;

            this.Slideout.on('beforeopen', function () {
                Elm.setStyle('display', null);
            });

            var openButtons = document.getElements('.shop-category-menu-button');

            if (openButtons) {
                openButtons.each(function (Button) {
                    Button.addEvent('click', self.toggle);
                });
            }

            this.Slideout.on('close', this.resetMenu);

            // control wrapper
            this.Wrapper = Elm.getParent('.slideout-menu');

            // first level menu
            this.FirstLevel = Elm.getElement('.categories-menu-list-level-1');

            // next level menu button
            this.openNextLevels = Elm.getElements('.categories-menu-list-entry-next');

            if (this.openNextLevels.length !== 0) {
                this.openNextLevels.each(function (OpenNextLevelButton) {

                    if (this.animate) {
                        return;
                    }

                    OpenNextLevelButton.addEvent('click', this.$openNextLevel);
                }.bind(this));
            }

            // go back button
            this.goBackButtons = Elm.getElements('.categories-menu-list-entry-backButton');

            if (this.goBackButtons.length !== 0) {
                this.goBackButtons.each(function (GoBackButton) {

                    if (this.animate) {
                        return;
                    }

                    GoBackButton.addEvent('click', this.$closeCurrentLevel);
                }.bind(this));
            }
        },

        /**
         * event : on resize
         */
        $onResize: function () {
            if (QUI.getWindowSize().x > this.menuWidht) {
                this.setAttribute('menu-width', this.menuWidht);
                return;
            }

            this.setAttribute('menu-width', QUI.getWindowSize().x);
        },

        /**
         * Open next level menu (slide)
         *
         * @param Button
         */
        $openNextLevel: function (Button) {
            this.animate   = true;
            this.menuDepth = this.menuDepth + 1;

            var self      = this,
                NextLevel = Button.target.getParent().getElement('ul'),
                offset    = 'translateX(-' + this.menuDepth * 100 + '%)';

            NextLevel.setStyle('display', 'block');

            // required to animate the scroll bar
            moofx(this.FirstLevel).animate({
                height: NextLevel.getSize().y
            }, {
                duration: 500
            });

            // scroll menu to top if needed
            if (this.getElm().getPosition(this.Wrapper).y !== 0) {
                var myFx = new Fx.Scroll(this.Wrapper).toTop();
            }

            // slide to show next level
            moofx(this.FirstLevel).animate({
                transform: offset
            }, {
                duration: 500,
                equation: 'cubic-bezier(0.77, 0, 0.175, 1)',
                callback: function () {
                    self.animate = false;
                }
            });
        },

        /**
         * Close current level menu (slide)
         *
         * @param Button
         */
        $closeCurrentLevel: function (Button) {
            this.animate   = true;
            this.menuDepth = this.menuDepth - 1;

            if (this.menuDepth < 0) {
                this.menuDepth = 0;
            }

            var self         = this,
                CurrentLevel = Button.target.getParent('ul'),
                ParentLevel  = Button.target.getParent('ul').getParent('ul'),
                offset       = 'translateX(-' + this.menuDepth * 100 + '%)';

            // Workaround to get the original height of parent level menu
            //
            // cache current height of (invisible) parent menu
            var currentHeight = ParentLevel.getSize().y;
            // remove height...
            ParentLevel.setStyle('height', '');
            // ...to get height, that the parent menu need
            var originalHeight = ParentLevel.getSize().y;
            // restore the current height
            ParentLevel.setStyle('height', currentHeight);
            // end workaround

            // required to animate the scroll bar
            moofx(this.FirstLevel).animate({
                height: originalHeight
            }, {
                duration: 500
            });

            // slide to show previous level
            moofx(this.FirstLevel).animate({
                transform: offset
            }, {
                duration: 500,
                equation: 'cubic-bezier(0.77, 0, 0.175, 1)',
                callback: function () {
                    CurrentLevel.setStyle('display', '');
                    self.animate = false;
                }
            });
        },

        /**
         * Restore menu to the initial state
         */
        resetMenu: function () {
            this.menuDepth = 0;

            this.getElm().getElements('ul').each(function (Menu) {
                if (Menu.hasClass('categories-menu-list-level-1')) {
                    Menu.setStyles({
                        transform: 'translateX(0)',
                        height   : ''
                    });
                    return;
                }
                Menu.hide();
            });
        }
    });
});

