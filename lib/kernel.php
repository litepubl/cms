<?php
//db.class.php
namespace litepubl;

class tdatabase {
    public $mysqli;
    public $result;
    public $sql;
    public $cache;
    public $dbname;
    public $table;
    public $prefix;
    public $history;
    public $debug;

    public static function i() {
        return getinstance(__class__);
    }

    public static function instance() {
        return static ::i();
    }

    public function __construct() {
        $this->sql = '';
        $this->cache = false;
        $this->table = '';
        $this->history = array();

        $this->setconfig($this->getconfig());
    }

    public function getconfig() {
        $this->debug = & litepubl::$debug;
        if (config::$db) {
            return config::$db;
        }

        if (isset(litepubl::$options->dbconfig)) {
            $result = litepubl::$options->dbconfig;
            //decrypt db password
            $result['password'] = litepubl::$options->dbpassword;
            return $result;
        }

        return false;
    }

    public function setconfig($dbconfig) {
        if (!$dbconfig) return false;
        $this->dbname = $dbconfig['dbname'];
        $this->prefix = $dbconfig['prefix'];

        $this->mysqli = new \mysqli($dbconfig['host'], $dbconfig['login'], $dbconfig['password'], $dbconfig['dbname'], $dbconfig['port'] > 0 ? $dbconfig['port'] : null);

        if (mysqli_connect_error()) {
            throw new \Exception('Error connect to database');
        }

        $this->mysqli->set_charset('utf8');
        //$this->query('SET NAMES utf8');
        /* lost performance
        $timezone = date('Z') / 3600;
        if ($timezone > 0) $timezone = "+$timezone";
        $this->query("SET time_zone = '$timezone:00'");
        */
    }
    /*
    public function __destruct() {
    if (is_object($this)) {
      if (is_object($this->mysqli)) $this->mysqli->close();
      $this->mysqli = false;
    }
    }
    */
    public function __get($name) {
        return $this->prefix . $name;
    }

    public function exec($sql) {
        return $this->query($sql);
    }

    public function query($sql) {
        $this->sql = $sql;
        if ($this->debug) {
            $this->history[] = array(
                'sql' => $sql,
                'time' => 0
            );
            $microtime = microtime(true);
        }

        if (is_object($this->result)) $this->result->close();

        if ($this->cache) {
            $sql = trim($sql);
            $select = 'select ';
            $sql_select = ($select == strtolower(substr($sql, 0, strlen($select)))) && !strpos($sql, 'last_insert_id');
            if ($sql_select) {
                if ($this->result = $this->cache->get($sql)) {
                    if ($this->debug) $this->history[count($this->history) - 1]['time'] = microtime(true) - $microtime;
                    return $this->result;
                }
            } else {
                $this->cache->clear();
            }
        }

        $this->result = $this->mysqli->query($sql);
        if ($this->debug) {
            $this->history[count($this->history) - 1]['time'] = microtime(true) - $microtime;
            if ($this->mysqli->warning_count && ($r = $this->mysqli->query('SHOW WARNINGS'))) {
                echo "<pre>\n$sql\n";
                var_dump($r->fetch_assoc());
                echo "</pre>\n";
            }
        }

        if ($this->result == false) {
            $this->doerror($this->mysqli->error);
        } elseif ($this->cache && $sql_select) {
            $this->cache->set($sql, $this->result);
        }

        return $this->result;
    }

    protected function doerror($mesg) {
        if (!$this->debug) return litepubl::$options->trace($this->sql . "\n" . $mesg);
        $log = "exception:\n$mesg\n$this->sql\n";
        try {
            throw new \Exception();
        }
        catch(Exception $e) {
            $log.= str_replace(litepubl::$paths->home, '', $e->getTraceAsString());
        }

        $log.= $this->performance();
        $log = str_replace("\n", "<br />\n", htmlspecialchars($log));
        die($log);
    }

    public function performance() {
        $result = '';
        $total = 0.0;
        $max = 0.0;
        foreach ($this->history as $i => $item) {
            $result.= "$i: {$item['time']}\n{$item['sql']}\n\n";
            $total+= $item['time'];
            if ($max < $item['time']) {
                $maxsql = $item['sql'];
                $max = $item['time'];
            }
        }
        $result.= "maximum $max\n$maxsql\n";
        $result.= sprintf("%s total time\n%d querries\n\n", $total, count($this->history));
        return $result;
    }

    public function quote($s) {
        return sprintf('\'%s\'', $this->mysqli->real_escape_string($s));
    }

    public function escape($s) {
        return $this->mysqli->real_escape_string($s);
    }

    public function settable($table) {
        $this->table = $table;
        return $this;
    }

    public function select($where) {
        if ($where != '') $where = 'where ' . $where;
        return $this->query("SELECT * FROM $this->prefix$this->table $where");
    }

    public function idselect($where) {
        return $this->res2id($this->query("select id from $this->prefix$this->table where $where"));
    }

    public function selectassoc($sql) {
        return $this->query($sql)->fetch_assoc();
    }

    public function getassoc($where) {
        return $this->select($where)->fetch_assoc();
    }

    public function update($values, $where) {
        return $this->query("update $this->prefix$this->table set $values   where $where");
    }

    public function idupdate($id, $values) {
        return $this->update($values, "id = $id");
    }

    public function assoc2update(array $a) {
        $list = array();
        foreach ($a As $name => $value) {
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
                $list[] = sprintf('%s = %s ', $name, $value);
                continue;
            }

            $list[] = sprintf('%s = %s', $name, $this->quote($value));
        }

        return implode(', ', $list);
    }

    public function updateassoc(array $a, $index = 'id') {
        $id = $a[$index];
        unset($a[$index]);
        return $this->update($this->assoc2update($a) , "$index = '$id' limit 1");
    }

    public function insertrow($row) {
        return $this->query(sprintf('INSERT INTO %s%s %s', $this->prefix, $this->table, $row));
    }

    public function insertassoc(array $a) {
        unset($a['id']);
        return $this->add($a);
    }

    public function addupdate(array $a) {
        if ($this->idexists($a['id'])) {
            $this->updateassoc($a);
        } else {
            return $this->add($a);
        }
    }

    public function add(array $a) {
        $this->insertrow($this->assoctorow($a));
        if ($id = $this->mysqli->insert_id) return $id;
        $r = $this->query('select last_insert_id() from ' . $this->prefix . $this->table)->fetch_row();
        return (int)$r[0];
    }

    public function insert(array $a) {
        $this->insertrow($this->assoctorow($a));
    }

    public function assoctorow(array $a) {
        $vals = array();
        foreach ($a as $val) {
            if (is_bool($val)) {
                $vals[] = $val ? '1' : '0';
            } else {
                $vals[] = $this->quote($val);
            }
        }
        return sprintf('(%s) values (%s)', implode(', ', array_keys($a)) , implode(', ', $vals));
    }

    public function getcount($where = '') {
        $sql = "SELECT COUNT(*) as count FROM $this->prefix$this->table";
        if ($where) $sql.= ' where ' . $where;
        if (($res = $this->query($sql)) && ($r = $res->fetch_assoc())) {
            return (int)$r['count'];
        }
        return false;
    }

    public function delete($where) {
        return $this->query("delete from $this->prefix$this->table where $where");
    }

    public function iddelete($id) {
        return $this->query("delete from $this->prefix$this->table where id = $id");
    }

    public function deleteitems(array $items) {
        return $this->delete('id in (' . implode(', ', $items) . ')');
    }

    public function idexists($id) {
        if ($r = $this->query("select id  from $this->prefix$this->table where id = $id limit 1")) {
            return $r && $r->fetch_assoc();
        }

        return false;
    }

    public function exists($where) {
        return $this->query("select *  from $this->prefix$this->table where $where limit 1")->num_rows;
    }

    public function getlist(array $list) {
        return $this->res2assoc($this->select(sprintf('id in (%s)', implode(',', $list))));
    }

    public function getitems($where) {
        return $this->res2assoc($this->select($where));
    }

    public function getitem($id, $propname = 'id') {
        if ($r = $this->query("select * from $this->prefix$this->table where $propname = $id limit 1")) return $r->fetch_assoc();
        return false;
    }

    public function finditem($where) {
        return $this->query("select * from $this->prefix$this->table where $where limit 1")->fetch_assoc();
    }

    public function findid($where) {
        return $this->findprop('id', $where);
    }

    public function findprop($propname, $where) {
        if ($r = $this->query("select $propname from $this->prefix$this->table where $where limit 1")->fetch_assoc()) return $r[$propname];
        return false;
    }

    public function getval($table, $id, $name) {
        if ($r = $this->query("select $name from $this->prefix$table where id = $id limit 1")->fetch_assoc()) return $r[$name];
        return false;
    }

    public function getvalue($id, $name) {
        if ($r = $this->query("select $name from $this->prefix$this->table where id = $id limit 1")->fetch_assoc()) return $r[$name];
        return false;
    }

    public function setvalue($id, $name, $value) {
        return $this->update("$name = " . $this->quote($value) , "id = $id");
    }

    public function getvalues($names, $where) {
        $result = array();
        $res = $this->query("select $names from $this->prefix$this->table where $where");
        if (is_object($res)) {
            while ($r = $res->fetch_row()) {
                $result[$r[0]] = $r[1];
            }
        }
        return $result;
    }

    public function res2array($res) {
        $result = array();
        if (is_object($res)) {
            while ($row = $res->fetch_row()) {
                $result[] = $row;
            }
            return $result;
        }
    }

    public function res2id($res) {
        $result = array();
        if (is_object($res)) {
            while ($row = $res->fetch_row()) {
                $result[] = $row[0];
            }
        }
        return $result;
    }

    public function res2assoc($res) {
        $result = array();
        if (is_object($res)) {
            while ($r = $res->fetch_assoc()) {
                $result[] = $r;
            }
        }
        return $result;
    }

    public function res2items($res) {
        $result = array();
        if (is_object($res)) {
            while ($r = $res->fetch_assoc()) {
                $result[(int)$r['id']] = $r;
            }
        }
        return $result;
    }

    public function fetchassoc($res) {
        return is_object($res) ? $res->fetch_assoc() : false;
    }

    public function fetchnum($res) {
        return is_object($res) ? $res->fetch_row() : false;
    }

    public function countof($res) {
        return is_object($res) ? $res->num_rows : 0;
    }

    public static function str2array($s) {
        $result = array();
        foreach (explode(',', $s) as $value) {
            if ($v = (int)trim($value)) {
                $result[] = $v;
            }
        }

        return $result;
    }

} //class

//data.class.php
namespace litepubl;

class tdata {
    const zerodate = '0000-00-00 00:00:00';
    public $data;
    public $basename;
    public $cache;
    public $coclasses;
    public $coinstances;
    public $lockcount;
    public $table;
    public static $guid = 0;

    public static function i() {
        return litepubl::$classes->getinstance(get_called_class());
    }

    public static function instance() {
        return static ::i();
    }

    public function __construct() {
        $this->lockcount = 0;
        $this->cache = true;
        $this->data = array();
        $this->coinstances = array();
        $this->coclasses = array();

        if (!$this->basename) {
            $this->basename = ltrim(basename(get_class($this)) , 'tT');
        }

        $this->create();
    }

    protected function create() {
    }

    public function __get($name) {
        if (method_exists($this, $get = 'get' . $name)) {
            return $this->$get();
        } elseif (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            foreach ($this->coinstances as $coinstance) {
                if (isset($coinstance->$name)) {
                    return $coinstance->$name;
                }
            }

            $this->error(sprintf('The requested property "%s" not found in class  %s', $name, get_class($this)));
        }
    }

    public function __set($name, $value) {
        if (method_exists($this, $set = 'set' . $name)) {
            $this->$set($value);
            return true;
        }

        if (key_exists($name, $this->data)) {
            $this->data[$name] = $value;
            return true;
        }

        foreach ($this->coinstances as $coinstance) {
            if (isset($coinstance->$name)) {
                $coinstance->$name = $value;
                return true;
            }
        }

        return false;
    }

    public function __call($name, $params) {
        if (method_exists($this, strtolower($name))) {
            return call_user_func_array(array(
                $this,
                strtolower($name)
            ) , $params);
        }

        foreach ($this->coinstances as $coinstance) {
            if (method_exists($coinstance, $name) || $coinstance->method_exists($name)) {
                return call_user_func_array(array(
                    $coinstance,
                    $name
                ) , $params);
            }
        }

        $this->error("The requested method $name not found in class " . get_class($this));
    }

    public function __isset($name) {
        if (array_key_exists($name, $this->data) || method_exists($this, "get$name") || method_exists($this, "Get$name")) {
            return true;
        }

        foreach ($this->coinstances as $coinstance) {
            if (isset($coinstance->$name)) {
                return true;
            }
        }

        return false;
    }

    public function method_exists($name) {
        return false;
    }

    public function error($Msg, $code = 0) {
        throw new \Exception($Msg, $code);
    }

    public function getbasename() {
        return $this->basename;
    }

    public function install() {
        $this->externalchain('Install');
    }

    public function uninstall() {
        $this->externalchain('Uninstall');
    }

    public function validate($repair = false) {
        $this->externalchain('Validate', $repair);
    }

    protected function externalchain($func, $arg = null) {
        $parents = class_parents($this);
        array_splice($parents, 0, 0, get_class($this));
        foreach ($parents as $class) {
            $this->externalfunc($class, $func, $arg);
        }
    }

    public function externalfunc($class, $func, $args) {
        $reflector = new \ReflectionClass($class);
        $filename = $reflector->getFileName();

        if (strpos($filename, '/kernel.')) {
            $filename = dirname($filename) . '/' . litepubl::$classes->items[$class];
        }

        $externalname = basename($filename, '.php') . '.install.php';
        $dir = dirname($filename) . DIRECTORY_SEPARATOR;
        $file = $dir . 'install' . DIRECTORY_SEPARATOR . $externalname;
        if (!file_exists($file)) {
            $file = $dir . $externalname;
            if (!file_exists($file)) {
                return;
            }
        }

        include_once ($file);

        $fnc = $class . $func;
        if (function_exists($fnc)) {
            if (is_array($args)) {
                array_unshift($args, $this);
            } else {
                $args = array(
                    $this,
                    $args
                );
            }

            return \call_user_func_array($fnc, $args);
        }
    }

    public function getstorage() {
        return litepubl::$storage;
    }

    public function load() {
        if ($this->getstorage()->load($this)) {
            $this->afterload();
            return true;
        }

        return false;
    }

    public function save() {
        if ($this->lockcount) {
            return;
        }

        return $this->getstorage()->save($this);
    }

    public function afterload() {
        foreach ($this->coinstances as $coinstance) {
            if (method_exists($coinstance, 'afterload')) {
                $coinstance->afterload();
            }
        }
    }

    public function lock() {
        $this->lockcount++;
    }

    public function unlock() {
        if (--$this->lockcount <= 0) {
            $this->save();
        }
    }

    public function getlocked() {
        return $this->lockcount > 0;
    }

    public function Getclass() {
        return get_class($this);
    }

    public function getdbversion() {
        return false;

    }

    public function getdb($table = '') {
        $table = $table ? $table : $this->table;
        if ($table) {
            litepubl::$db->table = $table;
        }

        return litepubl::$db;
    }

    protected function getthistable() {
        return litepubl::$db->prefix . $this->table;
    }

    public static function get_class_name($c) {
        return is_object($c) ? get_class($c) : trim($c);
    }

    public static function encrypt($s, $key) {
        $maxkey = mcrypt_get_key_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
        if (strlen($key) > $maxkey) {
            $key = substr($key, $maxkey);
        }

        $block = mcrypt_get_block_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
        $pad = $block - (strlen($s) % $block);
        $s.= str_repeat(chr($pad) , $pad);
        return mcrypt_encrypt(MCRYPT_Blowfish, $key, $s, MCRYPT_MODE_ECB);
    }

    public static function decrypt($s, $key) {
        $maxkey = mcrypt_get_key_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
        if (strlen($key) > $maxkey) {
            $key = substr($key, $maxkey);
        }

        $s = mcrypt_decrypt(MCRYPT_Blowfish, $key, $s, MCRYPT_MODE_ECB);
        $len = strlen($s);
        $pad = ord($s[$len - 1]);
        return substr($s, 0, $len - $pad);
    }

} //class

//array2prop.class.php
namespace litepubl;

class tarray2prop {
    public $array;
    public function __construct(array $a = null) {
        $this->array = $a;
    }
    public function __get($name) {
        return $this->array[$name];
    }
    public function __set($name, $value) {
        $this->array[$name] = $value;
    }
    public function __isset($name) {
        return array_key_exists($name, $this->array);
    }
    public function __tostring() {
        return $this->array[''];
    }
} //class

//utils.functions.php
namespace litepubl;

function sqldate($date = 0) {
    if ($date == 0) $date = time();
    return date('Y-m-d H:i:s', $date);
}

function sqltime($date = 0) {
    if ($date == 0) return '0000-00-00 00:00:00';
    return date('Y-m-d H:i:s', $date);
}

function dbquote($s) {
    return litepubl::$db->quote($s);
}

function md5rand() {
    return md5(mt_rand() . litepubl::$secret . microtime());
}

function md5uniq() {
    return basemd5(mt_rand() . litepubl::$secret . microtime());
}

function basemd5($s) {
    return trim(base64_encode(md5($s, true)) , '=');
}

function strbegin($s, $begin) {
    return strncmp($s, $begin, strlen($begin)) == 0;
}

function strbegins() {
    $a = func_get_args();
    $s = array_shift($a);
    while ($begin = array_shift($a)) {
        if (strncmp($s, $begin, strlen($begin)) == 0) return true;
    }
    return false;
}

function strend($s, $end) {
    return $end == substr($s, 0 - strlen($end));
}

function strip_utf($s) {
    $utf = "\xEF\xBB\xBF";
    return strbegin($s, $utf) ? substr($s, strlen($utf)) : $s;
}

function array_delete(array & $a, $i) {
    array_splice($a, $i, 1);
}

function array_delete_value(array & $a, $value) {
    $i = array_search($value, $a);
    if ($i !== false) {
        array_splice($a, $i, 1);
        return true;
    }

    return false;
}

function array_clean(array & $items) {
    $items = array_unique($items);
    foreach (array(
        0,
        false,
        null,
        ''
    ) as $v) {
        $i = array_search($v, $items);
        if (($i !== false) && ($items[$i] === $v)) {
            array_splice($items, $i, 1);
        }
    }
}

function array_insert(array & $a, $item, $index) {
    array_splice($a, $index, 0, array(
        $item
    ));
}

function array_move(array & $a, $oldindex, $newindex) {
    //delete and insert
    if (($oldindex == $newindex) || !isset($a[$oldindex])) return false;
    $item = $a[$oldindex];
    array_splice($a, $oldindex, 1);
    array_splice($a, $newindex, 0, array(
        $item
    ));
}

function strtoarray($s) {
    $a = explode("\n", trim($s));
    foreach ($a as $k => $v) $a[$k] = trim($v);
    return $a;
}

function tojson($a) {
    if (defined('JSON_NUMERIC_CHECK')) {
        return json_encode($a, JSON_NUMERIC_CHECK | (defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0));
    }

    return json_encode($a);
}

function jsonattr($a) {
    return str_replace('"', '&quot;', tojson($a));
}

function toenum($v, array $a) {
    $v = trim($v);
    return in_array($v, $a) ? $v : $a[0];
}

function dumpstr($s) {
    echo "<pre>\n", htmlspecialchars($s) , "</pre>\n";
}

function dumpvar($v) {
    echo "<pre>\n";
    var_dump($v);
    echo "</pre>\n";
}

//getter.class.php
namespace litepubl;

class getter {
    public $get;
    public $set;

    public function __construct($get = null, $set = null) {
        $this->get = $get;
        $this->set = $set;
    }

    public function __get($name) {
        return call_user_func_array($this->get, array(
            $name
        ));
    }

    public function __set($name, $value) {
        call_user_func_array($this->set, array(
            $name,
            $value
        ));
    }

}

//events.class.php
namespace litepubl;

class tevents extends tdata {
    protected $events;
    protected $eventnames;
    protected $map;

    public function __construct() {
        if (!is_array($this->eventnames)) {
            $this->eventnames = array();
        }

        if (!is_array($this->map)) {
            $this->map = array();
        }

        parent::__construct();

        $this->assignmap();
        $this->load();
    }

    public function __destruct() {
        unset($this->data, $this->events, $this->eventnames, $this->map);
    }

    protected function create() {
        $this->addmap('events', array());
        $this->addmap('coclasses', array());
    }

    public function assignmap() {
        foreach ($this->map as $propname => $key) {
            $this->$propname = & $this->data[$key];
        }
    }

    public function afterload() {
        $this->assignmap();

        foreach ($this->coclasses as $coclass) {
            $this->coinstances[] = getinstance($coclass);
        }

        parent::afterload();
    }

    protected function addmap($name, $value) {
        $this->map[$name] = $name;
        $this->data[$name] = $value;
        $this->$name = & $this->data[$name];
    }

    public function free() {
        unset(litepubl::$classes->instances[get_class($this) ]);
        foreach ($this->coinstances as $coinstance) {
            $coinstance->free();
        }
    }

    public function eventexists($name) {
        return in_array($name, $this->eventnames);
    }

    public function __get($name) {
        if (method_exists($this, $name)) {
            return array(
                get_class($this) ,
                $name
            );
        }

        return parent::__get($name);
    }

    public function __set($name, $value) {
        if (parent::__set($name, $value)) {
            return true;
        }

        if (in_array($name, $this->eventnames)) {
            $this->addevent($name, $value[0], $value[1]);
            return true;
        }
        $this->error(sprintf('Unknown property %s in class %s', $name, get_class($this)));
    }

    public function method_exists($name) {
        return in_array($name, $this->eventnames);
    }

    public function __call($name, $params) {
        if (in_array($name, $this->eventnames)) {
            return $this->callevent($name, $params);
        }

        parent::__call($name, $params);
    }

    public function __isset($name) {
        return parent::__isset($name) || in_array($name, $this->eventnames);
    }

    protected function addevents() {
        $a = func_get_args();
        array_splice($this->eventnames, count($this->eventnames) , 0, $a);
    }

    public function callevent($name, $params) {
        if (!isset($this->events[$name])) {
            return '';
        }

        $result = '';
        foreach ($this->events[$name] as $i => $item) {
            //backward compability
            $class = isset($item[0]) ? $item[0] : (isset($item['class']) ? $item['class'] : '');

            if (is_string($class) && class_exists($class)) {
                $call = array(
                    getinstance($class) ,
                    isset($item[1]) ? $item[1] : $item['func']
                );
            } elseif (is_object($class)) {
                $call = array(
                    $class,
                    isset($item[1]) ? $item[1] : $item['func']
                );
            } else {
                $call = false;
            }

            if ($call) {
                try {
                    $result = call_user_func_array($call, $params);
                }
                catch(ECancelEvent $e) {
                    return $e->result;
                }

                // 2 index = once
                if (isset($item[2]) && $item[2]) {
                    array_splice($this->events[$name], $i, 1);
                }

            } else {
                //class not found and delete event handler
                array_splice($this->events[$name], $i, 1);
                if (!count($this->events[$name])) {
                    unset($this->events[$name]);
                }

                $this->save();
            }
        }

        return $result;
    }

    public static function cancelevent($result) {
        throw new ECancelEvent($result);
    }

    public function setevent($name, $params) {
        return $this->addevent($name, $params['class'], $params['func']);
    }

    public function addevent($name, $class, $func, $once = false) {
        if (!in_array($name, $this->eventnames)) {
            return $this->error(sprintf('No such %s event', $name));
        }

        if (empty($class)) {
            $this->error("Empty class name to bind $name event");
        }

        if (empty($func)) {
            $this->error("Empty function name to bind $name event");
        }

        if (!isset($this->events[$name])) {
            $this->events[$name] = array();
        }

        //check if event already added
        foreach ($this->events[$name] as $event) {
            if (isset($event[0]) && $event[0] == $class && $event[1] == $func) {
                return false;
                //backward compability
                
            } elseif (isset($event['class']) && $event['class'] == $class && $event['func'] == $func) {
                return false;
            }
        }

        if ($once) {
            $this->events[$name][] = array(
                $class,
                $func,
                true
            );
        } else {
            $this->events[$name][] = array(
                $class,
                $func
            );
            $this->save();
        }
    }

    public function delete_event_class($name, $class) {
        if (!isset($this->events[$name])) {
            return false;
        }

        $list = & $this->events[$name];
        $deleted = false;
        for ($i = count($list) - 1; $i >= 0; $i--) {
            $event = $list[$i];

            if ((isset($event[0]) && $event[0] == $class) ||
            //backward compability
            (isset($event['class']) && $event['class'] == $class)) {
                array_splice($list, $i, 1);
                $deleted = true;
            }
        }

        if ($deleted) {
            if (count($list) == 0) {
                unset($this->events[$name]);
            }

            $this->save();
        }

        return $deleted;
    }

    public function unsubscribeclass($obj) {
        $this->unbind($obj);
    }

    public function unsubscribeclassname($class) {
        $this->unbind($class);
    }

    public function unbind($c) {
        $class = static ::get_class_name($c);
        foreach ($this->events as $name => $events) {
            foreach ($events as $i => $item) {
                if ((isset($item[0]) && $item[0] == $class) || (isset($item['class']) && $item['class'] == $class)) {
                    array_splice($this->events[$name], $i, 1);
                }
            }
        }

        $this->save();
    }

    public function seteventorder($eventname, $c, $order) {
        if (!isset($this->events[$eventname])) {
            return false;
        }

        $events = & $this->events[$eventname];
        $class = static ::get_class_name($c);
        $count = count($events);
        if (($order < 0) || ($order >= $count)) {
            $order = $count - 1;
        }

        foreach ($events as $i => $event) {
            if ((isset($event[0]) && $class == $event[0]) || (isset($event['class']) && $class == $event['class'])) {
                if ($i == $order) {
                    return true;
                }

                array_splice($events, $i, 1);
                array_splice($events, $order, 0, array(
                    0 => $event
                ));

                $this->save();
                return true;
            }
        }
    }

    private function indexofcoclass($class) {
        return array_search($class, $this->coclasses);
    }

    public function addcoclass($class) {
        if ($this->indexofcoclass($class) === false) {
            $this->coclasses[] = $class;
            $this->save();
            $this->coinstances = getinstance($class);
        }
    }

    public function deletecoclass($class) {
        $i = $this->indexofcoclass($class);
        if (is_int($i)) {
            array_splice($this->coclasses, $i, 1);
            $this->save();
        }
    }

} //class

//events.exception.class.php
namespace litepubl;

class ECancelEvent extends \Exception {
    public $result;

    public function __construct($message, $code = 0) {
        $this->result = $message;
        parent::__construct('', 0);
    }
}

//events.coclass.php
namespace litepubl;

class tcoevents extends tevents {
    protected $owner;
    protected $callbacks;

    public function __construct() {
        $args = func_get_args();
        if (isset($args[0])) {
            if (is_array($args[0])) {
                $this->callbacks = array_shift($args);
                $this->trigger_callback('construct');
            } else if (($owner = array_shift($args)) && is_object($owner) && ($owner instanceof tdata)) {
                $this->setowner($owner);
            }
        }

        if (is_array($this->eventnames)) {
            array_splice($this->eventnames, count($this->eventnames) , 0, $args);
        } else {
            $this->eventnames = $args;
        }

        parent::__construct();
    }

    public function setowner(tdata $owner) {
        $this->owner = $owner;
        if (!isset($owner->data['events'])) {
            $owner->data['events'] = array();
        }

        $this->events = & $owner->data['events'];
    }

    public function trigger_callback($name) {
        if (isset($this->callbacks[$name])) {
            $callback = $this->callbacks[$name];
            if (is_callable($callback)) {
                $callback($this);
            }
        }
    }

    public function __destruct() {
        parent::__destruct();
        unset($this->owner, $this->callbacks);
    }

    public function assignmap() {
        if (!$this->owner) {
            parent::assignmap();
        }

        $this->trigger_callback('assignmap');
    }

    protected function create() {
        if (!$this->owner) {
            parent::create();
        }

        $this->trigger_callback('create');
    }

    public function load() {
        if (!$this->owner) {
            return parent::load();
        }
    }

    public function afterload() {
        if ($this->owner) {
            $this->events = & $this->owner->data['events'];
        } else {
            parent::afterload();
        }

        $this->trigger_callback('afterload');
    }

    public function save() {
        if ($this->owner) {
            return $this->owner->save();
        } else {
            return parent::save();
        }
    }

    public function inject_events() {
        $a = func_get_args();
        array_splice($this->eventnames, count($this->eventnames) , 0, $a);
    }

} //class

//events.storage.class.php
namespace litepubl;

class tevents_storage extends tevents {

    public function getstorage() {
        return litepubl::$datastorage;
    }

} //class

//items.class.php
namespace litepubl;

class titems extends tevents {
    public $items;
    public $dbversion;
    protected $idprop;
    protected $autoid;

    protected function create() {
        parent::create();
        $this->addevents('added', 'deleted');
        $this->idprop = 'id';
        if ($this->dbversion) {
            $this->items = array();
        } else {
            $this->addmap('items', array());
            $this->addmap('autoid', 0);
        }
    }

    public function load() {
        if ($this->dbversion) {
            return litepubl::$datastorage->load($this);
        } else {
            return parent::load();
        }
    }

    public function save() {
        if ($this->lockcount > 0) {
            return;
        }

        if ($this->dbversion) {
            return litepubl::$datastorage->save($this);
        } else {
            return parent::save();
        }
    }

    public function loadall() {
        if ($this->dbversion) {
            return $this->select('', '');
        }
    }

    public function loaditems(array $items) {
        if (!$this->dbversion) {
            return;
        }

        //exclude loaded items
        $items = array_diff($items, array_keys($this->items));
        if (count($items) == 0) {
            return;
        }

        $list = implode(',', $items);
        $this->select("$this->thistable.$this->idprop in ($list)", '');
    }

    public function select($where, $limit) {
        if (!$this->dbversion) {
            $this->error('Select method must be called ffrom database version');
        }

        if ($where) {
            $where = 'where ' . $where;
        }

        return $this->res2items($this->db->query("SELECT * FROM $this->thistable $where $limit"));
    }

    public function res2items($res) {
        if (!$res) {
            return array();
        }

        $result = array();
        $db = litepubl::$db;
        while ($item = $db->fetchassoc($res)) {
            $id = $item[$this->idprop];
            $result[] = $id;
            $this->items[$id] = $item;
        }

        return $result;
    }

    public function finditem($where) {
        $a = $this->select($where, 'limit 1');
        return count($a) ? $a[0] : false;
    }

    public function getcount() {
        if ($this->dbversion) {
            return $this->db->getcount();
        } else {
            return count($this->items);
        }
    }

    public function getitem($id) {
        if (isset($this->items[$id])) {
            return $this->items[$id];
        }

        if ($this->dbversion && $this->select("$this->thistable.$this->idprop = $id", 'limit 1')) {
            return $this->items[$id];
        }

        return $this->error(sprintf('Item %d not found in class %s', $id, get_class($this)));
    }

    public function getvalue($id, $name) {
        if ($this->dbversion && !isset($this->items[$id])) {
            $this->items[$id] = $this->db->getitem($id, $this->idprop);
        }

        return $this->items[$id][$name];
    }

    public function setvalue($id, $name, $value) {
        $this->items[$id][$name] = $value;
        if ($this->dbversion) {
            $this->db->update("$name = " . dbquote($value) , "$this->idprop = $id");
        }
    }

    public function itemexists($id) {
        if (isset($this->items[$id])) {
            return true;
        }

        if ($this->dbversion) {
            try {
                return $this->getitem($id);
            }
            catch(Exception $e) {
                return false;
            }
        }
        return false;
    }

    public function indexof($name, $value) {
        if ($this->dbversion) {
            return $this->db->findprop($this->idprop, "$name = " . dbquote($value));
        }

        foreach ($this->items as $id => $item) {
            if ($item[$name] == $value) {
                return $id;
            }
        }
        return false;
    }

    public function additem(array $item) {
        $id = $this->dbversion ? $this->db->add($item) : ++$this->autoid;
        $item[$this->idprop] = $id;
        $this->items[$id] = $item;
        if (!$this->dbversion) {
            $this->save();
        }

        $this->added($id);
        return $id;
    }

    public function delete($id) {
        if ($this->dbversion) {
            $this->db->delete("$this->idprop = $id");
        }

        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            if (!$this->dbversion) {
                $this->save();
            }

            $this->deleted($id);
            return true;
        }
        return false;
    }

} //class

//items.storage.class.php
namespace litepubl;

class titems_storage extends titems {

    public function getstorage() {
        return litepubl::$datastorage;
    }

} //class

//items.single.class.php
namespace litepubl;

class tsingleitems extends titems {
    public $copyprops;
    public static $instances;

    protected function create() {
        $this->dbversion = false;
        parent::create();
        $this->copyprops = array();
    }

    public function addinstance($instance) {
        $classname = get_class($instance);
        $item = array(
            'classname' => $classname,
        );

        foreach ($this->copyprops as $prop) {
            $item[$prop] = $instance->{$prop};
        }

        $id = $this->additem($item);
        $instance->id = $id;
        $instance->save();

        if (isset(static ::$instances[$classname])) {
            static ::$instances[$classname][$id] = $instance;
        } else {
            static ::$instances[$classname] = array(
                $id => $instance
            );
        }

        return $id;
    }

    public function get($id) {
        $id = (int)$id;
        $classname = $this->items[$id]['classname'];
        $result = getinstance($classname);
        if ($id != $result->id) {
            if (!isset(static ::$instances[$classname])) {
                static ::$instances[$classname] = array();
            }

            if (isset(static ::$instances[$classname][$id])) {
                $result = static ::$instances[$classname][$id];
            } else {
                if ($result->id) {
                    $result = new $classname();
                }

                $result->id = $id;
                $result->load();
                static ::$instances[$classname][$id] = $result;
            }
        }

        return $result;
    }

} //class

//item.class.php
namespace litepubl;

class titem extends tdata {
    public static $instances;

    public static function i($id = 0) {
        return static ::iteminstance(get_called_class() , (int)$id);
    }

    public static function iteminstance($class, $id = 0) {
        //fix namespace
        if (!strpos($class, '\\') && !class_exists($class)) {
            $class = 'litepubl\\' . $class;
        }

        $name = call_user_func_array(array(
            $class,
            'getinstancename'
        ) , array());

        if (!isset(static ::$instances)) {
            static ::$instances = array();
        }

        if (isset(static ::$instances[$name][$id])) {
            return static ::$instances[$name][$id];
        }

        $self = litepubl::$classes->newitem($name, $class, $id);
        return $self->loaddata($id);
    }

    public function loaddata($id) {
        $this->data['id'] = $id;
        if ($id != 0) {
            if (!$this->load()) {
                $this->free();
                return false;
            }
            static ::$instances[$this->instancename][$id] = $this;
        }
        return $this;
    }

    public function free() {
        unset(static ::$instances[$this->getinstancename() ][$this->id]);
    }

    public function __construct() {
        parent::__construct();
        $this->data['id'] = 0;
    }

    public function __destruct() {
        $this->free();
    }

    public function __set($name, $value) {
        if (parent::__set($name, $value)) return true;
        return $this->Error("Field $name not exists in class " . get_class($this));
    }

    public function setid($id) {
        if ($id != $this->id) {
            $name = $this->instancename;
            if (!isset(static ::$instances)) static ::$instances = array();
            if (!isset(static ::$instances[$name])) static ::$instances[$name] = array();
            $a = & static ::$instances[$this->instancename];
            if (isset($a[$this->id])) unset($a[$this->id]);
            if (isset($a[$id])) $a[$id] = 0;
            $a[$id] = $this;
            $this->data['id'] = $id;
        }
    }

    public function request($id) {
        if ($id != $this->id) {
            $this->setid($id);
            if (!$this->load()) return 404;
        }
    }

    public static function deletedir($dir) {
        if (!@file_exists($dir)) return false;
        tfiler::delete($dir, true, true);
        @rmdir($dir);
    }

}

//item.storage.class.php
namespace litepubl;

class titem_storage extends titem {

    public function getowner() {
        $this->error(sprintf('The "%s" no have owner', get_class($this)));
    }

    public function load() {
        $owner = $this->owner;
        if ($owner->itemexists($this->id)) {
            $this->data = & $owner->items[$this->id];
            return true;
        }
        return false;
    }

    public function save() {
        return $this->owner->save();
    }

} //class

//classes.class.php
namespace litepubl;

class tclasses extends titems {
    public $namespaces;
    public $kernel;
    public $classes;
    public $remap;
    public $aliases;
    public $factories;
    public $instances;

    public static function i() {
        if (!isset(litepubl::$classes)) {
            $classname = get_called_class();
            litepubl::$classes = new $classname();
            litepubl::$classes->instances[$classname] = litepubl::$classes;
        }

        return litepubl::$classes;
    }

    protected function create() {
        parent::create();
        $this->basename = 'classes';
        $this->dbversion = false;
        $this->addevents('onnewitem', 'gettemplatevar', 'onrename');
        $this->addmap('namespaces', array());
        $this->addmap('kernel', array());
        $this->addmap('classes', array());
        $this->addmap('remap', array());
        $this->addmap('factories', array());
        $this->instances = array();
        $this->aliases = array();

        spl_autoload_register(array(
            $this,
            'autoload'
        ));
    }

    public function getstorage() {
        return litepubl::$datastorage;
    }

    public function getinstance($class) {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        if (isset($this->aliases[$class]) && ($alias = $this->aliases[$class]) && ($alias != $class)) {
            return $this->getinstance($alias);
        }

        if (!class_exists($class)) {
            $this->error(sprintf('Class "%s" not found', $class));
        }

        return $this->instances[$class] = $this->newinstance($class);
    }

    public function newinstance($class) {
        if (!empty($this->remap[$class])) {
            $class = $this->remap[$class];
        }

        return new $class();
    }

    public function newitem($name, $class, $id) {
        if (!empty($this->remap[$class])) $class = $this->remap[$class];
        $this->callevent('onnewitem', array(
            $name, &$class,
            $id
        ));
        return new $class();
    }

    public function __get($name) {
        if (isset($this->classes[$name])) {
            $result = $this->getinstance($this->classes[$name]);
        } else if (isset($this->items[$name])) {
            $result = $this->getinstance($name);
        } else if (isset($this->items['t' . $class])) {
            $result = $this->getinstance('t' . $class);
        } else {
            $result = parent::__get($name);
        }

        return $result;
    }

    public function add($class, $filename, $deprecatedPath = false) {
        if (isset($this->items[$class]) && ($this->items[$class] == $filename)) {
            return false;
        }

        $this->lock();
        if (!strpos($class, '\\')) {
            $class = 'litepubl\\' . $class;
            $filename = 'plugins/' . ($deprecatedPath ? $deprecatedPath . '/' : '') . $filename;
        }

        $this->items[$class] = $filename;
        $instance = $this->getinstance($class);
        if (method_exists($instance, 'install')) {
            $instance->install();
        }

        $this->unlock();
        $this->added($class);
        return true;
    }

    public function delete($class) {
        if (!isset($this->items[$class])) {
            return false;
        }

        $this->lock();
        if (class_exists($class)) {
            $instance = $this->getinstance($class);
            if (method_exists($instance, 'uninstall')) {
                $instance->uninstall();
            }
        }

        unset($this->items[$class]);
        unset($this->kernel[$class]);
        $this->unlock();
        $this->deleted($class);
    }

    public function reinstall($class) {
        if (isset($this->items[$class])) {
            $this->lock();
            $filename = $this->items[$class];
            $kernel = isset($this->kernel[$class]) ? $this->kernel[$class] : false;
            $this->delete($class);
            $this->add($class, $filename);
            if ($kernel) {
                $this->kernel[$class] = $kernel;
            }
            $this->unlock();
        }
    }

    public function baseclass($classname) {
        if ($i = strrpos($classname, '\\')) {
            return substr($classname, $i + 1);
        }

        return $classname;
    }

    public function addAlias($classname, $alias) {
        if (!$alias) {
            if ($i = strrpos($classname, '\\')) {
                $alias = substr($classname, $i + 1);
            } else {
                $alias = 'litepubl\\' . $classname;
            }
        }

        //may be exchange class names
        if (class_exists($alias, false) && !class_exists($classname, false)) {
            $tmp = $classname;
            $classname = $alias;
            $alias = $tmp;
        }

        if (!isset($this->aliases[$classname])) {
            class_alias($classname, $alias, false);
            $this->aliases[$classname] = $alias;
        }
    }

    public function autoload($classname) {
        if ($filename = $this->getpsr4($classname)) {
            $this->include($filename);
        } else if (!config::$useKernel || litepubl::$debug || !$this->includeKernel($classname)) {
            $this->includeClass($classname);
        }
    }

    public function includeClass($classname) {
        if (isset($this->items[$classname])) {
            $filename = litepubl::$paths->home . $this->items[$classname];
            $this->include_file($filename);
            $this->addAlias($classname, false);
        } else if (($subclass = $this->baseclass($classname)) && ($subclass != $classname) && isset($this->items[$subclass])) {
            $filename = litepubl::$paths->home . $this->items[$subclass];
            $this->include_file($filename);
            $this->addAlias($classname, $subclass);
        } else if (!strpos($classname, '\\') && isset($this->items['litepubl\\' . $classname])) {
            $filename = litepubl::$paths->home . $this->items['litepubl\\' . $classname];
            $this->include_file($filename);
            $this->addAlias('litepubl\\' . $classname, $classname);
        } else {
            return false;
        }

        return $filename;
    }

    public function includeKernel($classname) {
        if (isset($this->kernel[$classname])) {
            $filename = litepubl::$paths->home . $this->kernel[$classname];
            $this->include_file($filename);
            $this->addAlias($classname, false);
        } else if (($subclass = $this->baseclass($classname)) && ($subclass != $classname) && isset($this->kernel[$subclass])) {
            $filename = litepubl::$paths->home . $this->kernel[$subclass];
            $this->include_file($filename);
            $this->addAlias($classname, $subclass);
        } else if (!strpos($classname, '\\') && isset($this->kernel['litepubl\\' . $classname])) {
            $filename = litepubl::$paths->home . $this->kernel['litepubl\\' . $classname];
            $this->include_file($filename);
            $this->addAlias('litepubl\\' . $classname, $classname);
        } else {
            return false;
        }

        return $filename;
    }

    public function include ($filename) {
        require_once $filename;
    }

    public function include_file($filename) {
        if (file_exists($filename)) {
            $this->include($filename);
        }
    }

    public function getpsr4($classname) {
        if ($i = strrpos($classname, '\\')) {
            $ns = substr($classname, 0, $i);
            $baseclass = strtolower(substr($classname, $i + 1));

            if ($ns == 'litepubl') {
                $filename = litepubl::$paths->lib . $baseclass . '.php';
                if (file_exists($filename)) {
                    return $filename;
                }

                return false;
            }

            if (isset($this->namespaces[$ns])) {
                $filename = sprintf('%s%s/%s.php', litepubl::$paths->home, $this->namespaces[$ns], $baseclass);
                if (file_exists($filename)) {
                    return $filename;
                }
            }

            foreach ($this->namespaces as $name => $dir) {
                if (strbegin($ns, $name)) {
                    $filename = sprintf('%s%s%s/%s.php', litepubl::$paths->home, $this->namespaces[$name], substr($ns, strlen($name)) , $baseclass);
                    if (file_exists($filename)) {
                        return $filename;
                    }
                }
            }
        }

        return false;
    }

    public function exists($class) {
        return isset($this->items[$class]);
    }

    public function getfactory($instance) {
        foreach ($this->factories as $classname => $factory) {
            //fix namespace
            if (!strpos($classname, '\\')) {
                $classname = 'litepubl\\' . $classname;
            }

            if (is_a($instance, $classname)) {
                if (!strpos($factory, '\\')) {
                    $factory = 'litepubl\\' . $factory;
                }

                return $this->getinstance($factory);
            }
        }
    }

    public function rename($oldclass, $newclass) {
        if (isset($this->items[$oldclass])) {
            $this->items[$newclass] = $this->items[$oldclass];
            unset($this->items[$oldclass]);
            if (isset($this->kernel[$oldclass])) {
                $this->kernel[$newclass] = $this->items[$oldclass];
                unset($this->kernel[$oldclass]);
            }

            litepubl::$urlmap->db->update('class =' . dbquote($newclass) , 'class = ' . dbquote($oldclass));
            $this->save();
            $this->onrename($oldclass, $newclass);
        }
    }

    public function getresourcedir($c) {
        $reflector = new \ReflectionClass($c);
        $filename = $reflector->getFileName();
        return dirname($filename) . '/resource/';
    }

} //class

//classes.functions.php
namespace litepubl;

function getinstance($class) {
    return litepubl::$classes->getinstance($class);
}

//options.class.php
namespace litepubl;

class toptions extends tevents_storage {
    public $groupnames;
    public $parentgroups;
    public $group;
    public $idgroups;
    protected $_user;
    protected $_admincookie;
    public $gmt;
    public $errorlog;

    public static function i() {
        return getinstance(__class__);
    }

    public static function instance() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->basename = 'options';
        $this->addevents('changed', 'perpagechanged', 'onsave');
        unset($this->cache);
        $this->gmt = 0;
        $this->errorlog = '';
        $this->group = '';
        $this->idgroups = array();
        $this->addmap('groupnames', array());
        $this->addmap('parentgroups', array());
    }

    public function afterload() {
        parent::afterload();
        date_default_timezone_set($this->timezone);
        $this->gmt = date('Z');
        if (!defined('dbversion')) define('dbversion', true);
    }

    public function savemodified() {
        $result = $this->getstorage()->saveModified();
        $this->onsave($result);
        return $result;
    }

    public function __set($name, $value) {
        if (in_array($name, $this->eventnames)) {
            $this->addevent($name, $value['class'], $value['func']);
            return true;
        }

        if (method_exists($this, $set = 'set' . $name)) {
            $this->$set($value);
            return true;
        }

        if (!array_key_exists($name, $this->data) || ($this->data[$name] != $value)) {
            $this->data[$name] = $value;
            if ($name == 'solt') $this->data['emptyhash'] = $this->hash('');
            $this->save();
            $this->dochanged($name, $value);
        }
        return true;
    }

    private function dochanged($name, $value) {
        if ($name == 'perpage') {
            $this->perpagechanged();
            $urlmap = turlmap::i();
            $urlmap->clearcache();
        } elseif ($name == 'cache') {
            $urlmap = turlmap::i();
            $urlmap->clearcache();
        } else {
            $this->changed($name, $value);
        }
    }

    public function delete($name) {
        if (array_key_exists($name, $this->data)) {
            unset($this->data[$name]);
            $this->save();
        }
    }

    public function getadmincookie() {
        if (is_null($this->_admincookie)) {
            return $this->_admincookie = $this->authenabled && isset($_COOKIE['litepubl_user_flag']) && ($_COOKIE['litepubl_user_flag'] == 'true');
        }
        return $this->_admincookie;
    }

    public function setadmincookie($val) {
        $this->_admincookie = $val;
    }

    public function getuser() {
        if (is_null($this->_user)) {
            $this->_user = $this->authenabled ? $this->authcookie() : false;
        }

        return $this->_user;
    }

    public function setuser($id) {
        $this->_user = $id;
    }

    public function authcookie() {
        return $this->authcookies(isset($_COOKIE['litepubl_user_id']) ? (int)$_COOKIE['litepubl_user_id'] : 0, isset($_COOKIE['litepubl_user']) ? (string)$_COOKIE['litepubl_user'] : '');
    }

    public function authcookies($iduser, $password) {
        if (!$iduser || !$password) return false;
        $password = $this->hash($password);
        if ($password == $this->emptyhash) return false;
        if (!$this->finduser($iduser, $password)) return false;

        $this->_user = $iduser;
        $this->updategroup();
        return $iduser;
    }

    public function finduser($iduser, $cookie) {
        if ($iduser == 1) return $this->compare_cookie($cookie);
        if (!$this->usersenabled) return false;

        $users = tusers::i();
        try {
            $item = $users->getitem($iduser);
        }
        catch(Exception $e) {
            return false;
        }

        if ('hold' == $item['status']) return false;
        return ($cookie == $item['cookie']) && (strtotime($item['expired']) > time());
    }

    private function compare_cookie($cookie) {
        return !empty($this->cookiehash) && ($this->cookiehash == $cookie) && ($this->cookieexpired > time());
    }

    public function emailexists($email) {
        if (!$email) return false;
        if (!$this->authenabled) return false;
        if ($email == $this->email) return 1;
        if (!$this->usersenabled) return false;
        return tusers::i()->emailexists($email);
    }

    public function auth($email, $password) {
        if (!$this->authenabled) return false;
        if (!$email && !$password) return $this->authcookie();
        return $this->authpassword($this->emailexists($email) , $password);
    }

    public function authpassword($iduser, $password) {
        if (!$iduser) return false;
        if ($iduser == 1) {
            if ($this->data['password'] != $this->hash($password)) return false;
        } else {
            if (!tusers::i()->authpassword($iduser, $password)) return false;
        }

        $this->_user = $iduser;
        $this->updategroup();
        return $iduser;
    }

    public function updategroup() {
        if ($this->_user == 1) {
            $this->group = 'admin';
            $this->idgroups = array(
                1
            );
        } else {
            $user = tusers::i()->getitem($this->_user);
            $this->idgroups = $user['idgroups'];
            $this->group = count($this->idgroups) ? tusergroups::i()->items[$this->idgroups[0]]['name'] : '';
        }
    }

    public function can_edit($idauthor) {
        return ($idauthor == $this->user) || ($this->group == 'admin') || ($this->group == 'editor');
    }

    public function getpassword() {
        if ($this->user <= 1) return $this->data['password'];
        $users = tusers::i();
        return $users->getvalue($this->user, 'password');
    }

    public function changepassword($newpassword) {
        $this->data['password'] = $this->hash($newpassword);
        $this->save();
    }

    public function getdbpassword() {
        if (function_exists('mcrypt_encrypt')) {
            return static ::decrypt($this->data['dbconfig']['password'], $this->solt . litepubl::$secret);
        } else {
            return str_rot13(base64_decode($this->data['dbconfig']['password']));
        }
    }

    public function setdbpassword($password) {
        if (function_exists('mcrypt_encrypt')) {
            $this->data['dbconfig']['password'] = static ::encrypt($password, $this->solt . litepubl::$secret);
        } else {
            $this->data['dbconfig']['password'] = base64_encode(str_rot13($password));
        }

        $this->save();
    }

    public function logout() {
        $this->setcookies('', 0);
    }

    public function setcookie($name, $value, $expired) {
        setcookie($name, $value, $expired, litepubl::$site->subdir . '/', false, '', $this->securecookie);
    }

    public function setcookies($cookie, $expired) {
        $this->setcookie('litepubl_user_id', $cookie ? $this->_user : '', $expired);
        $this->setcookie('litepubl_user', $cookie, $expired);
        $this->setcookie('litepubl_user_flag', $cookie && ('admin' == $this->group) ? 'true' : '', $expired);

        if ($this->_user == 1) {
            $this->save_cookie($cookie, $expired);
        } else if ($this->_user) {
            tusers::i()->setcookie($this->_user, $cookie, $expired);
        }
    }

    public function Getinstalled() {
        return isset($this->data['email']);
    }

    public function settimezone($value) {
        if (!isset($this->data['timezone']) || ($this->timezone != $value)) {
            $this->data['timezone'] = $value;
            $this->save();
            date_default_timezone_set($this->timezone);
            $this->gmt = date('Z');
        }
    }

    public function save_cookie($cookie, $expired) {
        $this->data['cookiehash'] = $cookie ? $this->hash($cookie) : '';
        $this->cookieexpired = $expired;
        $this->save();
    }

    public function hash($s) {
        return basemd5((string)$s . $this->solt . litepubl::$secret);
    }

    public function ingroup($groupname) {
        //admin has all rights
        if ($this->user == 1) return true;
        if (in_array($this->groupnames['admin'], $this->idgroups)) return true;
        if (!$groupname) return true;
        $groupname = trim($groupname);
        if ($groupname == 'admin') return false;
        if (!isset($this->groupnames[$groupname])) $this->error(sprintf('The "%s" group not found', $groupname));
        $idgroup = $this->groupnames[$groupname];
        return in_array($idgroup, $this->idgroups);
    }

    public function ingroups(array $idgroups) {
        if ($this->ingroup('admin')) return true;
        return count(array_intersect($this->idgroups, $idgroups));
    }

    public function hasgroup($groupname) {
        if ($this->ingroup($groupname)) return true;
        // if group is children of user groups
        $idgroup = $this->groupnames[$groupname];
        if (!isset($this->parentgroups[$idgroup])) return false;
        return count(array_intersect($this->idgroups, $this->parentgroups[$idgroup]));
    }

    public function handexception($e) {
        $log = "Caught exception:\r\n" . $e->getMessage() . "\r\n";
        $trace = $e->getTrace();
        foreach ($trace as $i => $item) {
            if (isset($item['line'])) {
                $log.= sprintf('#%d %d %s ', $i, $item['line'], $item['file']);
            }

            if (isset($item['class'])) {
                $log.= $item['class'] . $item['type'] . $item['function'];
            } else {
                $log.= $item['function'];
            }

            if (isset($item['args']) && count($item['args'])) {
                $args = array();
                foreach ($item['args'] as $arg) {
                    $args[] = static ::var_export($arg);
                }

                $log.= "\n";
                $log.= implode(', ', $args);
            }

            $log.= "\n";
        }

        $log = str_replace(litepubl::$paths->home, '', $log);
        $this->errorlog.= str_replace("\n", "<br />\n", htmlspecialchars($log));
        tfiler::log($log, 'exceptions.log');

        if (!(litepubl::$debug || $this->echoexception || $this->admincookie || litepubl::$urlmap->adminpanel)) {
            tfiler::log($log, 'exceptionsmail.log');
        }
    }

    public function trace($msg) {
        try {
            throw new Exception($msg);
        }
        catch(Exception $e) {
            $this->handexception($e);
        }
    }

    public function showerrors() {
        if (!empty($this->errorlog) && (litepubl::$debug || $this->echoexception || $this->admincookie || litepubl::$urlmap->adminpanel)) {
            echo $this->errorlog;
        }
    }

    public static function var_export(&$v) {
        switch (gettype($v)) {
            case 'string':
                return "'$v'";

            case 'object':
                return get_class($v);

            case 'boolean':
                return $v ? 'true' : 'false';

            case 'integer':
            case 'double':
            case 'float':
                return $v;

            case 'array':
                $result = "array (\n";
                foreach ($v as $k => $item) {
                    $s = static ::var_export($item);
                    $result.= "$k = $s;\n";
                }
                $result.= ")\n";
                return $result;

            default:
                return gettype($v);
        }
    }

} //class

//site.class.php
namespace litepubl;

class tsite extends tevents_storage {
    public $mapoptions;
    private $users;

    public static function i() {
        return getinstance(__class__);
    }

    public static function instance() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->basename = 'site';
        $this->addmap('mapoptions', array(
            'version' => 'version',
            'language' => 'language',
        ));
    }

    public function __get($name) {
        if (isset($this->mapoptions[$name])) {
            $prop = $this->mapoptions[$name];
            if (is_array($prop)) {
                list($classname, $method) = $prop;
                return call_user_func_array(array(
                    getinstance($classname) ,
                    $method
                ) , array(
                    $name
                ));
            }

            return litepubl::$options->data[$prop];
        }

        return parent::__get($name);
    }

    public function __set($name, $value) {
        if ($name == 'url') return $this->seturl($value);
        if (in_array($name, $this->eventnames)) {
            $this->addevent($name, $value['class'], $value['func']);
        } elseif (isset($this->mapoptions[$name])) {
            $prop = $this->mapoptions[$name];
            if (is_string($prop)) litepubl::$options->{$prop} = $value;
        } elseif (!array_key_exists($name, $this->data) || ($this->data[$name] != $value)) {
            $this->data[$name] = $value;
            $this->save();
        }
        return true;
    }

    public function geturl() {
        if ($this->fixedurl) return $this->data['url'];
        return 'http://' . litepubl::$domain;
    }

    public function getfiles() {
        if ($this->fixedurl) return $this->data['files'];
        return 'http://' . litepubl::$domain;
    }

    public function seturl($url) {
        $url = rtrim($url, '/');
        $this->data['url'] = $url;
        $this->data['files'] = $url;
        $this->subdir = '';
        if ($i = strpos($url, '/', 10)) {
            $this->subdir = substr($url, $i);
        }
        $this->save();
    }

    public function getdomain() {
        return litepubl::$domain;
    }

    public function getuserlink() {
        if ($id = litepubl::$options->user) {
            if (!isset($this->users)) $this->users = array();
            if (isset($this->users[$id])) return $this->users[$id];
            $item = tusers::i()->getitem($id);
            if ($item['website']) {
                $result = sprintf('<a href="%s">%s</a>', $item['website'], $item['name']);
            } else {
                $page = $this->getdb('userpage')->getitem($id);
                if ((int)$page['idurl']) {
                    $result = sprintf('<a href="%s%s">%s</a>', $this->url, litepubl::$urlmap->getvalue($page['idurl'], 'url') , $item['name']);
                } else {
                    $result = $item['name'];
                }
            }
            $this->users[$id] = $result;
            return $result;
        }
        return '';
    }

    public function getliveuser() {
        return '<?php echo litepubl::$site->getuserlink(); ?>';
    }

} //class

//urlmap.class.php
namespace litepubl;

class turlmap extends titems {
    public $host;
    public $url;
    public $page;
    public $uripath;
    public $itemrequested;
    public $context;
    public $cache_enabled;
    public $is404;
    public $isredir;
    public $adminpanel;
    public $prefilter;
    protected $close_events;

    public function __construct() {
        parent::__construct();
        if (litepubl::$memcache) {
            $this->cache = new cachestorage_memcache();
        } else {
            $this->cache = new cachestorage_file();
        }
    }

    protected function create() {
        $this->dbversion = true;
        parent::create();
        $this->table = 'urlmap';
        $this->basename = 'urlmap';
        $this->addevents('beforerequest', 'afterrequest', 'onclearcache');
        $this->data['disabledcron'] = false;
        $this->data['redirdom'] = false;
        $this->addmap('prefilter', array());

        $this->is404 = false;
        $this->isredir = false;
        $this->adminpanel = false;
        $this->cache_enabled = litepubl::$options->cache && !litepubl::$options->admincookie;
        $this->page = 1;
        $this->close_events = array();
    }

    protected function prepareurl($host, $url) {
        $this->host = $host;
        $this->page = 1;
        $this->uripath = array();
        if (litepubl::$site->q == '?') {
            $this->url = substr($url, strlen(litepubl::$site->subdir));
        } else {
            $this->url = $_GET['url'];
        }
    }

    public function request($host, $url) {
        $this->prepareurl($host, $url);
        $this->adminpanel = strbegin($this->url, '/admin/') || ($this->url == '/admin');
        if ($this->redirdom) {
            $parsedurl = parse_url(litepubl::$site->url . '/');
            if ($host != strtolower($parsedurl['host'])) {
                return $this->redir($url);
            }
        }

        $this->beforerequest();
        if (!litepubl::$debug && litepubl::$options->ob_cache) {
            ob_start();
        }

        try {
            $this->dorequest($this->url);
        }
        catch(\Exception $e) {
            litepubl::$options->handexception($e);
        }

        // production mode: no debug and enabled buffer
        if (!litepubl::$debug && litepubl::$options->ob_cache) {
            litepubl::$options->showerrors();
            litepubl::$options->errorlog = '';

            $afterclose = $this->isredir || count($this->close_events);
            if ($afterclose) {
                $this->close_connection();
            }

            while (@ob_end_flush());
            flush();

            if ($afterclose) {
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }

                ob_start();
            }
        }

        $this->afterrequest($this->url);
        $this->close();
    }

    public function close_connection() {
        ignore_user_abort(true);
        $len = ob_get_length();
        header('Connection: close');
        header('Content-Length: ' . $len);
        header('Content-Encoding: none');
    }

    protected function dorequest($url) {
        $this->itemrequested = $this->find_item($url);

        if ($this->isredir) {
            return;
        }

        if ($this->itemrequested) {
            return $this->printcontent($this->itemrequested);
        } else {
            $this->notfound404();
        }
    }

    public function getidurl($id) {
        if (!isset($this->items[$id])) {
            $this->items[$id] = $this->db->getitem($id);
        }
        return $this->items[$id]['url'];
    }

    public function findurl($url) {
        if ($result = $this->db->finditem('url = ' . dbquote($url))) {
            return $result;
        }

        return false;
    }

    public function urlexists($url) {
        return $this->db->findid('url = ' . dbquote($url));
    }

    private function query($url) {
        if ($item = $this->findfilter($url)) {
            $this->items[$item['id']] = $item;
            return $item;
        } else if ($item = $this->db->getassoc('url = ' . dbquote($url) . ' limit 1')) {
            $this->items[$item['id']] = $item;
            return $item;
        }

        return false;
    }

    public function find_item($url) {
        if ($result = $this->query($url)) {
            return $result;
        }

        $srcurl = $url;
        if ($i = strpos($url, '?')) {
            $url = substr($url, 0, $i);
        }

        if ('//' == substr($url, -2)) {
            $this->redir(rtrim($url, '/') . '/');
        }

        //extract page number
        if (preg_match('/(.*?)\/page\/(\d*?)\/?$/', $url, $m)) {
            if ('/' != substr($url, -1)) {
                return $this->redir($url . '/');
            }

            $url = $m[1];
            if ($url == '') $url = '/';
            $this->page = max(1, abs((int)$m[2]));
        }

        if (($srcurl != $url) && ($result = $this->query($url))) {
            if (($this->page == 1) && ($result['type'] == 'normal') && ($srcurl != $result['url'])) {
                return $this->redir($result['url']);
            }

            return $result;
        }

        $url = $url != rtrim($url, '/') ? rtrim($url, '/') : $url . '/';
        if (($srcurl != $url) && ($result = $this->query($url))) {
            if (($this->page == 1) && ($result['type'] == 'normal') && ($srcurl != $result['url'])) {
                return $this->redir($result['url']);
            }

            return $result;
        }

        $this->uripath = explode('/', trim($url, '/'));
        return false;
    }

    public function findfilter($url) {
        foreach ($this->prefilter as $item) {
            switch ($item['type']) {
                case 'begin':
                    if (strbegin($url, $item['url'])) {
                        return $item;
                    }
                    break;


                case 'end':
                    if (strend($url, $item['url'])) {
                        return $item;
                    }
                    break;


                case 'regexp':
                    if (preg_match($item['url'], $url)) {
                        return $item;
                    }
                    break;
            }
        }

        return false;
    }

    public function updatefilter() {
        $this->prefilter = $this->db->getitems('type in (\'begin\', \'end\', \'regexp\')');
        $this->save();
    }

    private function getcachefile(array $item) {
        switch ($item['type']) {
            case 'normal':
                return sprintf('%s-%d.php', $item['id'], $this->page);

            case 'usernormal':
                return sprintf('%s-page-%d-user-%d.php', $item['id'], $this->page, litepubl::$options->user);

            case 'userget':
                return sprintf('%s-page-%d-user%d-get-%s.php', $item['id'], $this->page, litepubl::$options->user, md5($_SERVER['REQUEST_URI']));

            default: //get
                return sprintf('%s-%d-%s.php', $item['id'], $this->page, md5($_SERVER['REQUEST_URI']));
        }
    }

    protected function save_file($filename, $content) {
        $this->cache->setString($filename, $content);
    }

    protected function include_file($fn) {
        if (litepubl::$memcache) {
            if ($s = $this->cache->getString($fn)) {
                eval('?>' . $s);
                return true;
            }
            return false;
        }

        $filename = litepubl::$paths->cache . $fn;
        if (file_exists($filename) && ((filemtime($filename) + litepubl::$options->expiredcache - litepubl::$options->filetime_offset) >= time())) {
            include ($filename);
            return true;
        }

        return false;
    }

    private function printcontent(array $item) {
        $options = litepubl::$options;
        if ($this->cache_enabled && $this->include_file($this->getcachefile($item))) {
            return;
        }

        if (class_exists($item['class'])) {
            return $this->GenerateHTML($item);
        } else {
            $this->notfound404();
        }
    }

    public function getidcontext($id) {
        $item = $this->getitem($id);
        return $this->getcontext($item);
    }

    public function getcontext(array $item) {
        $classname = $item['class'];
        $parents = class_parents($classname);
        if (in_array('litepubl\titem', $parents)) {
            return call_user_func_array(array(
                $classname,
                'i'
            ) , array(
                $item['arg']
            ));
        } else {
            return litepubl::$classes->getinstance($classname);
        }
    }

    protected function GenerateHTML(array $item) {
        $context = $this->getcontext($item);
        $this->context = $context;

        //special handling for rss
        if (method_exists($context, 'request') && ($s = $context->request($item['arg']))) {
            switch ($s) {
                case 404:
                    return $this->notfound404();
                case 403:
                    return $this->forbidden();
            }
        } else {
            if ($this->isredir) {
                return;
            }

            $template = ttemplate::i();
            $s = $template->request($context);
        }

        eval('?>' . $s);
        if ($this->cache_enabled && $context->cache) {
            $this->save_file($this->getcachefile($item) , $s);
        }
    }

    public function notfound404() {
        $redir = tredirector::i();
        if ($url = $redir->get($this->url)) {
            return $this->redir($url);
        }

        $this->is404 = true;
        $this->printclasspage('litepubl\tnotfound404');
    }

    private function printclasspage($classname) {
        $cachefile = str_replace('\\', '_', $classname) . '.php';
        if ($this->cache_enabled && $this->include_file($cachefile)) {
            return;
        }

        $obj = litepubl::$classes->getinstance($classname);
        $Template = ttemplate::i();
        $s = $Template->request($obj);
        eval('?>' . $s);

        if ($this->cache_enabled && $obj->cache) {
            $this->cache->set($cachefile, $result);
        }
    }

    public function forbidden() {
        $this->is404 = true;
        $this->printclasspage('litepubl\tforbidden');
    }

    public function addget($url, $class) {
        return $this->add($url, $class, null, 'get');
    }

    public function add($url, $class, $arg, $type = 'normal') {
        if (empty($url)) $this->error('Empty url to add');
        if (empty($class)) $this->error('Empty class name of adding url');
        if (!in_array($type, array(
            'normal',
            'get',
            'usernormal',
            'userget',
            'begin',
            'end',
            'regexp'
        ))) {
            $this->error(sprintf('Invalid url type %s', $type));
        }

        if ($item = $this->db->finditem('url = ' . dbquote($url))) {
            $this->error(sprintf('Url "%s" already exists', $url));
        }

        $item = array(
            'url' => $url,
            'class' => $class,
            'arg' => (string)$arg,
            'type' => $type
        );

        $item['id'] = $this->db->add($item);
        $this->items[$item['id']] = $item;

        if (in_array($type, array(
            'begin',
            'end',
            'regexp'
        ))) {
            $this->prefilter[] = $item;
            $this->save();
        }

        return $item['id'];
    }

    public function delete($url) {
        $url = dbquote($url);
        if ($id = $this->db->findid('url = ' . $url)) {
            $this->db->iddelete($id);
        } else {
            return false;
        }

        foreach ($this->prefilter as $i => $item) {
            if ($id == $item['id']) {
                unset($this->prefilter[$i]);
                $this->save();
                break;
            }
        }

        $this->clearcache();
        $this->deleted($id);
        return true;
    }

    public function deleteclass($class) {
        if ($items = $this->db->getitems('class = ' . dbquote($class))) {
            foreach ($items as $item) {
                $this->db->iddelete($item['id']);
                $this->deleted($item['id']);
            }
        }

        $this->clearcache();
    }

    public function deleteitem($id) {
        if ($item = $this->db->getitem($id)) {
            $this->db->iddelete($id);
            $this->deleted($id);
        }
        $this->clearcache();
    }

    //for Archives
    public function GetClassUrls($class) {
        $res = $this->db->query("select url from $this->thistable where class = " . dbquote($class));
        return $this->db->res2id($res);
    }

    public function clearcache() {
        $this->cache->clear();
        $this->onclearcache();
    }

    public function setexpired($id) {
        if ($item = $this->getitem($id)) {
            $cache = $this->cache;
            $page = $this->page;
            for ($i = 1; $i <= 10; $i++) {
                $this->page = $i;
                $cache->delete($this->getcachefile($item));
            }
            $this->page = $page;
        }
    }

    public function setexpiredcurrent() {
        $this->cache->delete($this->getcachefile($this->itemrequested));
    }

    public function expiredclass($class) {
        $items = $this->db->getitems('class = ' . dbquote($class));
        if (!count($items)) {
            return;
        }

        $cache = $this->cache;
        $page = $this->page;
        $this->page = 1;
        foreach ($items as $item) {
            $cache->delete($this->getcachefile($item));
        }
        $this->page = $page;
    }

    public function addredir($from, $to) {
        if ($from == $to) {
            return;
        }

        $Redir = tredirector::i();
        $Redir->add($from, $to);
    }

    public static function unsub($obj) {
        $self = static ::i();
        $self->lock();
        $self->unbind($obj);
        $self->deleteclass(get_class($obj));
        $self->unlock();
    }

    public function setonclose(array $a) {
        if (count($a) == 0) {
            return;
        }

        $this->close_events[] = $a;
    }

    public function onclose() {
        $this->setonclose(func_get_args());
    }

    private function call_close_events() {
        foreach ($this->close_events as $a) {
            try {
                $c = array_shift($a);

                if (!is_callable($c)) {
                    $c = array(
                        $c,
                        array_shift($a)
                    );
                }

                call_user_func_array($c, $a);
            }
            catch(Exception $e) {
                litepubl::$options->handexception($e);
            }
        }

        $this->close_events = array();
    }

    protected function close() {
        $this->call_close_events();
        if ($this->disabledcron || ($this->context && (get_class($this->context) == 'litepubl\tcron'))) {
            return;
        }

        $memstorage = memstorage::i();
        if ($memstorage->hourcron + 3600 <= time()) {
            $memstorage->hourcron = time();
            $memstorage->singlecron = false;
            tcron::pingonshutdown();
        } else if ($memstorage->singlecron && ($memstorage->singlecron <= time())) {
            $memstorage->singlecron = false;
            tcron::pingonshutdown();
        }
    }

    public function redir($url, $status = 301) {
        litepubl::$options->savemodified();
        $this->isredir = true;

        switch ($status) {
            case 301:
                header('HTTP/1.1 301 Moved Permanently', true, 301);
                break;


            case 302:
                header('HTTP/1.1 302 Found', true, 302);
                break;


            case 307:
                header('HTTP/1.1 307 Temporary Redirect', true, 307);
                break;
        }

        if (!strbegin($url, 'http://') && !strbegin($url, 'https://')) $url = litepubl::$site->url . $url;
        header('Location: ' . $url);
    }

    public function seturlvalue($url, $name, $value) {
        if ($id = $this->urlexists($url)) {
            $this->setvalue($id, $name, $value);
        }
    }

    public function setidurl($id, $url) {
        $this->db->setvalue($id, 'url', $url);
        if (isset($this->items[$id])) $this->items[$id]['url'] = $url;
    }

    public function getnextpage() {
        $url = $this->itemrequested['url'];
        return litepubl::$site->url . rtrim($url, '/') . '/page/' . ($this->page + 1) . '/';
    }

    public function getprevpage() {
        $url = $this->itemrequested['url'];
        if ($this->page <= 2) {
            return url;
        }

        return litepubl::$site->url . rtrim($url, '/') . '/page/' . ($this->page - 1) . '/';
    }

    public static function htmlheader($cache) {
        return sprintf('<?php litepubl\turlmap::sendheader(%s); ?>', $cache ? 'true' : 'false');
    }

    public static function nocache() {
        Header('Cache-Control: no-cache, must-revalidate');
        Header('Pragma: no-cache');
    }

    public static function sendheader($cache) {
        if (!$cache) {
            static ::nocache();
        }

        header('Content-Type: text/html; charset=utf-8');
        header('Last-Modified: ' . date('r'));
        header('X-Pingback: ' . litepubl::$site->url . '/rpc.xml');
    }

    public static function sendxml() {
        header('Content-Type: text/xml; charset=utf-8');
        header('Last-Modified: ' . date('r'));
        header('X-Pingback: ' . litepubl::$site->url . '/rpc.xml');
        echo '<?xml version="1.0" encoding="utf-8" ?>';
    }

} //class

//itemplate.php
namespace litepubl;

interface itemplate {
    public function request($arg);
    public function gettitle();
    public function getkeywords();
    public function getdescription();
    public function gethead();
    public function getcont();
    public function getidview();
    public function setidview($id);
}

//interfaces.php
namespace litepubl;

interface iwidgets {
    public function getwidgets(array & $items, $sidebar);
    public function getsidebar(&$content, $sidebar);
}

interface iadmin {
    public function getcontent();
    public function processform();
}

interface iposts {
    public function add(tpost $post);
    public function edit(tpost $post);
    public function delete($id);
}

//plugin.class.php
namespace litepubl;

class tplugin extends tevents {

    protected function create() {
        parent::create();
        $this->basename = 'plugins/' . strtolower(str_replace('\\', '-', get_class($this)));
    }

    public function addClass($classname, $filename) {
        $ns = dirname(get_class($this));
        $reflector = new \ReflectionClass($class);
        $dir = dirname($reflector->getFileName());

        litepubl::$classes->add($ns . '\\' . $classname, $dir . '/' . $filename);
    }

}

//users.class.php
namespace litepubl;

class tusers extends titems {
    public $grouptable;

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        $this->dbversion = true;
        parent::create();
        $this->basename = 'users';
        $this->table = 'users';
        $this->grouptable = 'usergroup';
        $this->addevents('beforedelete');
    }

    public function res2items($res) {
        if (!$res) return array();
        $result = array();
        $db = litepubl::$db;
        while ($item = $db->fetchassoc($res)) {
            $id = (int)$item['id'];
            $item['idgroups'] = tdatabase::str2array($item['idgroups']);
            $result[] = $id;
            $this->items[$id] = $item;
        }
        return $result;
    }

    public function add(array $values) {
        return tusersman::i()->add($values);
    }

    public function edit($id, array $values) {
        return tusersman::i()->edit($id, $values);
    }

    public function setgroups($id, array $idgroups) {
        $idgroups = array_unique($idgroups);
        array_delete_value($idgroups, '');
        array_delete_value($idgroups, false);
        array_delete_value($idgroups, null);

        $this->items[$id]['idgroups'] = $idgroups;
        $db = $this->getdb($this->grouptable);
        $db->delete("iduser = $id");
        foreach ($idgroups as $idgroup) {
            $db->add(array(
                'iduser' => $id,
                'idgroup' => $idgroup
            ));
        }
    }

    public function delete($id) {
        if ($id == 1) return;
        $this->beforedelete($id);
        $this->getdb($this->grouptable)->delete('iduser = ' . (int)$id);
        tuserpages::i()->delete($id);
        $this->getdb('comments')->update("status = 'deleted'", "author = $id");
        return parent::delete($id);
    }

    public function emailexists($email) {
        if ($email == '') return false;
        if ($email == litepubl::$options->email) return 1;

        foreach ($this->items as $id => $item) {
            if ($email == $item['email']) return $id;
        }

        if ($item = $this->db->finditem('email = ' . dbquote($email))) {
            $id = (int)$item['id'];
            $this->items[$id] = $item;
            return $id;
        }

        return false;
    }

    public function getpassword($id) {
        return $id == 1 ? litepubl::$options->password : $this->getvalue($id, 'password');
    }

    public function changepassword($id, $password) {
        $item = $this->getitem($id);
        $this->setvalue($id, 'password', litepubl::$options->hash($item['email'] . $password));
    }

    public function approve($id) {
        $this->setvalue($id, 'status', 'approved');
        $pages = tuserpages::i();
        if ($pages->createpage) $pages->addpage($id);
    }

    public function auth($email, $password) {
        return $this->authpassword($this->emailexists($email) , $password);
    }

    public function authpassword($id, $password) {
        if (!$id || !$password) return false;
        $item = $this->getitem($id);
        if ($item['password'] == litepubl::$options->hash($item['email'] . $password)) {
            if ($item['status'] == 'wait') $this->approve($id);
            return $id;
        }
        return false;
    }

    public function authcookie($cookie) {
        $cookie = (string)$cookie;
        if (empty($cookie)) return false;
        $cookie = litepubl::$options->hash($cookie);
        if ($cookie == litepubl::$options->hash('')) return false;
        if ($id = $this->findcookie($cookie)) {
            $item = $this->getitem($id);
            if (strtotime($item['expired']) > time()) return $id;
        }
        return false;
    }

    public function findcookie($cookie) {
        $cookie = dbquote($cookie);
        if (($a = $this->select('cookie = ' . $cookie, 'limit 1')) && (count($a) > 0)) {
            return (int)$a[0];
        }
        return false;
    }

    public function getgroupname($id) {
        $item = $this->getitem($id);
        $groups = tusergroups::i();
        return $groups->items[$item['idgroups'][0]]['name'];
    }

    public function clearcookie($id) {
        $this->setcookie($id, '', 0);
    }

    public function setcookie($id, $cookie, $expired) {
        if ($cookie) $cookie = litepubl::$options->hash($cookie);
        $expired = sqldate($expired);
        if (isset($this->items[$id])) {
            $this->items[$id]['cookie'] = $cookie;
            $this->items[$id]['expired'] = $expired;
        }

        $this->db->updateassoc(array(
            'id' => $id,
            'cookie' => $cookie,
            'expired' => $expired
        ));
    }

} //class

//items.pool.class.php
namespace litepubl;

class tpoolitems extends tdata {
    protected $perpool;
    protected $pool;
    protected $modified;
    protected $ongetitem;

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->basename = 'poolitems';
        $this->perpool = 20;
        $this->pool = array();
        $this->modified = array();
    }

    public function getitem($id) {
        if (isset($this->ongetitem)) {
            return call_user_func_array($this->ongetitem, array(
                $id
            ));
        }

        $this->error('Call abastract method getitem in class' . get_class($this));
    }

    public function getfilename($idpool) {
        return $this->basename . '.pool.' . $idpool;
    }

    public function loadpool($idpool) {
        if ($data = litepubl::$urlmap->cache->get($this->getfilename($idpool))) {
            $this->pool[$idpool] = $data;
        } else {
            $this->pool[$idpool] = array();
        }
    }

    public function savepool($idpool) {
        if (!isset($this->modified[$idpool])) {
            litepubl::$urlmap->onclose = array(
                $this,
                'savemodified',
                $idpool
            );
            $this->modified[$idpool] = true;
        }
    }

    public function savemodified($idpool) {
        litepubl::$urlmap->cache->set($this->getfilename($idpool) , $this->pool[$idpool]);
    }

    public function getidpool($id) {
        $idpool = (int)floor($id / $this->perpool);
        if (!isset($this->pool[$idpool])) $this->loadpool($idpool);
        return $idpool;
    }

    public function get($id) {
        $idpool = $this->getidpool($id);
        if (isset($this->pool[$idpool][$id])) return $this->pool[$idpool][$id];
        $result = $this->getitem($id);
        $this->pool[$idpool][$id] = $result;
        $this->savepool($idpool);
        return $result;
    }

    public function set($id, $item) {
        $idpool = $this->getidpool($id);
        $this->pool[$idpool][$id] = $item;
        $this->savepool($idpool);
    }

} //class

//storage.mem.class.php
namespace litepubl;

class memstorage {
    public $memcache;
    public $memcache_prefix;
    public $lifetime;
    public $table;
    public $data;
    private $table_checked;

    public static function i() {
        return getinstance(__class__);
    }

    public function __construct() {
        $this->memcache_prefix = litepubl::$domain . ':';
        $this->table = 'memstorage';
        $this->table_checked = false;
        $this->data = array();
        if ($this->memcache = litepubl::$memcache) {
            $this->lifetime = 3600;
        } else {
            $this->lifetime = 10800;
        }
    }

    public function __get($name) {
        if (strlen($name) > 32) {
            $name = md5($name);
        }

        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return $this->get($name);
    }

    public function get($name) {
        $result = false;
        if ($this->memcache) {
            if ($s = $this->memcache->get($this->memcache_prefix . $name)) {
                $result = $this->unserialize($s);
                $this->data[$name] = $result;
            }
        } else {
            if (!$this->table_checked) {
                $this->check();
            }

            $db = litepubl::$db;
            if ($r = $db->query("select value from $db->prefix$this->table where name = '$name' limit 1")->fetch_assoc()) {
                $result = $this->unserialize($r['value']);
                $this->data[$name] = $result;
            }
        }

        return $result;
    }

    public function __set($name, $value) {
        if (strlen($name) > 32) {
            $name = md5($name);
        }

        $exists = isset($this->data[$name]);
        $this->data[$name] = $value;

        if ($this->memcache) {
            $this->memcache->set($this->memcache_prefix . $name, $this->serialize($value) , false, $this->lifetime);
        } else {
            if (!$this->table_checked) {
                $this->check();
            }

            $db = litepubl::$db;
            $v = $db->quote($this->serialize($value));
            if ($exists) {
                $db->query("update $db->prefix$this->table set value = $v where name = '$name' limit 1");
            } else {
                $db->query("insert into $db->prefix$this->table (name, value) values('$name', $v)");
            }
        }
    }

    public function __unset($name) {
        if (strlen($name) > 32) {
            $name = md5($name);
        }

        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }

        if ($this->memcache) {
            $this->memcache->delete($this->memcache_prefix . $name);
        } else {
            if (!$this->table_checked) {
                $this->check();
            }

            $db = litepubl::$db;
            $db->query("delete from $db->prefix$this->table where name = '$name' limit 1");
        }
    }

    public function serialize($data) {
        return serialize($data);
    }

    public function unserialize(&$data) {
        return unserialize($data);
    }

    public function check() {
        $this->table_checked = true;

        //exclude throw exception
        $db = litepubl::$db;
        $res = $db->mysqli->query("select value from $db->prefix$this->table where name = 'created' limit 1");
        if (is_object($res) && ($r = $res->fetch_assoc())) {
            $res->close();
            $created = $this->unserialize($r['value']);
            if ($created + $this->lifetime < time()) {
                $this->loadall();
                $this->clear_table();
                $this->data['created'] = time();
                $this->saveall();
            }
        } else {
            $this->create_table();
            $this->created = time();
        }
    }

    public function loadall() {
        $db = litepubl::$db;
        $res = $db->query("select * from $db->prefix$this->table");
        if (is_object($res)) {
            while ($item = $res->fetch_assoc()) {
                $this->data[$item['name']] = $this->unserialize($item['value']);
            }
        }
    }

    public function saveall() {
        $db = litepubl::$db;
        $a = array();
        foreach ($this->data as $name => $value) {
            $a[] = sprintf('(\'%s\',%s)', $name, $db->quote($this->serialize($value)));
        }

        $values = implode(',', $a);
        $db->query("insert into $db->prefix$this->table (name, value) values $values");
    }

    public function create_table() {
        $db = litepubl::$db;
        $db->mysqli->query("create table if not exists $db->prefix$this->table (
    name varchar(32) not null,
    value varchar(255),
    key (name)
    )
    ENGINE=MEMORY
    DEFAULT CHARSET=utf8
    COLLATE = utf8_general_ci");
    }

    public function clear_table() {
        $db = litepubl::$db;
        try {
            $db->query("truncate table $db->prefix$this->table");
        }
        catch(Exception $e) {
            //silince
            
        }
    }

} //class

//storage.cache.file.class.php
namespace litepubl;

class cachestorage_file {

    public function getdir() {
        return litepubl::$paths->cache;
    }

    public function set($filename, $data) {
        $this->setString($filename, serialize($data));
    }

    public function setString($filename, $str) {
        $fn = $this->getdir() . $filename;
        file_put_contents($fn, $str);
        @chmod($fn, 0666);
    }

    public function get($filename) {
        if ($s = $this->getString($filename)) {
            return unserialize($s);
        }

        return false;
    }

    public function getString($filename) {
        $fn = $this->getdir() . $filename;
        if (file_exists($fn)) {
            return file_get_contents($fn);
        }

        return false;
    }

    public function delete($filename) {
        $fn = $this->getdir() . $filename;
        if (file_exists($fn)) {
            unlink($fn);
        }
    }

    public function exists($filename) {
        return file_exists($this->getdir() . $filename);
    }

    public function clear() {
        $path = $this->getdir();
        if ($h = @opendir($path)) {
            while (FALSE !== ($filename = @readdir($h))) {
                if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) continue;
                $file = $path . $filename;
                if (is_dir($file)) {
                    tfiler::delete($file . DIRECTORY_SEPARATOR, true, true);
                } else {
                    unlink($file);
                }
            }
            closedir($h);
        }
    }

} //class

//storage.cache.memcache.class.php
namespace litepubl;

class cachestorage_memcache {
    public $memcache;
    public $lifetime;
    public $prefix;
    public $revision;
    public $revisionKey;

    public function __construct() {
        $this->memcache = litepubl::$memcache;
        $this->lifetime = 3600;
        $this->prefix = litepubl::$domain . ':cache:';
        $this->revision = 0;
        $this->revisionKey = 'cache_revision';
        if ($this->memcache) {
            $this->getRevision();
        }
    }

    public function getPrefix() {
        return $this->prefix . $this->revision . '.';
    }

    public function getRevision() {
        return $this->revision = (int)$this->memcache->get($this->prefix . $this->revisionKey);
    }

    public function clear() {
        $this->revision++;
        $this->memcache->set($this->prefix . $this->revisionKey, "$this->revision", false, $this->lifetime);
    }

    public function serialize($data) {
        return serialize($data);
    }

    public function unserialize(&$data) {
        return unserialize($data);
    }

    public function setString($filename, $str) {
        $this->memcache->set($this->getPrefix() . $filename, $str, false, $this->lifetime);
    }

    public function set($filename, $data) {
        $this->setString($filename, $this->serialize($data));
    }

    public function getString($filename) {
        return $this->memcache->get($this->getPrefix() . $filename);
    }

    public function get($filename) {
        if ($s = $this->getString($filename)) {
            return $this->unserialize($s);
        }

        return false;
    }

    public function delete($filename) {
        $this->memcache->delete($this->getPrefix() . $filename);
    }

    public function exists($filename) {
        return !!$this->memcache->get($this->prefix . $filename);
    }

} //class

//paths.php
namespace litepubl;

class paths {
    public $home;
    public $lib;
    public $libinclude;
    public $storage;
    public $data;
    public $cache;
    public $backup;
    public $js;
    public $plugins;
    public $themes;
    public $files;

    public function __construct() {
        $this->home = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        $this->lib = __DIR__ . DIRECTORY_SEPARATOR;
        $this->libinclude = $this->lib . 'include' . DIRECTORY_SEPARATOR;
        $this->languages = $this->lib . 'languages' . DIRECTORY_SEPARATOR;
        $this->storage = $this->home . 'storage' . DIRECTORY_SEPARATOR;
        $this->data = $this->storage . 'data' . DIRECTORY_SEPARATOR;
        $this->cache = $this->storage . 'cache' . DIRECTORY_SEPARATOR;
        $this->backup = $this->storage . 'backup' . DIRECTORY_SEPARATOR;
        $this->plugins = $this->home . 'plugins' . DIRECTORY_SEPARATOR;
        $this->themes = $this->home . 'themes' . DIRECTORY_SEPARATOR;
        $this->files = $this->home . 'files' . DIRECTORY_SEPARATOR;
        $this->js = $this->home . 'js/';
    }
}

//litepubl.php
namespace litepubl;

class litepubl {
    public static $cache;
    public static $classes;
    public static $datastorage;
    public static $db;
    public static $debug;
    public static $domain;
    public static $log;
    public static $memcache;
    public static $microtime;
    public static $options;
    public static $paths;
    public static $secret;
    public static $site;
    public static $storage;
    public static $urlmap;

    public static function init() {
        static ::$microtime = microtime(true);
        //backward compability, in near future will be removed on config::$secret
        static ::$secret = config::$secret;
        static ::$debug = config::$debug || (defined('litepublisher_mode') && (litepublisher_mode == 'debug'));
        static ::$domain = static ::getHost();
        static ::createAliases();
        static ::createInstances();
    }

    public function createAliases() {
        \class_alias(get_called_class() , 'litepublisher');
        \class_alias(get_called_class() , 'litepubl\litepublisher');
        \class_alias(get_called_class() , 'litepubl');
    }

    public static function createInstances() {
        static ::$paths = new paths();
        static ::createStorage();
        static ::$classes = tclasses::i();
        static ::$options = toptions::i();
        static ::$site = tsite::i();
        static ::$db = tdatabase::i();
        //static::$cache = new cache();
        static ::$urlmap = turlmap::i();
    }

    public static function createStorage() {
        if (config::$memcache && class_exists('Memcache')) {
            static ::$memcache = new Memcache;
            static ::$memcache->connect(isset(config::$memcache['host']) ? config::$memcache['host'] : '127.0.0.1', isset(config::$memcache['post']) ? config::$memcache['post'] : 1211);
        }

        if (isset(config::$classes['storage']) && class_exists(config::$classes['storage'])) {
            $classname = config::$classes['storage'];
            static ::$storage = new $classname();
        } else if (static ::$memcache) {
            static ::$storage = new memcachestorage();
        } else {
            static ::$storage = new storage();
        }

        static ::$datastorage = new datastorage();
        static ::$datastorage->loaddata();
        if (!static ::$datastorage->isInstalled()) {
            require (static ::$paths->lib . 'install/install.php');
            //exit() in lib/install/install.php
            
        }
    }

    public static function cachefile($filename) {
        if (!static ::$memcache) {
            return file_get_contents($filename);
        }

        if ($s = static ::$memcache->get($filename)) {
            return $s;
        }

        $s = file_get_contents($filename);
        static ::$memcache->set($filename, $s, false, 3600);
        return $s;
    }

    public static function getHost() {
        if (config::$host) {
            return config::$host;
        }

        $host = isset($_SERVER['HTTP_HOST']) ? \strtolower(\trim($_SERVER['HTTP_HOST'])) : false;
        if ($host && \preg_match('/(www\.)?([\w\.\-]+)(:\d*)?/', $host, $m)) {
            return $m[2];
        }

        if (config::$dieOnInvalidHost) {
            die('cant resolve domain name');
        }
    }

    public static function request() {
        if (static ::$debug) {
            \error_reporting(-1);
            \ini_set('display_errors', 1);
            \Header('Cache-Control: no-cache, must-revalidate');
            \Header('Pragma: no-cache');
        }

        if (config::$beforeRequest && \is_callable(config::$beforeRequest)) {
            \call_user_func_array(config::$beforeRequest, []);
        }

        return static ::$urlmap->request(static ::$domain, $_SERVER['REQUEST_URI']);
    }

    public static function run() {
        try {
            static ::init();

            if (!config::$ignoreRequest) {
                static ::request();
            }
        }
        catch(\Exception $e) {
            static ::$options->handexception($e);
        }

        static ::$options->savemodified();
        static ::$options->showerrors();
    }

} //class

//storage.php
namespace litepubl;

class storage {
    public $ext;

    public function __construct() {
        $this->ext = '.php';
    }

    public function serialize(array $data) {
        return \serialize($data);
    }

    public function unserialize($str) {
        if ($str) {
            return \unserialize($str);
        }

        return false;
    }

    public function before($str) {
        return \sprintf('<?php /* %s */ ?>', \str_replace('*/', '**//*/', $str));
    }

    public function after($str) {
        return \str_replace('**//*/', '*/', \substr($str, 9, \strlen($str) - 9 - 6));
    }

    public function getfilename(tdata $obj) {
        return litepubl::$paths->data . $obj->getbasename();
    }

    public function save(tdata $obj) {
        return $this->savefile($this->getfilename($obj) , $this->serialize($obj->data));
    }

    public function savedata($filename, array $data) {
        return $this->savefile($filename, $this->serialize($data));
    }

    public function load(tdata $obj) {
        try {
            if ($data = $this->loaddata($this->getfilename($obj))) {
                $obj->data = $data + $obj->data;
                return true;
            }
        }
        catch(\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage();
        }

        return false;
    }

    public function loaddata($filename) {
        if ($s = $this->loadfile($filename)) {
            return $this->unserialize($s);
        }

        return false;
    }

    public function loadfile($filename) {
        if (\file_exists($filename . $this->ext) && ($s = \file_get_contents($filename . $this->ext))) {
            return $this->after($s);
        }

        return false;
    }

    public function savefile($filename, $content) {
        $tmp = $filename . '.tmp' . $this->ext;
        if (false === \file_put_contents($tmp, $this->before($content))) {
            $this->error(\sprintf('Error write to file "%s"', $tmp));
            return false;
        }

        \chmod($tmp, 0666);

        //replace file
        $curfile = $filename . $this->ext;
        if (\file_exists($curfile)) {
            $backfile = $filename . '.bak' . $this->ext;
            $this->delete($backfile);
            \rename($curfile, $backfile);
        }

        if (!\rename($tmp, $curfile)) {
            $this->error(sprintf('Error rename temp file "%s" to "%s"', $tmp, $curfile));
            return false;
        }

        return true;
    }

    public function remove($filename) {
        $this->delete($filename . $this->ext);
        $this->delete($filename . '.bak' . $this->ext);
    }

    public function delete($filename) {
        if (\file_exists($filename) && !\unlink($filename)) {
            \chmod($filename, 0666);
            \unlink($filename);
        }
    }

    public function error($mesg) {
        litepubl::$options->trace($mesg);
    }

} //class

//storageinc.php
namespace litepubl;

class storageinc extends storage {

    public function __construct() {
        $this->ext = '.inc.php';
    }

    public function serialize(array $data) {
        return \var_export($data, true);
    }

    public function unserialize($str) {
        $this->error('Call unserialize');
    }

    public function before($str) {
        return \sprintf('<?php return %s;', $str);
    }

    public function after($str) {
        $this->error('Call after method');
    }

    public function loaddata($filename) {
        if (\file_exists($filename . $this->ext)) {
            return include ($filename . $this->ext);
        }

        return false;
    }

    public function loadfile($filename) {
        $this->error('Call loadfile');
    }

} //class

//storagememcache.php
namespace litepubl;

class storagememcache extends storage {
    public $memcache;

    public function __construct() {
        $this->memcache = litepubl::$memcache;
    }

    public function loadfile($filename) {
        if ($s = $this->memcache->get($filename)) {
            return $s;
        }

        if ($s = parent::loadfile($filename)) {
            $this->memcache->set($filename, $s, false, 3600);
            return $s;
        }

        return false;
    }

    public function savefile($filename, $content) {
        $this->memcache->set($filename, $content, false, 3600);
        return parent::savefile($filename, $content);
    }

    public function delete($filename) {
        parent::delete($filename);
        $this->memcache->delete($filename);
    }

} //class

//datastorage.php
namespace litepubl;

class datastorage {
    public $data;
    private $modified;

    public function __construct() {
        $this->data = [];
    }

    public function getStorage() {
        return litepubl::$storage;
    }

    public function save(tdata $obj) {
        $this->modified = true;
        $base = $obj->getbasename();
        if (!isset($this->data[$base])) {
            $this->data[$base] = & $obj->data;
        }

        return true;
    }

    public function load(tdata $obj) {
        $base = $obj->getbasename();
        if (isset($this->data[$base])) {
            $obj->data = & $this->data[$base];
            return true;
        } else {
            $this->data[$base] = & $obj->data;
            return false;
        }
    }

    public function remove(tdata $obj) {
        $base = $obj->getbasename();
        if (isset($this->data[$base])) {
            unset($this->data[$base]);
            $this->modified = true;
            return true;
        }
    }

    public function loaddata() {
        if ($data = $this->getStorage()->loaddata(litepubl::$paths->data . 'storage')) {
            $this->data = $data;
            return true;
        }

        return false;
    }

    public function saveModified() {
        if (!$this->modified) {
            return false;
        }

        $lockfile = litepubl::$paths->data . 'storage.lok';
        if (($fh = @\fopen($lockfile, 'w')) && \flock($fh, LOCK_EX | LOCK_NB)) {
            $this->getStorage()->savedata(litepubl::$paths->data . 'storage', $this->data);
            $this->modified = false;
            \flock($fh, LOCK_UN);
            \fclose($fh);
            @\chmod($lockfile, 0666);
            return true;
        } else {
            if ($fh) {
                @\fclose($fh);
            }

            $this->error('Storage locked, data not saved');
            return false;
        }
    }

    public function error($mesg) {
        tfiler::log($mesg);
    }

    public function isInstalled() {
        return count($this->data);
    }

} //class

//litepubl.init.php
namespace litepubl;

if (\version_compare(\PHP_VERSION, '5.4', '<')) {
    die('Lite Publisher requires PHP 5.4 or later. You are using PHP ' . \PHP_VERSION);
}

if (isset(config::$classes['root']) && class_exists(config::$classes['root'])) {
    \call_user_func_array(config::$classes['root'], 'run', []);
} else {
    litepubl::run();
}

