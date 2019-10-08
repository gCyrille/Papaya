<?php
/**
 * Papaya
 *
 * An open source application to help in the managment of vegetable deliveries.
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2018 - 2019, Chikowa Youth Development Centre
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author	Cyrille Gandon
 * @copyright	Copyright (c) 2018 - 2019, Chikowa Youth Development Centre
 * @license	http://opensource.org/licenses/MIT	MIT License
 */
class Admin_model extends CI_Model {

	private $app_tables = array(
		'customers', 
		'deliveries',
		'detailed_orders', 
		'lists',
		'lists_customers',
		'lists_vegetables',
		'orders',
		'vegetables',
		'accounting'
	);
	
	private $_db_config = array();
	
	public function __construct()
	{
		// Load defautl db configs
		include(APPPATH.'config/database.php');
		$this->_db_config = $db['default'];
	}
	
	public function force_reload_config()
	{
		// Load defautl db configs
		include(APPPATH.'config/database.php');
		$this->_db_config = $db['default'];
	}
	
	private function _connect_to_mysql()
	{
		// Connect to default db to be able to create the db
		$config['hostname'] = 'localhost';
		$config['username'] = 'root';
		$config['database'] = 'mysql';
		$config['dbdriver'] = 'mysqli';
		return $this->load->database($config, TRUE);	
	}
	
	private function _connect_to_default()
	{
		
	}
	
	public function db_config($item)
	{
		return $this->_db_config[$item];
	}
	
	public function db_exists()
	{
		$mysql_db = $this->_connect_to_mysql();
		
		$dbutil = $this->load->dbutil($mysql_db, TRUE);
	
		return $dbutil->database_exists($this->_db_config['database']);
	}
	
	/**
	 * Check if the db is ready
	 *
	 * @return	mixed	TRUE if db is ready, targeted migration if needed, FALSE on not ready (no db, partial tables)
	 */
	public function is_db_ready()
	{
		if ($this->db_exists())
		{
			$this->load->database();
			$ok = TRUE;
			
			foreach ($this->app_tables as $table)
			{
				$ok = $ok && $this->db->table_exists($table);
			}
			
			if ($ok === TRUE)
			{
				$this->load->library('migration'); // Load this library only here because it try to connect to the database using the config file.
				$this->config->load('migration');
				$row = $this->db->select('version')->get( $this->config->item('migration_table'))->row();
				$curr_version = $row ? $row->version : '0';
				$target_version = $this->config->item('migration_version');
				if ($curr_version != $target_version)
				{
					return $target_version;
				}
				
				/* // This check for the latest available
				$migrations = $this->migration->find_migrations();
				end($migrations);
				$last_migration = key($migrations);
				if ($last_migration != $this->config->item('migration_version'))
				{
					return $last_migration;
				}*/
					//this->config->item('migration_version'));
			}

			return $ok;
		} 
		else 
		{
			return FALSE;
		}
	}
	
	public function create_db()
	{
		$mysql_db = $this->_connect_to_mysql();
		
		$dbutil = $this->load->dbutil($mysql_db, TRUE);
		
		if ($dbutil->database_exists($this->_db_config['database']))
		{
			return TRUE; // db exists next step
		}
		else
		{
			$dbforge = $this->load->dbforge($mysql_db, TRUE);
			return $dbforge->create_database($this->_db_config['database']);
		}
	}
}
