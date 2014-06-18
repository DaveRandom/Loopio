Loopio
======

Provides interoperability between [Alert](https://github.com/rdlowrey/Alert) and
[React](https://github.com/reactphp/react).

This library is simply a set of adapters that will allow you to use applications designed around the React event loop to
work with an Alert event loop, and vice versa. It does not provide any complete functionality on its own.

Adapters are provided for all stable versions of React up to the 0.4.x series, and all stable versions of Alert up to
the 0.8.x series, excluding the initial Alert release (0.1.0).

###Installation

Preferably via [Composer](https://getcomposer.org/).

    "require": {
        "daverandom/loopio": "~0.1.0"
    }

###Usage

The best way to create the correct adapter for the installed React and Alert versions is to use the appropriate method
of `Loopio\LoopFactory`. This will auto-detect the installed versions of Alert and React and return the correct adapter
for your combination.

---

If you are using React as your primary event loop and you want to use a component designed around Alert in your
application (such as [Artax](https://github.com/rdlowrey/Artax)), an adapter can be created in the following manner:

    <?php

    // Create a React event loop
    $reactLoop = React\EventLoop\Factory::create();

    // Create an Alert adapter
    $alertLoop = (new Loopio\LoopFactory)->createAlertLoop($reactLoop);

    // Create an Artax async client
    $client = new Artax\AsyncClient($alertLoop);

---

If you are using Alert as your primary event loop and you want to use a component designed around React in your
application (such as [Ratchet](https://github.com/ratchetphp/Ratchet)), an adapter can be created in the following
manner:

    <?php

    // Create an Alert event loop
    $alertLoop = (new Alert\ReactorFactory)->select();

    // Create a React adapter
    $reactLoop = (new Loopio\LoopFactory)->createReactLoop($alertLoop);

    // Create a Ratchet app
    $client = new Ratchet\App('localhost', 8080, '127.0.0.1', $reactLoop);
