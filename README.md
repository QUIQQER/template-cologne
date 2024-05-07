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
- Scroll function for A-elements
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

If you wont to test confirmation mail template you will finde some 
 information in [QUIQQER Order wiki](https://dev.quiqqer.com/quiqqer/order/wikis/Home/Send-order-confirmation-mail-console-tool). 

### Smooth scroll to element by click
You can give each html element an extra CSS class to smoothly scroll to the target element. All you need is to 
give at least `scrollToLink` class. For more control, consider the following parameters: 
- `data-qui-target="#myElement"` - _[optional]_ every valid css selector
- `data-qui-offset="60"` - _[optional]_ scroll offset

Example:

```html
<!-- Top of the page -->
<p>
 <a class="btn btn-primary btn-large" href="#Contact" data-qui-offset="120">
  Get in touch <span class="fa fa-long-arrow-right"></span>
 </a>
</p>
<p>
 <button class="btn btn-secondary btn-large" data-qui-target=".myContactForm" data-qui-offset="100">
  Get in touch one more time <span class="fa fa-long-arrow-right"></span>
 </button>
</p>


<!-- ... -->

<!-- Contact form at the bottom -->
<h2 id="Contact" class="myContactForm">Send us a message</h2>
<form>
 <!-- ... -->
</form>
```

### Available template events
For more information go to
 [QUIQQER Wiki - Template events](https://dev.quiqqer.com/quiqqer/core/wikis/design_standard#template-events).


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
