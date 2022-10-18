<?php

/**
 * Register application modules
 */
$application->registerModules(array(
    'frontend' => array(
        'className' => 'testproject\Frontend\Module',
        'path' => __DIR__ . '/../apps/frontend/Module.php'
    ),
    'backend' => array(
        'className' => 'testproject\Backend\Module',
        'path' => __DIR__ . '/../apps/backend/Module.php'
    )
));
