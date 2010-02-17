<?php

/**
 * @file
 *
 * Richtet eine neue Umgebung ein
 * Voraussetung damit das funktioniert:
 * Manuelle Installation von:
 * apt-get install git
 * git clone git.squatlabs.net:system_SERVER_NAME /root/git_system
 *
 */

$plugin['info']       = 'initial a new system environment';
$plugin['root_only']  = TRUE;

class sldeploy_plugin_init_system extends sldeploy {

  public function run() {

    if (is_array($this->conf['init-system']['dirs']) &&
        count($this->conf['init-system']['dirs'])) {

      foreach($this->conf['init-system']['dirs'] AS $dir) {
        if (file_exists($dir)) {
          $this->msg('Directory '. $dir . ' already exists.');
        }
        else {
          $this->msg('Creating directory '. $dir);
          mkdir($dir, 0775, TRUE);
        }
      }
    }

    if (!empty($this->conf['init-system']['packages'])) {
      $this->msg('Install linux packages...');
      $rc = $this->system('apt-get install -y '. $this->conf['init-system']['packages']);
      if ($rc['rc']) {
        $this->msg('An error occured while installing packages:');
        foreach ($rc['output'] AS $line) {
          $this->msg($line);
        }
        exit(2);
      }

      $this->system('apt-get clean');

      chown('/var/log/php_errors', 'www-data');
      chgrp('/var/log/php_errors', 'www-data');
    }
  }

}