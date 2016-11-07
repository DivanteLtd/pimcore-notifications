/**
 * @author Kamil Karkus <kkarkus@divante.pl>
 */
pimcore.registerNS("pimcore.plugin.notifications");

pimcore.plugin.notifications = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.notifications";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    startAjaxConnection: function () {
        pimcore["intervals"]["checkNewNotification"] = window.setInterval(function () {
            Ext.Ajax.request({
                url: "/plugin/PimcoreNotifications/index/unread?interval=" + 30,
                success: function (response) {
                    var data = Ext.decode(response.responseText);
                    pimcore.plugin.notifications.helpers.updateCount(data.unread);
                    pimcore.plugin.notifications.helpers.showNotifications(data.data);
                }
            });
        }, 30000);
    },

    startConnection: function () {
        Ext.Ajax.request({
            url: "/plugin/PimcoreNotifications/index/token",
            success: function (response) {
                var data = Ext.decode(response.responseText);

                this.socket = new WebSocket("ws://" + location.host + ":8080/?token=" + data['token'] + "&user=" + data['user']);
                this.socket.onopen = function (event) {
                };
                this.socket.onclose = function (event) {
                };
                this.socket.onerror = function (error) {
                    //cannot start websocket so start ajax
                    this.startAjaxConnection();
                }.bind(this);
                this.socket.onmessage = function (event) {
                    var msg = event.data;
                    var data = Ext.decode(msg);
                    var unreadCount = data['unread'];
                    var newNotifications = data['notifications'];

                    pimcore.plugin.notifications.helpers.updateCount(unreadCount);
                    pimcore.plugin.notifications.helpers.showNotifications(newNotifications);
                };
            }.bind(this)
        });
    },

    addIcon: function () {
        var statusbar = Ext.get("pimcore_status");
        statusbar.insertHtml('AfterBegin', '<div id="pimcore_status_notification" data-menu-tooltip=" ' + t("pimcore_status_notification") + '" style="display: none; cursor: pointer;"><span id="notification_value" style="display: none;"></span></div>');

        Ext.get("pimcore_status_notification").show();
        Ext.get("pimcore_status_notification").on("click", this.showNotificationTab.bind());
    },

    pimcoreReady: function (params, broker) {
        this.addIcon();
        this.startConnection();
    },

    showNotificationTab: function () {
        try {
            pimcore.globalmanager.get("notifications").activate();
        }
        catch (e) {
            pimcore.globalmanager.add("notifications", new pimcore.plugin.notifications.panel());
        }
    }
});

var notificationsPlugin = new pimcore.plugin.notifications();

