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
class Deliveries_model extends CI_Model {
	
	const TABLE_NAME 				= 'deliveries';
	const FIELD_ID					= 'id';
	const FIELD_LIST_ID				= 'l_id';
	const FIELD_CREATION_DATE		= 'creation_date';
	const FIELD_DELIVERY_DATE		= 'delivery_date';
	const FIELD_STATUS 				= 'status';
	const FIELD_VEGETABLES			= 'vegetables';

	public function __construct()
	{
		$this->load->database();
	}
	
	public function get_list($value=NULL, $offset=NULL)
	{
		$this->db->from(self::TABLE_NAME)
			->order_by(self::FIELD_DELIVERY_DATE, 'ASC')
			->order_by(self::FIELD_CREATION_DATE, 'ASC');
		if ($value !== NULL && $offset !== NULL)
		{
			$this->db->limit($value, $offset);
		}
		$query = $this->db->get();
		
		return $query->result();
	}
			
	public function get_list_date_range($start_date, $end_date)
	{		
		$this->db->from(self::TABLE_NAME)
			//->order_by(Orders_model::FIELD_STATUS, 'DESC')
			->order_by('delivery_date', 'DESC')
			->where('delivery_date >= \''.$start_date.'\'')
			->where('delivery_date <= \''.$end_date.'\'');

		$query = $this->db->get();
		
		return $query->result();
	}
	
	public function get_count()
	{
		return $this->db->count_all(self::TABLE_NAME);
	}
	
	public function get($id, $as_array=FALSE)
	{
		$this->load->model('lists_model');
		
		$query = $this->db
//			->select(self::TABLE_NAME.'.*,'.Lists_model::TABLE_NAME.'.name AS list_name')
			->where(self::TABLE_NAME.'.'.self::FIELD_ID, $id)
			->from(self::TABLE_NAME)
//			->join(
//				Lists_model::TABLE_NAME, 
//				Lists_model::TABLE_NAME.'.'.Lists_model::FIELD_ID.'='.self::TABLE_NAME.'.'.self::FIELD_LIST_ID)
			->get();
		
		if ($as_array)
		{
			$d = $query->row_array();
			$l = $this->lists_model->get($d[self::FIELD_LIST_ID]);
			if ($l !== NULL)
			{
				$d['list_name'] = $l->name;
			}
			else
			{
				$d->list_name = 'Deleted list';
			}
		}
		else
		{
			$d = $query->row();
			$l = $this->lists_model->get($d->{self::FIELD_LIST_ID});
			if ($l !== NULL)
			{
				$d->list_name = $l->name;
			}
			else
			{
				$d->list_name = 'Deleted list';
			}
		}
		
		return $d;
	}
	
	public function update($d_id, array $order)
	{
		if (isset($order[self::FIELD_VEGETABLES]) && is_array($order[self::FIELD_VEGETABLES]))
		{
			$order[self::FIELD_VEGETABLES] = json_encode($order[self::FIELD_VEGETABLES]);
		}

		$success = $this->db->from(self::TABLE_NAME)
			->where(self::FIELD_ID, $d_id)
			->set($order)
			->update();

		return $success;
	}
	
	public function update_vegetables($d_id, $vegetables)
	{
		if( ! is_array($vegetables))
		{
			return FALSE;
		}
		return $this->db->from(self::TABLE_NAME)
			->where(self::FIELD_ID, $d_id)
			->set(self::FIELD_VEGETABLES, json_encode($vegetables))
			->update();
	}
	
	public function create($list_id)
	{
		$this->load->model('lists_model');
		
		$vegetables = $this->lists_model->get_vegetables($list_id);
		
		$input_date = $this->input->post('delivery_date');
		preg_match(date_regex(), $input_date, $matches);
		$delivery_date = NULL;

		if ( ! empty($matches['date0']))
		{
			$delivery_date = DateTime::createFromFormat('d'.$matches[2].'m'.$matches[3].'Y', $matches['date0']);
		}
		elseif ( ! empty($matches['date1']))
		{
			$delivery_date = DateTime::createFromFormat('d'.$matches[6].'m', $matches['date1']);
		}
		elseif ( ! empty($matches['date2']))
		{
			$delivery_date = DateTime::createFromFormat('Y'.$matches[8].'m'.$matches[9].'d', $matches['date2']);
		}
		$now = new DateTime();
		
		$success = $this->db
			->set(self::FIELD_LIST_ID, $list_id)
			->set(self::FIELD_CREATION_DATE, $now->format('Y-m-d'))
			->set(self::FIELD_DELIVERY_DATE, $delivery_date->format('Y-m-d')) // MySQL DATE format)
			->set(self::FIELD_STATUS, 'collect')
			->set(self::FIELD_VEGETABLES, json_encode($vegetables))
			->insert(self::TABLE_NAME);
		
		if ($success === TRUE)
		{
			$id = $this->db->insert_id();
			return $id;
		}
		
		return FALSE;
	}
	
	/**
	 * WARNING: Be sure to delete first all the orders and the payments related to this order !
	 */
	public function delete($id)
	{
		// Delete delivery
		return $this->db->from(self::TABLE_NAME)
			->where(self::FIELD_ID, $id)
			->delete();
	}
	
}
