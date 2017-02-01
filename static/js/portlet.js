/**
 * @author Piotr Ćwięcek <pcwiecek@divante.pl>
 * @author Kamil Karkus <kkarkus@divante.pl>
 */

pimcore.registerNS("pimcore.layout.portlets.notifications");
pimcore.layout.portlets.notifications = Class.create(pimcore.layout.portlets.abstract, {

    getType: function () {
        return "pimcore.layout.portlets.notifications";
    },

    getName: function () {
        return t("notifications");
    },

    getIcon: function () {
        return "pimcore_icon_email";
    },

    getLayout: function (portletId) {
        var itemsPerPage = pimcore.helpers.grid.getDefaultPageSize();
        this.store = pimcore.helpers.grid.buildDefaultStore(
            '/plugin/PimcoreNotifications/index/list?',
            ["id", "title", "from", "date", "unread"],
            itemsPerPage
        );

        var typesColumns = [
            {header: "ID", flex: 1, sortable: false, hidden: true, dataIndex: 'id'},
            {
                header: t("title"),
                flex: 4,
                sortable: false,
                dataIndex: 'title',
                renderer: function (val, metaData, record, rowIndex, colIndex, store) {
                    var unread = parseInt(store.getAt(rowIndex).get("unread"));
                    if (unread) {
                        return '<strong>' + val + '</strong>';
                    }
                    return val;
                }
            },
            {header: t("from"), flex: 2, sortable: false, dataIndex: 'from'},
            {header: t("date"), flex: 2, sortable: false, dataIndex: 'date'},
            {
                header: t("element"),
                xtype: 'actioncolumn',
                flex: 0.5,
                items: [
                    {
                        tooltip: t('open_linked_element'),
                        icon: "/pimcore/static6/img/flat-color-icons/cursor.svg",
                        handler: function (grid, rowIndex) {
                            pimcore.plugin.notifications.helpers.openLinkedElement(grid.getStore().getAt(rowIndex).data);
                        }.bind(this),
                        isDisabled: function (grid, rowIndex) {
                            return !parseInt(grid.getStore().getAt(rowIndex).data['linkedElementId']);
                        }.bind(this)
                    }
                ]
            },
            {
                xtype: 'actioncolumn',
                flex: 1,
                items: [
                    {
                        tooltip: t('open'),
                        icon: "/pimcore/static6/img/flat-color-icons/right.svg",
                        handler: function (grid, rowIndex) {
                            pimcore.plugin.notifications.helpers.openDetails(grid.getStore().getAt(rowIndex).get("id"));
                        }.bind(this)
                    },
                    {
                        tooltip: t('mark_as_read'),
                        icon: '/pimcore/static6/img/flat-color-icons/checkmark.svg',
                        handler: function (grid, rowIndex) {
                            pimcore.plugin.notifications.helpers.markAsRead(grid.getStore().getAt(rowIndex).get("id"), function () {
                                this.reload();
                            }.bind(this));
                        }.bind(this),
                        isDisabled: function (grid, rowIndex) {
                            return !parseInt(grid.getStore().getAt(rowIndex).get("unread"));
                        }.bind(this)
                    },
                    {
                        tooltip: t('delete'),
                        icon: '/pimcore/static6/img/flat-color-icons/delete.svg',
                        handler: function (grid, rowIndex) {
                            pimcore.plugin.notifications.helpers.delete(grid.getStore().getAt(rowIndex).get("id"), function () {
                                this.reload();
                            }.bind(this));
                        }.bind(this)
                    }

                ]
            }
        ];

        this.grid = new Ext.grid.GridPanel({
            frame: false,
            autoScroll: true,
            store: this.store,
            columns: typesColumns,
            trackMouseOver: true,
            bbar: this.pagingtoolbar,
            columnLines: true,
            stripeRows: true,
            listeners: {
                "itemdblclick": function (grid, record, tr, rowIndex, e, eOpts) {
                    pimcore.plugin.notifications.helpers.openDetails(record.data.id, function() {
                        grid.getStore().reload();
                    });
                }

            },
            viewConfig: {
                forceFit: true
            },
            tbar: toolbar
        });

        this.layout = Ext.create('Portal.view.Portlet', Object.extend(this.getDefaultConfig(), {
            title: this.getName(),
            iconCls: this.getIcon(),
            height: 275,
            layout: "fit",
            items: [this.grid]
        }));

        this.layout.portletId = portletId;
        return this.layout;
    }
});
