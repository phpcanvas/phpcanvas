<?php
class MainController extends ControllerInterface{
    public $layout = false;
    public $scriptTime = false;
    
    public function main() {
        $this->layout = 'demo';
        $this->scriptTime = false;
        
        $phpver = phpversion();
        $sysDir = strtoupper(str_replace('\\', '/', App::$sysroot));
        
        $appDir = pathinfo($_SERVER['SCRIPT_FILENAME']);
        $appDir = strtoupper($appDir['dirname']);
        
        if (0 === strpos($sysDir, '..') || 0 === strpos($sysDir, '/') || 0 !== strpos($sysDir, $appDir)) {
            $integration = 'Centralized';
        } else {
            $integration = 'Portable';
        }
        if (function_exists('apache_get_modules')) { 
            $modules = apache_get_modules();
            $modules = in_array('mod_rewrite', $modules) ? 'ok': 'failed';
        } else {
            $modules = 'unknown';
        }
         
        $fallback = function_exists('json_decode') && function_exists('json_encode') ? 
            (5 <= (int)$phpver && 2 <= (int)substr($phpver, 2) ? 'ignored': 'ok'): 'failed';
        
        $tmp = new File(App::conf('file.tmp'));
        if(touch(App::conf('file.tmp') . '/demo.txt')) {
            $tempFolder = 'ok';
            unlink(App::conf('file.tmp') . '/demo.txt');
        } else {
            $tempFolder = 'failed';
        }
        
        $dbs = App::conf('database');
        if (empty($dbs)) {
            $db = 'empty';
        } else {
            list($dbName, $dbConf) = each($dbs);
            $modelMySQL = new MysqlDatabase();
            $modelOracle = new OracleDatabase();
            
            if ($modelMySQL->connect($dbConf)) {
                $db = 'ok';
            } else {
                $db = 'failed';
            }
        }
       
        $siteurl = false === App::conf('site_url') ? 'unset': 'ok';
        
        $this->show('phpversion', $phpver);
        $this->show('integration', $integration);
        $this->show('rewrite', $modules);
        $this->show('fallback', $fallback);
        $this->show('tempfolder', $tempFolder);
        $this->show('dbname', $dbName);
        $this->show('db', $db);
        $this->show('siteurl', $siteurl);
    }
}
