<?php
/**
 * php-hash2data v1.0.2
 * 
 * Single PHP library file for binding hashes to data
 * Easily save and load data on the session using hashed as ids.
 *
 * 
 * MIT License
 *
 * Copyright (c) 2018 Grammatopoulos Athanasios-Vasileios
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

/**
 * Usage:
 * 		// Init databank
 * 		$databank = new Hash2Data();
 *
 * 		// Save data on the session
 * 		$hash = $databank->save(<data>);
 *
 * 		// Load them later
 * 		$data = $databank->load($hash);
 *
 * 		// Or even update them
 * 		$databank->update($hash, <data>);
 */
class Hash2Data {

	private $name;
	private $hashes2data;
	private $hashTime2Live;
	private $hashSize;
	private $inputName;

	function __construct ($sessionName='hash2data-lib', $hashTime2Live=0, $hashSize=64) {
		// Session mods
		$this->name = $sessionName;
		// Default time before expire for hashes
		$this->hashTime2Live = $hashTime2Live;
		// Default size for hashes
		$this->hashSize = $hashSize;
		// Load hash2data list
		$this->_load();
	}

	/**
	 * Generate a Hash bined to the data
	 * @param  mixed  $data   		the data to save
	 * @param  string  $context   	Name of the group
	 * @param  integer $time2Live 	Seconds before expiration
	 * @return Hash2Data_Hash
	 */
	public function save ($data, $context = '', $time2Live=-1) {
		// If no time2live (or invalid) use default
		if ($time2Live < 0) $time2Live = $this->hashTime2Live;
		// Generate new hash2data
		$hash2data = new Hash2Data_Hash($data, $context, $time2Live, $this->hashSize);
		// Save it
		array_push($this->hashes2data, $hash2data);
		$this->_save();

		// Return hash
		return $hash2data->getHash();
	}

	/**
	 * Update bined data
	 * @param  string  $hash   		the hash
	 * @param  mixed   $data   		the data to save
	 * @param  string  $context   	Name of the group
	 * @param  integer $time2Live 	Seconds before expiration
	 * @return Hash2Data_Hash
	 */
	public function update ($hash, $data, $context = '', $time2Live=-1) {
		// Find data
		$index = $this->_find($hash, $context);
		if ($index < 0) return false;
		$hash2data = $this->hashes2data[$index];

		// Update
		$hash2data->update($data, $time2Live);

		// Save it
		$this->_save();

		// Return saved flag
		return true;
	}

	/**
	 * Check if hash exists
	 * @param  string  $hash   		the hash
	 * @param  string  $context   	Name of the group
	 * @return boolean
	 */
	public function exists ($hash, $context = '') {
		if ($this->_find($hash, $context) < 0) return false;
		return true;
	}

	/**
	 * Get your data from a hash
	 * @param  mixed  $hash   		The hash that references the data
	 * @param  string  $context   	Name of the group
	 * @param  integer $remove 		Delete after load
	 * @return mixed
	 */
	public function load ($hash, $context = '', $remove=false) {
		// Find data
		$index = $this->_find($hash, $context);
		if ($index < 0) return null;

		$data = $this->hashes2data[$index]->getData();
		if ($remove) {
			array_splice($this->hashes2data, $index, 1);
		}

		// Not found
		return $data;
	}

	/**
	 * Delete data and hash binding
	 * @param  string  $hash   		the hash
	 * @param  string  $context   	Name of the group
	 * @return boolean
	 */
	public function delete ($hash, $context = '') {
		return $this->load($hash, $context, true);
	}

	/**
	 * Find index on the hashes2data list from a hash
	 * @param  string $hash    The hash bind to the data
	 * @param  string $context The group to search into
	 * @return int             The index on the list
	 */
	private function _find ($hash, $context = '') {
		// Check in the hashes2data list
		for ($i = count($this->hashes2data) - 1; $i >= 0; $i--) {
			if ($this->hashes2data[$i]->match($hash, $context)) {
				return $i;
			}
		}

		// Not found
		return -1;
	}

	/**
	 * Load hash list
	 */
	private function _load () {
		$this->hashes2data = array();
		// If there are hashes on the session
		if (isset($_SESSION[$this->name])) {
			// Load session hashes
			$session_hashes = unserialize($_SESSION[$this->name]);
			// Ignore expired
			for ($i = count($session_hashes) - 1; $i >= 0; $i--) {
				// If an expired found, the rest will be expired
				if ($session_hashes[$i]->hasExpire()) {
					break;
				}
				array_unshift($this->hashes2data, $session_hashes[$i]);
			}
			if (count($this->hashes2data) != count($session_hashes)) {
				$this->_save();
			}
		}
	}

	/**
	 * Save hash list
	 */
	private function _save () {
		$_SESSION[$this->name] = serialize($this->hashes2data);
	}

}

class Hash2Data_Hash {

	private $hash;
	private $data;
	private $context;
	private $expire;

	/**
	 * [__construct description]
	 * @param string  $context   [description]
	 * @param integer $time2Live Number of seconds before expiration
	 */
	function __construct($data, $context, $time2Live=0, $hashSize=64) {
		// Save context name
		$this->context = $context;
		// Save data
		$this->data = $data;

		// Generate hash
		$this->hash = $this->_generateHash($hashSize);

		// Set expiration time
		if ($time2Live > 0) {
			$this->expire = time() + $time2Live;
		}
		else {
			$this->expire = 0;
		}
	}

	/**
	 * The hash function to use
	 * @param  int $n 	Size in bytes
	 * @return string 	The generated hash
	 */
	private function _generateHash ($n) {
		return bin2hex(openssl_random_pseudo_bytes($n/2));
	}

	/**
	 * Check if hash has expired
	 * @return boolean
	 */
	public function hasExpire () {
		if ($this->expire == 0 || $this->expire > time()) {
			return false;
		}
		return true;
	}

	/**
	 * Match hash
	 * @return boolean
	 */
	public function match ($hash, $context='') {
		if (strcmp($context, $this->context) == 0 && !$this->hasExpire() && strcmp($hash, $this->hash) == 0) {
			return true;
		}
		return false;
	}

	/**
	 * Get hash
	 * @return string
	 */
	public function getHash () {
		return $this->hash;
	}
	/**
	 * Get data bind to the hash
	 * @return mixed
	 */
	public function getData () {
		return $this->data;
	}

	/**
	 * Update saved data
	 * @param  mixed  $data       The data to save
	 * @param  integer $time2Live Number of seconds before expiration
	 */
	public function update($data, $time2Live=-1) {
		// Set expiration time
		if ($time2Live > 0) {
			$this->expire = time() + $time2Live;
		}
		else if ($time2Live == 0) {
			$this->expire = $time2Live;
		}

		// Save data
		$this->data = $data;
	}
}
