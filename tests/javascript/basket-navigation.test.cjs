const assert = require('node:assert/strict');
const {readFileSync} = require('node:fs');
const path = require('node:path');
const {test} = require('node:test');
const vm = require('node:vm');
const source = readFileSync(path.join(__dirname, '../../bin/javascript/BasketNavigation.js'), 'utf8');

function fixture() {
    const requests = [];
    const messages = [];
    let navigation;
    const dependencies = {
        'qui/QUI': {getMessageHandler: () => Promise.resolve({addAttention: text => messages.push(text)})},
        Ajax: {get: (name, resolve, options) => requests.push({name, resolve, options})},
        Locale: {get: (group, key) => key}
    };
    vm.runInNewContext(source, {
        define: (name, names, factory) => { navigation = factory(...names.map(name => dependencies[name])); }
    });
    return {navigation, requests, messages};
}

function buttonFixture(button, globals = {}) {
    const navigationModule = 'package/quiqqer/template-cologne/bin/javascript/BasketNavigation';
    const loads = [];
    const deferred = [];
    let options;
    let definition;
    const dependencies = {
        'qui/QUI': {setAttribute() {}, getWindowSize: () => ({x: 1024})},
        'qui/utils/System': {iOSversion: () => false},
        'package/quiqqer/order/bin/frontend/controls/basket/Button': function(attributes) {
            options = attributes;
            this.inject = () => {};
        }
    };
    const context = {
        ...globals,
        MENU_BREAK_POINT: 768,
        window: {
            addEvent: (name, callback) => callback(),
            matchMedia: () => ({matches: false}),
            location: {hash: ''}
        },
        document: {
            body: {hasClass: () => false},
            getElements: () => null,
            getElement: selector => selector === '.cologne-header-control-basket' ? {} : null,
            querySelector: () => null,
            addEventListener() {}
        },
        Class: function(attributes) { return attributes; },
        define: (name, names, factory) => {
            loads.push(...names);
            definition = factory(...names.map(name => dependencies[name] ?? {}));
        },
        require: (names, resolve, reject) => {
            loads.push(...names);
            if (names.includes(navigationModule)) {
                deferred.push({resolve, reject});
                return;
            }
            resolve(...names.map(name => dependencies[name]));
        }
    };
    const filename = button === 'header' ? 'init.js' : 'controls/Menu/Categories.js';
    vm.runInNewContext(readFileSync(path.join(__dirname, '../../bin/javascript', filename), 'utf8'), context);
    if (button === 'categories') definition.createBasketButton();
    return {options, loads, deferred, navigationModule};
}

for (const button of ['header', 'categories']) {
    test(`${button} initializes default actions without loading navigation`, () => {
        const f = buttonFixture(button);
        assert.equal(f.options.action, 'openSmallBasket');
        assert.equal(f.options.mobileAction, 'openShoppingCart');
        assert.equal(f.loads.includes(f.navigationModule), false);
    });

    test(`${button} initializes explicit actions without loading navigation`, () => {
        const f = buttonFixture(button, {
            BASKET_ACTION: 'openShoppingCart', BASKET_MOBILE_ACTION: 'openOrderProcessUrl'
        });
        assert.equal(f.options.action, 'openShoppingCart');
        assert.equal(f.options.mobileAction, 'openOrderProcessUrl');
        assert.equal(f.loads.includes(f.navigationModule), false);
    });

    for (const mobile of [false, true]) {
        test(`${button} loads navigation on demand and forwards mobile=${mobile}`, async () => {
            const f = buttonFixture(button);
            const navigation = fixture();
            const result = f.options.getNavigationUrl(mobile);
            assert.equal(f.deferred.length, 1);
            assert.equal(navigation.requests.length, 0);
            f.deferred[0].resolve(navigation.navigation);
            await Promise.resolve();
            assert.equal(navigation.requests.length, 1);
            assert.equal(navigation.requests[0].options.mobile, mobile ? '1' : '0');
            navigation.requests[0].resolve('/cart');
            assert.equal(await result, '/cart');
        });
    }

    test(`${button} propagates module loading failures and permits retry`, async () => {
        const f = buttonFixture(button);
        const result = f.options.getNavigationUrl(true);
        f.deferred[0].reject(new Error('module unavailable'));
        await assert.rejects(result, /module unavailable/);
        const retry = f.options.getNavigationUrl(true);
        f.deferred[1].resolve({getNavigationUrl: () => Promise.resolve('/cart')});
        assert.equal(await retry, '/cart');
    });
}

test('parallel clicks share a request and return the resolved destination', async () => {
    const f = fixture();
    const first = f.navigation.getNavigationUrl(true);
    const second = f.navigation.getNavigationUrl(true);
    assert.equal(first, second);
    assert.equal(f.requests.length, 1);
    assert.equal(f.requests[0].options.mobile, '1');
    f.requests[0].resolve('/cart');
    assert.equal(await first, '/cart');
    assert.deepEqual(f.messages, []);
});

for (const failure of ['missing destination', 'network error']) {
    test(`${failure} shows one message, resolves without navigation and allows retry`, async () => {
        const f = fixture();
        const first = f.navigation.getNavigationUrl(false);
        const second = f.navigation.getNavigationUrl(false);
        const request = f.requests[0];
        assert.equal(request.options.showError, false);
        if (failure === 'network error') request.options.onError(new Error('offline'));
        else request.resolve(null);
        assert.deepEqual(await Promise.all([first, second]), [null, null]);
        assert.deepEqual(f.messages, ['basket.navigation.unavailable']);
        const retry = f.navigation.getNavigationUrl(false);
        assert.equal(f.requests.length, 2);
        f.requests[1].resolve('/order');
        assert.equal(await retry, '/order');
    });
}

for (const target of ['button', 'icon', 'container']) {
    test(`category basket ${target} click performs exactly one action`, () => {
        const categorySource = readFileSync(path.join(__dirname, '../../bin/javascript/controls/Menu/Categories.js'), 'utf8');
        let definition, container, actions = 0, clicking = false;
        const slideoutEvents = {};
        const icon = {};
        const button = {
            contains: target => target === button || target === icon,
            click() {
                if (clicking) return;
                clicking = true;
                actions++;
                container.click({target: button});
                clicking = false;
            }
        };
        const basket = {mayBeDisplayed: () => true, getElm: () => button, inject() {}};
        const dependencies = {Locale: {get: () => 'Warenkorb'}};
        vm.runInNewContext(categorySource, {
            document: {getElements: () => null},
            Class: function(options) { return options; },
            Element: function(tag, options) {
                container = {title: options.title, inject() {}, getSize: () => ({y: 50}),
                    addEventListener: (name, callback) => { container[name] = callback; }};
                return container;
            },
            define: (name, names, factory) => { definition = factory(...names.map(name => dependencies[name] ?? {})); }
        });
        const node = {getParent: () => ({setStyle() {}}), getElement: () => null, getElements: () => []};
        const instance = Object.assign({}, definition, {
            parent() {}, getElm: () => node, getAttribute: () => true,
            Slideout: {on: (name, callback) => { slideoutEvents[name] = callback; }},
            createBasketButton: () => basket
        });
        instance.$onImport();
        slideoutEvents.beforeopen();
        const clicked = {button, icon, container}[target];
        if (target !== 'container') actions++;
        container.click({target: clicked});
        assert.equal(actions, 1);
        assert.equal(container.title, 'Warenkorb');
    });
}
