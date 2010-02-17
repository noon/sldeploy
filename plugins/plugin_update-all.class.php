<?php

/**
 * @file
 *
 * Run other plugins as a batch
 *
 */

$plugin['info']       = 'Run update-project, update-system, update-drupal and permission plugins';
$plugin['root_only']  = FALSE;

class sldeploy_plugin_update_all extends sldeploy {

  public function run_batch() {
    return array('update-project', 'update-system', 'update-drupal', 'permission');
  }
}