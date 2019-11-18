Template Cologne
======

First eCommerce template based on QUIQQER system.

Package name:

    quiqqer/template-cologne
    
Features
--------
- Responsive
- Integrated QUIQQER breadcrumb
- Page transition effects
- FontAwesome support
- (...)

Installation
------------

The package name is: quiqqer/template-businesspro


Contribute
----------

- Issue Tracker: https://dev.quiqqer.com/quiqqer/template-cologne/issues
- Source Code: https://dev.quiqqer.com/quiqqer/template-cologne


Support
-------

If you have found a bug or want to make improvements,
then you can write an e-mail to support@pcsg.de.

For developers
--------------

### Available template events
For mor information go to
 [QUIQQER Wiki - Template events](https://dev.quiqqer.com/quiqqer/quiqqer/wikis/design_standard#template-events).


```html
<!-- body -->
{template_event name="quiqqer::template-cologne::header::begin" Template=$Template}
{template_event name="quiqqer::template-cologne::header::afterCSSStyles" Template=$Template}
{template_event name="quiqqer::template-cologne::header::afterSettingsCSS" Template=$Template}
{template_event name="quiqqer::template-cologne::header::end" Template=$Template}

<!-- body -->
{template_event name="quiqqer::template-cologne::body::begin" Template=$Template}
{template_event name="quiqqer::template-cologne::body::end" Template=$Template}
```
