<?php

/**
 *
 * Set file and directory permissions
 *
 *
 * Permissions
 *
 * name = filename or directory name
 * rec  = recursive: yes, files (files only) dirs (directories only) or no [default]
 * mod  = permissions
 * own  = owner
 */

$plugin['info']       = 'set file and directory permissions';
$plugin['root_only']  = TRUE;

class sldeploy_plugin_permission extends sldeploy {

  public function run() {

    $this->msg('Set permissions...');

    if (is_array($this->conf['permissions'])) {

      foreach ($this->conf['permissions'] AS $permission) {

        if ($permission['name'] == '/') {
    			$this->msg('Permission should never ever set tor / (recursive)!');
          exit(2);
        }
        elseif (empty($permission['name'])) {
          $this->msg('Missing name (directory) for this entry.');
        }
        else {

          if (!empty($permission['mod'])) {
            $this->set_permissions('mod',
                                    $permission['name'],
                                    $permission['mod'],
                                    $permission['rec']);
          }

          if (!empty($permission['own'])) {
            $this->set_permissions('own',
                                    $permission['name'],
                                    $permission['own'],
                                    $permission['rec']);
          }
        }
      }
    }
  }

  /**
    *
    * Set permissions
    *
    * @params string $mode
    * @params string $directory
    * @params string $value
    * @params string $recursive
    *
    */
  private function set_permissions($mode, $directory, $value, $recursive=NULL) {

    if ($mode=='own') $command = 'chown';
    else              $command = 'chmod';

    if (!empty($value)) {

      $this->msg('Set permissions ('. $value .') to '. $directory .'...');

      switch ($recursive) {

        case 'files':
          $this->system('find '. $directory .' -type f -exec '. $command .' '. $value .' {} \;');
          break;

        case 'dirs':
          $this->system('find '. $directory .' -type d -exec '. $command .' '. $value .' {} \;');
          break;

        case 'yes':
          $this->system($command .' -R '. $value .' '. $directory);
          break;

        default: // not recursive
          $this->system($command .' '. $value .' '. $directory);
      }
    }
  }

}