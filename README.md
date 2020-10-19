# It's in the core now!
As the notifications have been merged to the core of Pimcore, this repository is no longer maintained.

# PimcoreNotifications
 
It's simple plugin that allows to send notifications to user. 
Plugin adds to status bar new clickable icon, on click it opens
new tab with all notifications, also it contains badge with unread
notifications count.

There're two different ways of communication:
- WebSockets - if it's possible to initialize
- Ajax - otherwise

When there's new notification for user, it shows as window
with possibility to close it, mark as read or open details.

## Requirements

- Pimcore with ExtJS6
- Composer (optionally)

## Installation

### First step

#### via Composer

```
composer require divante-ltd/pimcore-notifications
```

#### manually

- Download this repository into your plugins directory.
- Download manually dependencies (see composer.json).
- Follow next steps in this instruction.

### Second step

Open Extension tab in admin panel and install plugin.
After this, installation is finished.

## Usage

If you want to send some notifications to user:
```php
$notification = new \PimcoreNotifications\Model\Notification();
$notification
    ->setTitle('your title of notification goes here')
    ->setMessage('your message')
    ->setType('info') // allowed: info|success|error
    ->setUser($user)
    ->setFromUser($fromUser) // optional
    ->setLinkedElement($document) // optional, allowed: Document|Asset|Object
;
$notification->save();
```

## How to enable WebSockets?

Just run this command (it'll start WebSocket server):
```
php pimcore/cli/console.php pimcore-notifications:run
```

Supervisord is highly recommended to use (read more [here](http://socketo.me/docs/deploy#supervisor) )


## About Authors


![Divante-logo](http://divante.co///logo_1.png "Divante")

We are a Software House from Europe, headquartered in Poland and employing about 150 people. Our core competencies are built around Magento, Pimcore and bespoke software projects (we love Symfony3, Node.js, Angular, React, Vue.js). We specialize in sophisticated integration projects trying to connect hardcore IT with good product design and UX.

Visit our website [Divante.co](https://divante.co/ "Divante.co") for more information.
