<?php
// global configuration
// you can overwrite these settings in sldeploy.ini

$conf = array();

$conf['git_bin']        = '/usr/bin/git';
$conf['svn_bin']        = '/usr/bin/svn';
$conf['cvs_bin']        = '/usr/bin/cvs';
$conf['nice_bin']       = '/usr/bin/nice';
$conf['cp_bin']         = '/bin/cp';
$conf['gzip_bin']       = '/bin/gzip';
$conf['gunzip_bin']     = '/bin/gunzip';
$conf['mysqldump_bin']  = '/usr/bin/mysqldump';
$conf['mysql_bin']      = '/usr/bin/mysql';
$conf['mysqladmin_bin'] = '/usr/bin/mysqladmin';
$conf['scp_bin']        = '/usr/bin/scp';
$conf['ssh_bin']        = '/usr/bin/ssh';

// root directory of all projects
$conf['www_path'] = '/www';

// system configuration directory
$conf['system_source'] = '/root/system_source';

$conf['source_scm'] = 'git';

// backup directory (used by reset-db)
$conf['backup_dir'] = '/srv/backups';

// temporary directory
$conf['tmp_dir'] = '/tmp';

// system source code mananagement system: git, svn or cvs
$conf['system_scm']    = 'git';

// activate log
$conf['write_to_log'] = TRUE;

// log file
$conf['log_file'] = '/var/log/sldeploy.log';

$conf['deploy_git']     = array();
$conf['deploy_svn']     = array();
$conf['deploy_cvs']     = array();
$conf['permissions']    = array();
$conf['reset_db']       = array();
$conf['drush']          = array();

$conf['init-system'] = array();
$conf['init-system']['dirs']     = array();
$conf['init-system']['packages'] = '';

// default access for all plugins
$plugin = array('root_only' => FALSE);