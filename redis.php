<?php
/**
 * Redis storage engine for cache
 */

App::import('Lib', 'Redisent');

class RedisEngine extends CacheEngine 
{
	public $__Redis = null;
	public $settings = array();

	public function init($settings = array()) 
	{
		if (!class_exists('Redisent')) 
			return false;
		
		parent::init( array_merge( array(
			'engine'  => 'Redis', 
			'prefix'  => Inflector::slug(APP_DIR) . '_', 
			'servers' => array('127.0.0.1')
			), $settings)
		);
		
		if (!is_array($this->settings['servers'])) {
			$this->settings['servers'] = array($this->settings['servers']);
		}
		if (!isset($this->__Redis)) {
			$return = false;
			$this->__Redis =& new Redisent();
			foreach ($this->settings['servers'] as $server) {
				list($host, $port) = $this->_parseServerString($server);
				if ($this->__Redis->addServer($host, $port)) {
					$return = true;
				}
			}
			return $return;
		}
		return true;
	}
	
	public function _parseServerString($server) {
		if (substr($server, 0, 1) == '[') {
			$position = strpos($server, ']:');
			if ($position !== false) {
				$position++;
			}
		} else {
		    $position = strpos($server, ':');
		}
		$port = 6379;
		$host = $server;
		if ($position !== false) {
			$host = substr($server, 0, $position);
			$port = substr($server, $position + 1);
		}
		return array($host, $port);
	}

	public function write($key, &$value, $duration) 
	{
		return $this->__Redis->set($key, serialize($value));
	}

	public function read($key) 
	{
		return $this->__Redis->get($key);
	}
	
	public function delete($key) 
	{
		return $this->__Redis->delete($key);
	}
	
	public function clear() 
	{
		return $this->__Redis->flush();
	}

	public function connect($host, $port = 6379) 
	{
		if ($this->__Redis->getServerStatus($host, $port) === 0) {
			if ($this->__Redis->connect($host, $port)) {
				return true;
			}
			return false;
		}
		return true;
	}
}