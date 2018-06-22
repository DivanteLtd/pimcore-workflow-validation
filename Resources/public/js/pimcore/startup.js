/**
 * @date        16/11/2017
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2017 DIVANTE (http://divante.pl)
 */

pimcore.registerNS("pimcore.plugin.DivanteWorkflowValidationBundle");

pimcore.plugin.DivanteWorkflowValidationBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.DivanteWorkflowValidationBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
        // alert("DivanteWorkflowValidationBundle ready!");
    }
});

var DivanteWorkflowValidationBundlePlugin = new pimcore.plugin.DivanteWorkflowValidationBundle();
