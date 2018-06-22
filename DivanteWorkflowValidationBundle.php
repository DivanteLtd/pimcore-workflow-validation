<?php
/**
 * @date        16/11/2017
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2017 DIVANTE (http://divante.pl)
 */

namespace Divante\WorkflowValidationBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

/**
 * Class DivanteWorkflowValidationBundle
 * @package Divante\WorkflowValidationBundle
 */
class DivanteWorkflowValidationBundle extends AbstractPimcoreBundle
{
    /**
     * {@inheritdoc}
     */
    public function getJsPaths()
    {
        return [
            '/bundles/divanteworkflowvalidation/js/pimcore/startup.js',
            '/bundles/divanteworkflowvalidation/js/pimcore/workflow/item.js',
            '/bundles/divanteworkflowvalidation/js/pimcore/workflow/panel.js',
            '/bundles/divanteworkflowvalidation/js/pimcore/workflow/validationRulesWindow.js',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCssPaths()
    {
        return [
            '/bundles/divanteworkflowvalidation/css/style.css',
        ];
    }
}
