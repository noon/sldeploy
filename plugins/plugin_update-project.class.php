<?php

/**
 * @file
 *
 * Update repositories
 *
 */

$plugin['info']       = 'update all project repositories';
$plugin['root_only']  = FALSE;

class sldeploy_plugin_update_project extends sldeploy {

  public function run() {

    $this->update_repository($this->conf['deploy_cvs'], $this->conf['cvs_bin'] .' update', 'CVS');
    $this->update_repository($this->conf['deploy_svn'], $this->conf['svn_bin'] .' update', 'SVN');
    $this->update_repository($this->conf['deploy_git'], $this->conf['git_bin'] .' pull', 'GIT');

    // Make sure sldeploy is executable
    chmod($this->base_dir .'/sldeploy', 0775);
  }

  /**
   * @params array $directories
   * @params string $command
   * @params string $info
   *
   */
  private function update_repository($directories, $command, $info) {

    if ((is_array($directories)) && count($directories)) {
      $this->msg('Update '. $info .' repository...');

      foreach($directories AS $source_dir) {
        if (file_exists($source_dir)) {
          if (is_dir($source_dir)) {
            chdir($source_dir);
            $this->system($command, TRUE);
          }
          else {
            $this->msg($source_dir .' is not a directory');
          }
        }
        else {
          $this->msg($source_dir .' does not exist');
        }
      }
    }

    return TRUE;
  }

}