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

The package name is: quiqqer/template-cologne


Contribute
----------

- Project: https://dev.quiqqer.com/quiqqer/template-cologne
- Issue Tracker: https://dev.quiqqer.com/quiqqer/template-cologne/issues
- Source Code: https://dev.quiqqer.com/quiqqer/template-cologne/tree/master
- Wiki: https://dev.quiqqer.com/quiqqer/template-cologne/wikis/home


Support
-------

If you have found a bug or want to make improvements,
then you can write an e-mail to support@pcsg.de.

For developers
--------------

### Testing of order confirmation mail template 

https://dev.quiqqer.com/quiqqer/order/wikis/Home/Send-order-confirmation-mail-console-tool


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
{template_event name="quiqqer::template-cologne::body::afterTopBar" Template=$Template}
{template_event name="quiqqer::template-cologne::body::afterMainMenu" Template=$Template}
{template_event name="quiqqer::template-cologne::body::beforePage" Template=$Template}
{template_event name="quiqqer::template-cologne::body::afterHeader" Template=$Template}
{template_event name="quiqqer::template-cologne::body::afterBreadcrumb" Template=$Template}
{template_event name="quiqqer::template-cologne::body::beforeMain" Template=$Template}
{template_event name="quiqqer::template-cologne::body::afterMain" Template=$Template}
{template_event name="quiqqer::template-cologne::body::beforeFooter" Template=$Template}
{template_event name="quiqqer::template-cologne::body::afterFooter" Template=$Template}
{template_event name="quiqqer::template-cologne::body::end" Template=$Template}
```
