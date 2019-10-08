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

class Migration_Change_orders_view extends CI_Migration {

	public function up()
	{	
		// DROP old table
		$this->db->query('DROP VIEW `detailed_orders`');
		
		//		CREATE VIEW `detailed_orders` with no join to lists table
		$this->db->query('CREATE VIEW `detailed_orders` AS select `orders`.`id` AS `id`,`orders`.`d_id` AS `d_id`,`orders`.`c_id` AS `c_id`,`orders`.`vegetables` AS `vegetables`,`orders`.`status` AS `status`,`orders`.`payment` AS `payment`,`orders`.`invoice` AS `invoice`,`orders`.`comments` AS `comments`,`deliveries`.`l_id` AS `l_id`,`deliveries`.`delivery_date` AS `delivery_date`,`deliveries`.`status` AS `delivery_status`,`customers`.`name` AS `customer` from ((`orders` join `deliveries` on((`deliveries`.`id` = `orders`.`d_id`))) join `customers` on((`customers`.`id` = `orders`.`c_id`)))');
	}

	public function down()
	{
		// DROP old table
		$this->db->query('DROP VIEW `detailed_orders`');
		
		//		CREATE VIEW `detailed_orders` with join to lists table
		$this->db->query('CREATE VIEW `detailed_orders` AS select `orders`.`id` AS `id`,`orders`.`d_id` AS `d_id`,`orders`.`c_id` AS `c_id`,`orders`.`vegetables` AS `vegetables`,`orders`.`status` AS `status`,`orders`.`payment` AS `payment`,`orders`.`invoice` AS `invoice`,`orders`.`comments` AS `comments`,`lists`.`name` AS `delivery_name`,`deliveries`.`delivery_date` AS `delivery_date`,`deliveries`.`status` AS `delivery_status`,`customers`.`name` AS `customer` from (((`orders` join `deliveries` on((`deliveries`.`id` = `orders`.`d_id`))) join `lists` on((`lists`.`id` = `deliveries`.`l_id`))) join `customers` on((`customers`.`id` = `orders`.`c_id`)))');
	}

}
