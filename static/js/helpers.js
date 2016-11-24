/**
 * @author Piotr Ćwięcek <pcwiecek@divante.pl>
 * @author Kamil Karkus <kkarkus@divante.pl>
 */
pimcore.registerNS("pimcore.plugin.notifications.helpers.x");


pimcore.plugin.notifications.helpers.updateCount = function (count) {
    if (count > 0) {
        Ext.get("notification_value").show();
        Ext.fly('notification_value').update(count);
    } else {
        Ext.get("notification_value").hide();
    }
};

pimcore.plugin.notifications.helpers.showNotifications = function (notifications) {
    for (var i = 0; i < notifications.length; i++) {
        var row = notifications[i];
        var tools = [];
        tools.push({
            type: 'save',
            tooltip: t('mark_as_read'),
            handler: function () {
                this.up('window').close();
                pimcore.plugin.notifications.helpers.markAsRead(row.id);
            }
        });
        if (row.linkedElementId) {
            tools.push({
                type: 'right',
                tooltip: t('open_linked_element'),
                handler: function () {
                    this.up('window').close();
                    pimcore.plugin.notifications.helpers.openLinkedElement(row);
                }
            });
        }
        tools.push({
            type: 'maximize',
            tooltip: t('open'),
            handler: function () {
                this.up('window').close();
                pimcore.plugin.notifications.helpers.openDetails(row.id);
            }
        });
        var notification = Ext.create('Ext.window.Toast', {
            iconCls: 'pimcore_icon_' + row.type,
            title: row.title,
            html: row.message,
            autoShow: true,
            width: 'auto',
            maxWidth: 350,
            closable: true,
            autoClose: false,
            tools: tools
        });
        notification.show();
    }
};

pimcore.plugin.notifications.helpers.delete = function (id, callback) {
    Ext.Ajax.request({
        url: "/plugin/PimcoreNotifications/index/delete?id=" + id,
        success: function (response) {
            if (callback) {
                callback();
            }
        }
    });
};

pimcore.plugin.notifications.helpers.markAsRead = function (id, callback) {
    Ext.Ajax.request({
        url: "/plugin/PimcoreNotifications/index/mark-as-read?id=" + id,
        success: function (response) {
            if (callback) {
                callback();
            }
        }
    });
};

pimcore.plugin.notifications.helpers.deleteAll = function (callback) {
    Ext.Ajax.request({
        url: "/plugin/PimcoreNotifications/index/delete-all",
        success: function (response) {
            if (callback) {
                callback();
            }
        }
    });
};


pimcore.plugin.notifications.helpers.openDetails = function (id, callback) {
    Ext.Ajax.request({
        url: "/plugin/PimcoreNotifications/index/details?id=" + id,
        success: function (response) {
            response = Ext.decode(response.responseText);
            if (!response.success) {
                return;
            }
            pimcore.plugin.notifications.helpers.openDetailsWindow(
                response.data.id,
                response.data.title,
                response.data.message,
                response.data.type,
                callback
            );
        }
    });
};

pimcore.plugin.notifications.helpers.openDetailsWindow = function (id, title, message, type, callback) {
    var notification = new Ext.Window({
        modal: true,
        iconCls: 'pimcore_icon_' + type,
        title: title,
        html: message,
        autoShow: true,
        width: 'auto',
        maxWidth: 700,
        closable: true,
        bodyStyle: "padding: 10px; background:#fff;",
        autoClose: false,
        listeners: {
            focusleave: function () {
                this.close();
            },
            afterrender: function () {
                pimcore.plugin.notifications.helpers.markAsRead(id, callback);
            }
        }
    });
    notification.show(document);
    notification.focus();
};

pimcore.plugin.notifications.helpers.openLinkedElement = function(row) {
    if ('document' == row['linkedElementType']) {
        pimcore.helpers.openElement(row['linkedElementId'], 'document');
    } else if ('asset' == row['linkedElementType']) {
        pimcore.helpers.openElement(row['linkedElementId'], 'asset');
    } else if ('object' == row['linkedElementType']) {
        pimcore.helpers.openElement(row['linkedElementId'], 'object');
    }
};