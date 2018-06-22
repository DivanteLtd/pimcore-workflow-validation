pimcore.registerNS("pimcore.plugin.workflowgui.item");

pimcore.plugin.workflowgui.item = Class.create(pimcore.plugin.workflowgui.item, {

    initialize: function (id, parentPanel) {
        this.parentPanel = parentPanel;
        this.id = id;

        Ext.Ajax.request({
            url: "/admin/workflow-validation/get",
            success: this.loadComplete.bind(this),
            params: {
                id: this.id
            }
        });
    },

    getSettingsPanel: function() {
        if (!this.settingsPanel) {

            var typesStore = [['object', 'object'], ['asset', 'asset'], ['document', 'document']];

            var classesStore = new Ext.data.JsonStore({
                autoDestroy: true,
                proxy: {
                    type: 'ajax',
                    url: '/admin/class/get-tree'
                },
                fields: ['text']
            });
            classesStore.load();

            var assetTypeStore = new Ext.data.JsonStore({
                autoDestroy: true,
                proxy: {
                    type: 'ajax',
                    url: '/admin/class/get-asset-types'
                },
                fields: ["text"]
            });
            assetTypeStore.load();

            var documentTypeStore = new Ext.data.JsonStore({
                autoDestroy: true,
                proxy: {
                    type: 'ajax',
                    url: '/admin/class/get-document-types'
                },
                fields: ["text"]
            });
            documentTypeStore.load();

            this.settingsPanel = new Ext.form.Panel({
                border: false,
                autoScroll: true,
                title: t('settings'),
                iconCls: 'pimcore_icon_settings',
                padding: 10,
                items: [
                    {
                        xtype: 'textfield',
                        name: 'name',
                        width: 500,
                        value: this.data.name,
                        fieldLabel: t('name')
                    },
                    {
                        xtype: 'checkbox',
                        name: 'enabled',
                        width: 500,
                        value: this.data.enabled,
                        fieldLabel: t('enabled')
                    },
                    {
                        xtype: 'checkbox',
                        name: 'allowUnpublished',
                        width: 500,
                        checked: this.data.allowUnpublished,
                        fieldLabel: t('allow_unpusblished')
                    },
                    {
                        xtype: 'combo',
                        fieldLabel: t('default_state'),
                        name: 'defaultState',
                        value: this.data.defaultState,
                        width: 500,
                        store: this.statesStore,
                        triggerAction: 'all',
                        typeAhead: false,
                        editable: false,
                        forceSelection: true,
                        queryMode: 'local',
                        displayField: 'label',
                        valueField: 'name'
                    },
                    {
                        xtype: 'combo',
                        fieldLabel: t('default_status'),
                        name: 'defaultStatus',
                        value: this.data.defaultStatus,
                        width: 500,
                        store: this.statusStore,
                        triggerAction: 'all',
                        typeAhead: false,
                        editable: false,
                        forceSelection: true,
                        queryMode: 'local',
                        displayField: 'label',
                        valueField: 'name'
                    },
                    {
                        xtype: 'combo',
                        fieldLabel: t('types'),
                        name: 'types',
                        value: this.data.workflowSubject ? this.data.workflowSubject.types : [],
                        width: 500,
                        store: typesStore,
                        triggerAction: 'all',
                        typeAhead: false,
                        editable: false,
                        forceSelection: true,
                        queryMode: 'local',
                        multiSelect: true,
                        listeners: {
                            change: function (field, newValue, oldValue, eOpts) {
                                if (!newValue.includes('object')) {
                                    this.actionsStore.getRange().forEach(function (record) {
                                        record.set('validation', {
                                            classes: []
                                        })
                                    });
                                } else if (!oldValue.includes('object')) {
                                    var classes = [];

                                    var settings = this.getSettingsPanel().getForm().getFieldValues();
                                    for (var i = 0; i < settings.classes.length; i++) {
                                        classes.push({
                                            id: settings.classes[i],
                                            rules: []
                                        });
                                    }

                                    this.actionsStore.getRange().forEach(function (record) {
                                        record.set('validation', {
                                            classes: classes
                                        })
                                    });
                                }
                            }.bind(this)
                        }
                    },
                    {
                        xtype: 'combo',
                        fieldLabel: t('allowed_classes'),
                        name: 'classes',
                        value: this.data.workflowSubject ? this.data.workflowSubject.classes : [],
                        width: 500,
                        store: classesStore,
                        triggerAction: 'all',
                        typeAhead: false,
                        editable: false,
                        forceSelection: true,
                        queryMode: 'local',
                        multiSelect: true,
                        valueField: 'id',
                        displayField: 'text',
                        listeners: {
                            change: function (field, newValue, oldValue, eOpts) {
                                this.actionsStore.getRange().forEach(function (record) {
                                    var validation = record.get('validation');

                                    var del = [];
                                    for (var i = 0; i < oldValue.length; i++) {
                                        if (!newValue.includes(oldValue[i])) {
                                            del.push(oldValue[i]);
                                        }
                                    }

                                    var add = [];
                                    for (var i = 0; i < newValue.length; i++) {
                                        if (!oldValue.includes(newValue[i])) {
                                            add.push(newValue[i]);
                                        }
                                    }

                                    var classes = [];

                                    for (var i = 0; i < validation.classes.length; i++) {
                                        var classId = validation.classes[i].id;
                                        if (!del.includes(classId)) {
                                            classes.push(validation.classes[i]);
                                        }
                                    }

                                    for (var i = 0; i < add.length; i++) {
                                        classes.push({
                                            id: add[i],
                                            rules: []
                                        });
                                    }

                                    validation.classes = classes;
                                    record.set('validation', validation);
                                });
                            }.bind(this)
                        }
                    },
                    {
                        xtype: 'combo',
                        fieldLabel: t('allowed_asset_types'),
                        name: 'assetTypes',
                        value: this.data.workflowSubject ? this.data.workflowSubject.assetTypes : [],
                        width: 500,
                        store: assetTypeStore,
                        triggerAction: 'all',
                        typeAhead: false,
                        editable: false,
                        forceSelection: true,
                        queryMode: 'local',
                        multiSelect: true
                    },
                    {
                        xtype: 'combo',
                        fieldLabel: t('allowed_document_types'),
                        name: 'documentTypes',
                        value: this.data.workflowSubject ? this.data.workflowSubject.documentTypes : [],
                        width: 500,
                        store: documentTypeStore,
                        triggerAction: 'all',
                        typeAhead: false,
                        editable: false,
                        forceSelection: true,
                        queryMode: 'local',
                        multiSelect: true
                    }
                ]
            });
        }

        return this.settingsPanel;
    },

    getActionsPanel: function() {
        if (!this.actionsPanel) {
            this.actionsPanel = new Ext.Panel({
                border: false,
                autoScroll: true,
                title: t('actions'),
                iconCls: 'pimcore_icon_workflow',
                items: [
                    {
                        xtype: 'grid',
                        margin: '0 0 15 0',
                        store: this.actionsStore,
                        sm: Ext.create('Ext.selection.RowModel', {}),
                        columns: [
                            {
                                xtype: 'gridcolumn',
                                dataIndex: 'name',
                                text: t('name'),
                                flex: 1
                            },
                            {
                                xtype: 'gridcolumn',
                                flex: 1,
                                dataIndex: 'label',
                                text: t('label')
                            },
                            {
                                menuDisabled: true,
                                sortable: false,
                                xtype: 'actioncolumn',
                                width: 50,
                                items: [{
                                    iconCls: 'pimcore_icon_edit',
                                    tooltip: t('edit'),
                                    handler: function (grid, rowIndex, colIndex) {
                                        this.editAction(grid.store.getAt(rowIndex));
                                    }.bind(this)
                                }]
                            },
                            {
                                menuDisabled: true,
                                sortable: false,
                                xtype: 'actioncolumn',
                                width: 50,
                                items:[{
                                    iconCls: 'pimcore_icon_workflow_validation',
                                    tooltip: t('workflow_validation_rules'),
                                    handler: function (grid, rowIndex, colIndex) {
                                        var record = grid.store.getAt(rowIndex);
                                        var w = new pimcore.plugin.workflowgui.validationRulesWindow(this, record);
                                        w.show();
                                    }.bind(this)
                                }]
                            },
                            {
                                menuDisabled: true,
                                sortable: false,
                                xtype: 'actioncolumn',
                                width: 50,
                                items: [{
                                    iconCls: 'pimcore_icon_delete',
                                    tooltip: t('delete'),
                                    handler: function (grid, rowIndex, colIndex) {
                                        grid.store.removeAt(rowIndex);
                                    }.bind(this)
                                }]
                            }
                        ],
                        tbar: [
                            {
                                text: t('add'),
                                handler: function (btn) {
                                    Ext.MessageBox.prompt(
                                        t('add_workflow_action'),
                                        t('enter_the_name_of_the_new_workflow_action'),
                                        function (button, value) {
                                            if (button == "ok") {
                                                var settings = this.getSettingsPanel().getForm().getFieldValues();

                                                var classes = [];
                                                if (settings.types.includes('object')) {
                                                    for (var i = 0; i < settings.classes.length; i++) {
                                                        classes.push({
                                                            id: settings.classes[i],
                                                            rules: []
                                                        });
                                                    }
                                                }

                                                var u = {
                                                    name: value,
                                                    label: value,
                                                    transitionTo: {},
                                                    notes: {
                                                        required: false
                                                    },
                                                    validation: {
                                                        classes: classes
                                                    }
                                                };

                                                btn.up("grid").store.add(u);
                                            }
                                        }.bind(this)
                                    );
                                }.bind(this),
                                iconCls:"pimcore_icon_add"
                            }
                        ],
                        viewConfig: {
                            forceFit: true
                        }
                    }
                ]
            });
        }

        return this.actionsPanel;
    },

    save: function () {
        Ext.Ajax.request({
            url: "/admin/workflow-validation/update",
            method: "post",
            params: {
                data: this.getData(),
                id : this.id
            },
            success: this.saveOnComplete.bind(this)
        });
    }
});
