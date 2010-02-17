<?php

/**
 * @file
 *
 *   Run drupal update
 *
 */

$plugin['info']       = 'run drupal updates on all drupal projects';
$plugin['root_only']  = FALSE;

class sldeploy_plugin_update_drupal extends sldeploy {

  public function run() {

    if (is_array($this->conf['drush']) && count($this->conf['drush'])) {
      foreach ($this->conf['drush'] AS $name => $script) {
        $this->msg('Drupal updatedb to '. $name);
        $this->drush_exec($script);
      }
    }
    elseif (!empty($this->conf['drush'])) {
      $this->msg('Drupal updatedb');
      $this->drush_exec($this->conf['drush']);
    }
    else {
      $this->msg('Drush configuration is required to run update_drupal!');
      exit(1);
    }
  }

  private function drush_exec($script) {

    $this->system($script . ' --yes updatedb', TRUE);
    $this->system($script . ' cache clear', TRUE);
    $this->system($script . ' cron', TRUE);

  }

}
