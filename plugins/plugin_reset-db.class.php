<?php

/**
 * @file
 *   Plugin to reset database
 *
 * This is useful, if you want to fetch a copy an extern installation to your local developer environment.
 *
 */

$plugin['info']       = 'reset database';
$plugin['root_only']  = TRUE;

class sldeploy_plugin_reset_db extends sldeploy {

  const db_para = '-c --skip-opt --disable-keys --set-charset --add-locks --lock-tables --create-options --add-drop-table';

  private $reset_db;

  public function run() {

    if (is_array($this->conf['reset-db']) && count($this->conf['reset-db'])) {

      $this->reset_db = $this->conf['reset-db'];

      if (!empty($this->reset_db['db'])) {

        $sql_file = $this->get_sql_file();

        if (!empty($sql_file)) {

          // create database of existing database
          $this->db_backup();

          // drop database
          $this->system($this->conf['mysqladmin_bin'] .' -f drop '. $this->reset_db['db']);

          $this->msg('Recreating database '. $this->reset_db['db'] .'...');
          sleep(2);

          system($this->conf['mysqladmin_bin'] .' create '. $this->reset_db['db']);

          $this->msg('Import data...');
          $this->system($this->conf['gunzip_bin'] .' < '. $sql_file .' | '. $this->conf['mysql_bin'] .' '. $this->reset_db['db']);

          $this->post_commands();

          $this->msg('Database '. $this->reset_db['db'] .' has been successfully reseted.');
        }
        else {
          $this->msg('SQL file for import could not be identify');
        }
      }
    }
    else {
      $this->msg('No configuration found for reset-db');
    }
  }

  private function get_sql_file() {

    switch ($this->reset_db['mode']) {

      case 'local':
        $sql_file = $this->reset_db['local_sql'] .'.gz';
        break;

      case 'remote':

        $remote_command = $this->conf['ssh_bin'] .' '. $this->reset_db['remote_user'] .'@'. $this->reset_db['remote_server'];
        $remote_file    = $this->reset_db['remote_dir'] .'/'. $this->reset_db['db'] .'.sql';

        $this->msg('Create Dump on remote server...');
        $rc = $this->system($remote_command .' "'.  $this->conf['mysqldump_bin'] .' '. $this->reset_db['db'] .' > '. $remote_file .'"', TRUE);
        if ($rc['rc']) {
          $this->msg('Error creating remote dump.');
          exit(1);
        }

        $this->msg('Compress remote file...');
        $rc = $this->system($remote_command .' '.  $this->conf['gzip_bin'] .' -f '. $remote_file);
        if ($rc['rc']) {
          $this->msg('Error compress remote file.');
          exit(1);
        }

        $scp_file = $this->reset_db['remote_user'] . '@'. $this->reset_db['remote_server'] .':'. $this->reset_db['remote_dir'] .'/'. $this->reset_db['db'] .'.sql.gz';
        $sql_file = $this->conf['tmp_dir'] .'/'. $this->reset_db['db'] .'.sql.gz';

        $this->msg('Transfer file...');
        $rc = $this->system($this->conf['scp_bin'] .' '. $scp_file . ' ' .$sql_file);
        if ($rc['rc']) {
          $this->msg('SQL file could not be transfered. ('. $scp_file .')');
          exit(5);
        }
        break;

      default:
        $sql_file = '';
    }

    return $sql_file;
  }

  private function post_commands() {

    if (is_array($this->reset_db['post_commands'])) {

      foreach ($this->reset_db['post_commands'] AS $command) {
        $this->msg('Running post command: '. $command);
        $this->system($command);
      }
    }
  }

  private function db_backup() {

    $this->msg('creating database dump of '. $this->reset_db['db']);
    $target_file = $this->conf['backup_dir'] .'/'. $this->reset_db['db'] . '-'. $this->date_stamp .'.sql';
    $this->system($this->conf['nice_bin'] .' -10 '. $this->conf['mysqldump_bin'] .' '. self::db_para .' '. $this->reset_db['db'] .' > '. $target_file);

    $this->gzip_file($target_file);
  }

  private function gzip_file($file) {
    $this->msg('compressing '. $file);
    $this->system($this->conf['nice_bin'] .' -n 15 '. $this->conf['gzip_bin'] .' '. $file);
  }

}