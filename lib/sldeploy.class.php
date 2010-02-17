<?php

/**
 * @file
 *   Main class of sl-deploy script
 *
 */

class sldeploy {

  /**
   * Version of sldeploy
   *
   * @var int
   */
  private $version = '0.10';

  /**
   * Base directory
   *
   * @var string
   */
  public $base_dir;

  /**
   * Plugin directory
   *
   * @var string
   */
  public $plugin_dir;

  /**
   * Configuration
   *
   * @var array
   */
  public $conf;

  /**
   * Debug mode
   *
   * @var bool
   */
  public $debug = false;

  /**
   * Hostname of script server
   *
   * @var string
   */
  protected $hostname;

  /**
   * Active plugin
   *
   * (see $this->check_paras for available plugins)
   *
   * @var string
   */
  protected $plugin_name;

  /**
   * A list of all available plugins
   *
   * @var array
   */
  public $plugins;

  /**
   *
   * @var string
   */
  public $date_stamp;

  /**
   * User, which execute sldeploy
   *
   * @var string
   */
  public $current_user;

  public function __construct($conf, $write_to_log=FALSE) {

    $this->conf         = $conf;
    $this->hostname     = $this->get_hostname();
    $this->base_dir     = dirname($_SERVER['SCRIPT_NAME']);
    $this->plugin_dir   = $this->base_dir .'/plugins';

    $this->date_stamp   = date('YmdHi');

    $rc        = $this->system('whoami');
    $this->current_user = $rc['output'][0];

    $paras              = getopt('p:rvqjh');

    if (isset($this->conf['debug']) && $this->conf['debug'] ) {
      $this->debug = true;
    }

    if (($this->conf['write_to_log']) && (!$write_to_log)) {
      $this->conf['write_to_log'] = FALSE;
    }

    if ($this->check_paras($paras)) {
      if (array_key_exists('h', $paras)) {
        $this->help();
        exit(0);
      }
      else {
        $this->plugin_name = $paras['p'];
        if (array_key_exists('r', $paras)) $this->with_report = TRUE;
        if (array_key_exists('v', $paras)) $this->debug       = TRUE;
        if (array_key_exists('q', $paras)) $this->quiet       = TRUE;
      }
    }
    else {
      $this->msg('wrong usage');
      $this->help();
      exit(1);
    }
  }

  /**
   * Print script help to console
   */
  public function help() {

    $this->msg('sldeploy '. $this->version);
    $this->msg("\nUsage: -p PLUGIN [OPTION]\n");
    $this->msg("PLUGIN is required and has to be one of the following values:");

    $plugins = $this->get_plugin_info();
    foreach ($plugins AS $plugin_name => $plugin_info) {
      $this->msg($plugin_name ."\t". $plugin_info);
    }

    $this->msg("\nList of optional parameters:");
    $this->msg("-h\tdisplay this help");
    $this->msg("-q\tsuppress messages (expect error message)");
    $this->msg("-r\tsend status report (e.g. email)");
  }


  private function get_plugin_info() {

    $plugins = array();

    foreach($this->plugins AS $plugin_name) {
      $plugin['info'] = '';
      require_once $this->plugin_dir .'/plugin_'. $plugin_name .'.class.php';

      if ($this->check_plugin_permission($plugin['root_only'])) {
        if (!empty($plugin['info'])) {
          $plugins[$plugin_name] = $plugin['info'];
        }
        else {
          $plugins[$plugin_name] = 'No plugin info defined.';
        }
      }
    }

    return $plugins;
  }

  /**
    * Get all available modules
    *
    */
  private function set_plugins() {

    $this->plugins = array();

    $d = dir($this->plugin_dir);
    while (false !== ($entry = $d->read())) {
      if ((substr($entry, 0, 7) == 'plugin_') && (substr($entry, -10) == '.class.php')) {
        $this->plugins[] = substr($entry, 7, -10);
      }
    }
    $d->close();
  }

  /**
   * Check parameters, if there are valid
   *
   * m (required) = modules
   *
   * @param   array   $paras
   * @return  bool    true, if parameters are valid
   */
  private function check_paras($paras) {

    $this->set_plugins();

    if (!is_array($paras))                        return;
    if (array_key_exists('h', $paras))            return true;
    if ((!array_key_exists('p', $paras)) ||
        (!in_array($paras['p'], $this->plugins))) return;

/*
      switch ($paras['p']) {
          case 'build':
              if (!array_key_exists('C', $paras)) return;
              break;
      }
*/
      return TRUE;
  }

  /**
   * Run specified plugin
   *
   * @return  int - return code of plugin
   */
  public function run() {

    require_once $this->plugin_dir .'/plugin_'. $this->plugin_name .'.class.php';

    $c = 'sldeploy_plugin_'. str_replace('-', '_', $this->plugin_name);
    $app = new $c($this->conf, TRUE);

    // if "run_batch" exists, use this instead of "run"
    if (method_exists($app, 'run_batch')) {
      $this->msg('Batch mode');

      $plugins = $app->run_batch();
      foreach ($plugins AS $plugin_name) {

        if ($plugin_name==$this->plugin_name) {
          $this->msg('run_batch misconfiguration. batch mode itself can not be a child.');
          exit(1);
        }

        $this->msg('Run '. $plugin_name .' at '. $this->hostname .'...');

        require_once $this->plugin_dir .'/plugin_'. $plugin_name .'.class.php';

        // make sure, we are in base directory (possible change in a plugin)
        chdir($this->base_dir);

        $c = 'sldeploy_plugin_'. str_replace('-', '_', $plugin_name);
        $app = new $c($this->conf, TRUE);

        if (!$this->check_plugin_permission($plugin['root_only'])) {
          $this->msg('Only root can run this plugin');
          exit(2);
        }

        $rc = $app->run();
        if ($rc) {
          $this->msg('An error occured (rc='. $rc .')');
        }
      }

      if (!$rc) {
        $this->msg('Deploy finished.');
      }
    }
    else {

      if (!$this->check_plugin_permission($plugin['root_only'])) {
        $this->msg('Only root can run this plugin');
        exit(2);
      }

      $this->msg('Run '. $this->plugin_name .' at '. $this->hostname .'...');

      $rc = $app->run();
      if ($rc) {
        $this->msg('An error occured (rc='. $rc .')');
      }
      else {
        $this->msg('Deploy finished.');
      }
    }

    return $rc;
  }

  /**
   * Get hostname
   * @return  string
   */
  protected function get_hostname() {
    $hi = $this->system('hostname');
    return $hi['output'][0];
  }

  /**
   * Execute system call
   *
   * @param   string  $command    - command to execute
   * @return  string              - command output
   */
  public function system($command, $passthru=FALSE) {
    if ($this->debug) {
      $this->msg('system: '. $command);
    }

    if ($passthru) {
      passthru($command, $rc);
      $output = '';
    }
    else {
      exec($command, $output, $rc);
    }

    return array('output' => $output, 'rc' => $rc);
  }

  /**
   * Print message to console
   */
  public function msg($msg){
    echo $msg ."\n";
    if ($this->conf['write_to_log']) {
      @file_put_contents($this->conf['log_file'], date('c'). ' '. $msg ."\n", FILE_APPEND);
    }
  }

  public function check_plugin_permission($root_only) {

    $rc = TRUE;

    if ($root_only) {

      if ($this->current_user != 'root') {
        $rc = FALSE;
      }
    }

    return $rc;
  }
}
