pimcore.registerNS("pimcore.plugin.workflowgui.panel");

pimcore.plugin.workflowgui.panel = Class.create(pimcore.plugin.workflowgui.panel, {

    deleteField: function (tree, record) {
        Ext.Ajax.request({
            url: "/admin/workflow-validation/delete",
            params: {
                id: record.data.id
            }
        });

        this.getEditPanel().removeAll();
        record.remove();
    }
});
