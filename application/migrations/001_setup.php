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
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setup extends CI_Migration {

	public function up()
	{
//		CREATE TABLE `vegetables` (
//		 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
//		 `name` varchar(255) NOT NULL,
//		 `price` decimal(10,2) unsigned NOT NULL,
//		 `unit` varchar(50) NOT NULL,
//		 `accounting_cat` varchar(50) DEFAULT NULL,
//		 PRIMARY KEY (`id`),
//		 UNIQUE KEY `name` (`name`)
//		)
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
                'unique' => TRUE
			),
			'price' => array(
				'type' => 'decimal',
				'constraint' => '10,2',
				'unsigned' => TRUE,
			),
			'unit' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
			),
			'accounting_cat' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'null' => TRUE,
				'default' => NULL
			)
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('vegetables');
		
//		CREATE TABLE `customers` (
//		 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
//		 `name` varchar(255) NOT NULL,
//		 `contact_name` varchar(255) DEFAULT NULL,
//		 `email` varchar(255) DEFAULT NULL,
//		 `email_2` varchar(255) DEFAULT NULL,
//		 `delivery_place` tinytext DEFAULT NULL,
//		 `delivery_place_2` tinytext,
//		 `current_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
//		 PRIMARY KEY (`id`),
//		 UNIQUE KEY `name` (`name`),
//		 UNIQUE KEY `email` (`email`)
//		)
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
                'unique' => TRUE
			),
			'contact_name' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => TRUE,
				'default' => NULL
			),
			'email' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => TRUE/*,
                'unique' => TRUE*/
			),
			'email_2' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => TRUE
			),
			'delivery_place' => array(
				'type' => 'tinytext',
				'null' => TRUE
			),
			'delivery_place_2' => array(
				'type' => 'tinytext',
				'null' => TRUE
			),
			'current_balance' => array(
				'type' => 'decimal',
				'constraint' => '10,2',
				'default' => '0.00'
			)
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('customers');

//		CREATE TABLE `deliveries` (
//		 `id` int(11) NOT NULL AUTO_INCREMENT,
//		 `l_id` int(11) NOT NULL,
//		 `creation_date` date NOT NULL,
//		 `delivery_date` date NOT NULL,
//		 `status` varchar(25) NOT NULL DEFAULT 'collect',
//		 `vegetables` text NOT NULL,
//		 PRIMARY KEY (`id`)
//		)
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'l_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE
			),
			'creation_date' => array(
				'type' => 'date',
			),
			'delivery_date' => array(
				'type' => 'date',
			),
			'status' => array(
				'type' => 'VARCHAR',
				'constraint' => 25,
				'default' => 'collect'
			),
			'vegetables' => array(
				'type' => 'text'
			)
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('deliveries');

//		CREATE TABLE `lists` (
//		 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
//		 `name` varchar(50) NOT NULL,
//		 `day_of_week` tinyint(4) NOT NULL,
//		 PRIMARY KEY (`id`),
//		 UNIQUE KEY `name` (`name`)
//		)
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
                'unique' => TRUE
			),
			'day_of_week' => array(
				'type' => 'tinyint',
				'constraint' => 4
			)
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('lists');
		
//		CREATE TABLE `lists_customers` (
//		 `l_id` int(11) NOT NULL,
//		 `c_id` int(11) NOT NULL,
//		 PRIMARY KEY (`l_id`,`c_id`)
//		)
		$this->dbforge->add_field(array(
			'l_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE
			),
			'c_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE
			)
		));
		$this->dbforge->add_key('l_id', TRUE);
		$this->dbforge->add_key('c_id', TRUE);
		$this->dbforge->create_table('lists_customers');
		
//		 CREATE TABLE `lists_vegetables` (
//		 `l_id` int(11) NOT NULL,
//		 `v_id` int(11) NOT NULL,
//		 PRIMARY KEY (`l_id`,`v_id`)
//		)
		$this->dbforge->add_field(array(
			'l_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE
			),
			'v_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE
			)
		));
		$this->dbforge->add_key('l_id', TRUE);
		$this->dbforge->add_key('v_id', TRUE);
		$this->dbforge->create_table('lists_vegetables');
		
//		CREATE TABLE `orders` (
//		 `id` int(11) NOT NULL AUTO_INCREMENT,
//		 `d_id` int(11) NOT NULL,
//		 `c_id` int(11) NOT NULL,
//		 `vegetables` text,
//		 `status` varchar(25) NOT NULL DEFAULT 'collect',
//		 `payment` decimal(10,2) NOT NULL DEFAULT '0',
//		 `invoice` text,
//		 `comments` tinytext,
//		 PRIMARY KEY (`id`),
//		 UNIQUE KEY `d_id` (`d_id`,`c_id`)
//		)
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE
			),
			'c_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE
			),
			'vegetables' => array(
				'type' => 'text',
                'null' => TRUE
			),
			'status' => array(
				'type' => 'VARCHAR',
				'constraint' => 25,
				'default' => 'collect'
			),
			'payment' => array(
				'type' => 'decimal',
				'constraint' => '10,2',
				'constraint' => 11,
				'default' => 0
			),
			'invoice' => array(
				'type' => 'text',
                'null' => TRUE
			),
			'comments' => array(
				'type' => 'tinytext',
                'null' => TRUE
			)
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('orders');
		//$this->dbforge->add_key(array('d_id', 'c_id'));
		// DB Forge don't known how to add pair of unique keys
		$this->db->query('ALTER TABLE `orders` ADD UNIQUE( `d_id`, `c_id`)');
		
//		CREATE VIEW `detailed_orders`
		$this->db->query('CREATE VIEW `detailed_orders` AS select `orders`.`id` AS `id`,`orders`.`d_id` AS `d_id`,`orders`.`c_id` AS `c_id`,`orders`.`vegetables` AS `vegetables`,`orders`.`status` AS `status`,`orders`.`payment` AS `payment`,`orders`.`invoice` AS `invoice`,`orders`.`comments` AS `comments`,`lists`.`name` AS `delivery_name`,`deliveries`.`delivery_date` AS `delivery_date`,`deliveries`.`status` AS `delivery_status`,`customers`.`name` AS `customer` from (((`orders` join `deliveries` on((`deliveries`.`id` = `orders`.`d_id`))) join `lists` on((`lists`.`id` = `deliveries`.`l_id`))) join `customers` on((`customers`.`id` = `orders`.`c_id`)))');
		
//		CREATE TABLE `accounting` (
//		 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
//		 `d_id` int(11) unsigned NOT NULL,
//		 `c_id` int(11) unsigned NOT NULL,
//		 `total_due` decimal(10,2) NOT NULL DEFAULT '0',
//		 `total_paid` decimal(10,2) NOT NULL DEFAULT '0',
//		 `details` text,
//		 `payments` text NOT NULL,
//		 `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//		 UNIQUE KEY `d_id` (`d_id`,`c_id`)
//		 PRIMARY KEY (`id`)
//		)
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE
			),
			'c_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'total_due' => array(
				'type' => 'decimal',
				'constraint' => '10,2',
				'default' => '0'
			),
			'total_paid' => array(
				'type' => 'decimal',
				'constraint' => '10,2',
				'default' => '0'
			),
			'details' => array(
				'type' => 'text',
                'null' => TRUE
			),
			'payments' => array(
				'type' => 'text'
			)/*,
			'timestamp' => array(
				'type' => 'timestamp',
				'default' => CURRENT_TIMESTAMP
			)*/
		));
		$this->dbforge->add_field('`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('accounting');
		//$this->dbforge->add_key(array('d_id','c_id'));
		// DB Forge don't known how to add pair of unique keys
		$this->db->query('ALTER TABLE `accounting` ADD UNIQUE( `d_id`, `c_id`)');
	}

	public function down()
	{
		$this->dbforge->drop_table('customers', TRUE);
		$this->dbforge->drop_table('deliveries', TRUE);
		$this->dbforge->drop_table('lists', TRUE);
		$this->dbforge->drop_table('lists_customers', TRUE);
		$this->dbforge->drop_table('lists_vegetables', TRUE);
		$this->dbforge->drop_table('orders', TRUE);
		$this->dbforge->drop_table('vegetables', TRUE);
		$this->dbforge->drop_table('accounting', TRUE);
		$this->db->query('DROP VIEW `detailed_orders`');
	}
}