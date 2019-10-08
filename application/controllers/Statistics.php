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
class Statistics extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		$data['title'] = "Statistics for deliveries";
		
		// Load the templates in order with the data to print
		$this->load->view('templates/header', $data);
		$this->load->view('statistics/home', $data);
		$this->load->view('templates/footer', $data);
	}
	
	/**
	 * Page to display statistics about sales (orders)
	 */
	public function sales()
	{
		$data['title'] = "Sales by customer";
		
		$this->load->model('customers_model');
		$this->load->model('lists_model');
		$this->load->helper('form');
		$customers =  array( '*' => 'All') + array_column($this->customers_model->get_list(), 'name', 'id');
		$data['customers_list'] = $customers;
		$data['delivery_lists'] = array_column($this->lists_model->get_list(), 'name', 'id');
		
		// Load the templates in order with the data to print
		$this->load->view('templates/header', $data);
		$this->load->view('statistics/sales', $data);
		$this->load->view('templates/footer', $data);
	}
	
	/**
	 * Page to dispay bar chart of customers with the total of the orders
	 */
	public function top_customers()
	{
		$data['title'] = "Top customers";
		
		$this->load->model('customers_model');
		$this->load->model('lists_model');
		$this->load->helper('form');
		
		$data['customers_list'] = array_column($this->customers_model->get_list(), 'name', 'id');
		$data['delivery_lists'] = array_column($this->lists_model->get_list(), 'name', 'id');
		
		// Load the templates in order with the data to print
		$this->load->view('templates/header', $data);
		$this->load->view('statistics/top_customers', $data);
		$this->load->view('templates/footer', $data);
	}
	
	/**
	 * Endpoint REST API for CanvaJS charts
	 * @POST date 'start'		=> period start date
	 * @POST date 'end'			=> period end date
	 * @POST string	'groupBy'	=> Group the orders per day, week or month
	 * @POST int 'customerIds'	=> the ids of customers to include in the results ('*' to select all customers)
	 * @POST int 'customersExcl'=> the ids of customers to exclude from the results
	 * @POST int 'listIds'		=> the ids of the list to include in the results, if NULL it selects all lists
	 * @POST int 'vegetId'		=> Filter the results to show only this vegetables
	 * @POST string 'separateLists' => 'true' to generate one serie per delivery list.
	 */
	public function get_sales()
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
			return;
		}
		
		$start_date = $this->input->post('start');
		$end_date = $this->input->post('end');
		$group_by = $this->input->post('groupBy');
		$customer_ids = $this->input->post('customerIds'); // Include these customers
		$customers_excl = $this->input->post('customersExcl'); // Exclude these customers
		$list_ids = $this->input->post('listIds'); // Show only those lists, or all if NULL
		$vegetId = $this->input->post('vegetId'); // Filter the results to show only for this vegetable (or all if NULL)
		$separate_lists = $this->input->post('separateLists') == 'true';
		
		if ( ! in_array($group_by, array('day', 'week', 'month')))
		{
				$group_by = 'week';
		}
		
		if ($start_date !== NULL && $end_date !== NULL && $customer_ids !== NULL) 
		{
			$response = array();
			$response['series'] = NULL;
			
			$this->load->model('customers_model');
			$this->load->model('deliveries_model');
			$this->load->model('veget_model');
			
			if (in_array('*', $customer_ids))
			{
				$customer_ids = array('*');
			}
			
			$totals = array();
			foreach($customer_ids as $customer_id)
			{
				$orders = $this->customers_model->get_orders_date_range($customer_id, $start_date, $end_date);
				if (count($orders) > 0) 
				{
					foreach ($orders as $order) 
					{
						if ($order->invoice != NULL // Filter the results
							&& ($list_ids === NULL || in_array($order->l_id, $list_ids))
						   	&& ($customers_excl === NULL || ! in_array($order->c_id, $customers_excl)))
						{
							$inv = json_decode($order->invoice);
							if (count($inv->vegets) > 0)
							{
								if ($separate_lists && ! isset($totals[$order->l_id]))
								{
									$totals[$order->l_id] = array();
								}
								
								switch($group_by)
								{
									case 'day':
										$index = $order->delivery_date;
										break;
									case 'week':
										$index = date("Y-m-d", strtotime("monday this week $order->delivery_date"));
										break;
									case 'month':
										$index = substr($order->delivery_date, 0, 7);//date("Y-m-d", strtotime("first day of the month $order->delivery_date"));
										break;
								}
								if ($separate_lists)
								{
									// Fill with 0 the unkown dates to avoid bug on stacked are chart
									foreach($totals as $l_id => $serie)
									{
										if ( ! isset($totals[$l_id][$index]))
										{
											$totals[$l_id][$index] = 0;
										}
									}
									
									$sub_total = &$totals[$order->l_id][$index];// += $inv->total;
								}
								else
								{
									if ( ! isset($totals[$index]))
									{
										$totals[$index] =  0;
									}
									$sub_total = &$totals[$index];// += $inv->total;
								}
								//	if $vegetId !== NULL : 
								// 		Get from $inv->vegets the quantity of this vegetable.
								// 		Add to $sub_total the generated income
								//	else
								//		Add to the result the invoice total
								if ($vegetId == NULL)
								{
									$sub_total +=  $inv->total;
								}
								else
								{
									$vegets = json_decode($order->vegetables, TRUE);
									if (array_key_exists($vegetId, $vegets))
									{
										// Build price list for the delivery
										if ( ! isset($prices[$order->d_id]))
										{
											$delivery = $this->deliveries_model->get($order->d_id);
											if ($delivery != NULL)
											{
												$sub_prices = array_column(json_decode($delivery->vegetables), 'price', 'id');
												if (isset($sub_prices[$vegetId]))
												{
													$prices[$order->d_id] = $sub_prices[$vegetId];
													$sub_total +=  ($vegets[$vegetId] * $prices[$order->d_id]);
												}
												else
												{
													if ( ! isset($prices['*']))
													{
														$v = $this->veget_model->get($vegetId);
														$prices['*'] = $v->price;
													}
													$sub_total += ($vegets[$vegetId] * $prices['*']);
												}
											}
										}
										else
										{
											$sub_total += ($vegets[$vegetId] * $prices[$order->d_id]);	
										}
									}
									else
									{
										//skip this order
									}
								}
							}
						}
					}
				}
			}
			
			if ( ! $separate_lists)
			{
				$response['series'] = array(array('name' => 'Sales', 'data' => array()));
				ksort($totals);
				foreach ($totals as $key => $total)
				{
					// Format the JSON answer
					$response['series'][0]['data'][] = array(
						'label' => $key,
						'value' => $total
					);
				}

				$response['average'] = count($totals) > 0 ? format_kwacha(array_sum($totals) / count($totals)) : 0;
			}
			else
			{
				$response['series'] = array();
				$sum = 0;
				$count = 0;
				
				$this->load->model('lists_model');
				
				foreach($totals as $l_id => $serie)
				{
					
					$list = $this->lists_model->get($l_id);
					
					$data = array(
						'name' => $list != NULL ? $list->name : $l_id,
						'data' => array()
					);
					
					ksort($serie);
					foreach ($serie as $key => $total)
					{
						// Format the JSON answer
						$data['data'][] = array(
							'label' => $key,
							'value' => $total
						);
						if ($total > 0)
						{
							$count ++;
						}
					}
//					$count += count($serie);
					$sum += array_sum($serie);
					
					$response['series'][] = $data;
				}
				
				$response['average'] = $count > 0 ? format_kwacha($sum / $count) : 0;
			}
			
			if ($response['series'] == NULL)
			{
				$response['success'] = 'false';
				$response['message'] = 'No order found';
			} 
			else 
			{
				$response['success'] = 'true';
			}
		}
		else 
		{
			$response['success'] = 'false';
			$response['message'] = 'Date must be selected.';
		}
		echo json_encode($response);
	}
	
	/**
	 * Endpoint REST API for CanvaJS charts
	 * @POST date 'start'		=> period start date
	 * @POST date 'end'			=> period end date
	 * @POST int 'customersExcl'=> the ids of customers to exclude from the results
	 * @POST int 'listIds'		=> the ids of the list to include in the results, if NULL it selects all lists
	 * @POST int 'vegetId'		=> this ids of the veget to use to filter the results, if NULL it selects all vegetables
	 * @POST string 'sortedBy'	=> ['quantity', 'income'] Use the quantities of sold vegetables or the generated incomes
	 */
	public function get_customers()
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
			return;
		}
		
		$start_date = $this->input->post('start');
		$end_date = $this->input->post('end');
		$customers_excl = $this->input->post('customersExcl'); // Exclude these customers
		$list_ids = $this->input->post('listIds'); // Show only those lists, or all if NULL
		$vegetId = $this->input->post('vegetId'); // Filter the results to show only for this vegetable (or all if NULL)
		$sorted_by = $this->input->post('sortedBy'); // Use quantity or income
		
		// Set default values
		$sorted_by = $sorted_by == NULL ? 'income' : $sorted_by;
		
		if ($start_date !== NULL && $end_date !== NULL) 
		{
			$response = array();
			$response['data'] = NULL;
			
			$this->load->model('customers_model');
			$this->load->model('deliveries_model');
			$this->load->model('veget_model');
			
			$orders = $this->customers_model->get_orders_date_range('*', $start_date, $end_date);
			if (count($orders) > 0) 
			{
				$customers = array();
				$prices = array();
				foreach ($orders as $order) 
				{
					if ($order->invoice != NULL // Filter the results
							&& ($list_ids === NULL || in_array($order->l_id, $list_ids))
						   	&& ($customers_excl === NULL || ! in_array($order->c_id, $customers_excl)))
					{
						$inv = json_decode($order->invoice);
						if (count($inv->vegets) > 0)
						{
							//	if $vegetId !== NULL : 
							// 		Get from $inv->vegets the quantity of this vegetable.
							// 		Add to $customers[$order->c_id] the quantity or the income generated
							//	else
							//		Add to $customers[$order->c_id] the invoice total or the total quantities of vegetables
							if ($vegetId == NULL)
							{
								if ( ! isset($customers[$order->c_id]))
								{
									$customers[$order->c_id] =  0;
								}
								switch($sorted_by)
								{
									case 'quantity':
										$customers[$order->c_id] += array_sum(json_decode($order->vegetables, TRUE));
										break;
									case 'income':
										$customers[$order->c_id] +=  $inv->total;
										break;
								}
										
							}
							else
							{
								$vegets = json_decode($order->vegetables, TRUE);
								if (array_key_exists($vegetId, $vegets))
								{
									if ( ! isset($customers[$order->c_id])) // Create customer only of ordered this vegetable
									{
										$customers[$order->c_id] =  0;
									}
									switch($sorted_by)
									{
										case 'quantity':
											$customers[$order->c_id] += $vegets[$vegetId];
											break;
										case 'income':
											// Build price list for the delivery
											if ( ! isset($prices[$order->d_id]))
											{
												$delivery = $this->deliveries_model->get($order->d_id);
												if ($delivery != NULL)
												{
													$sub_prices = array_column(json_decode($delivery->vegetables), 'price', 'id');
													if (isset($sub_prices[$vegetId]))
													{
														$prices[$order->d_id] = $sub_prices[$vegetId];
														$customers[$order->c_id] +=  ($vegets[$vegetId] * $prices[$order->d_id]);
													}
													else
													{
														if ( ! isset($prices['*']))
														{
															$v = $this->veget_model->get($vegetId);
															$prices['*'] = $v->price;
														}
														$customers[$order->c_id] += ($vegets[$vegetId] * $prices['*']);
													}
												}
											}
											else
											{
												$customers[$order->c_id] +=  ($vegets[$vegetId] * $prices[$order->d_id]);	
											}
											break;
									}
								}
								else
								{
									//skip this order
								}
							}
						}
					}
				}
				
				asort($customers);
				
				$response['data'] = array();
				foreach ($customers as $key => $total)
				{
					if ($total > 0)
					{
						$c = $this->customers_model->get($key);
						
						if ($c !== NULL)
						{
							$response['data'][] = array(
								'label' => $c->name,
								'value' => $total
							);
							
						}
					}
				}
				
				$response['average'] = count($customers) > 0 ? format_kwacha(array_sum($customers) / count($customers)) : 0;
				
			}
			if ($response['data'] == NULL)
			{
				$response['success'] = 'false';
				$response['message'] = 'No order found';
			} 
			else 
			{
				$response['success'] = 'true';
			}
		}
		else 
		{
			$response['success'] = 'false';
			$response['message'] = 'Date must be selected.';
		}
		echo json_encode($response);
	}
	
	/**
	 * Page to show bar chart of vegetables based on the quatities or the total incomes
	 */
	public function top_vegetables()
	{
		$data['title'] = "Top vegetables";
		
		$this->load->model('veget_model');
		$this->load->model('lists_model');
		$this->load->model('customers_model');
		$this->load->helper('form');

		
		$data['vegets_list'] = array_column($this->veget_model->get_list(), 'name', 'id');
		$data['delivery_lists'] = array_column($this->lists_model->get_list(), 'name', 'id');
		$data['customers_list'] = array_column($this->customers_model->get_list(), 'name', 'id');
		
		// Load the templates in order with the data to print
		$this->load->view('templates/header', $data);
		$this->load->view('statistics/top_vegets', $data);
		$this->load->view('templates/footer', $data);
	}
	
	/**
	 * Endpoint REST API for CanvaJS charts
	 * @POST date 'start'		=> period start date
	 * @POST date 'end'			=> period end date
	 * @POST int 'vegetsExcl'	=> the ids of vegetables to exclude from the results
	 * @POST int 'customersExcl'=> the ids of customers to exclude from the results
	 * @POST int 'listIds'		=> the ids of the list to include in the results, if NULL it selects all lists
	 * @POST string 'sortedBy'	=> ['quantity', 'income'] Use the quantities of sold vegetables or the generated incomes
	 * @POST string 'perDelivery'=> 'true' to generate one serie per vegetable that contains one value per delivery. 
	 * 								Otherwise the deliveries are compiled in one value per vegetable.
	 */
	public function get_vegetables()
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
			return;
		}
		
		$start_date = $this->input->post('start');
		$end_date = $this->input->post('end');
		$vegets_excl = $this->input->post('vegetsExcl'); // Exclude these vegetables
		$customers_excl = $this->input->post('customersExcl'); // Exclude these customers
		$list_ids = $this->input->post('listIds'); // Show only those lists, or all if NULL
		$sorted_by = $this->input->post('sortedBy'); // Use quantity or income
		$per_deliveries = $this->input->post('perDelivery') == 'true'; // Create one data serie per vegetable with value per delivery
		
		if ( ! in_array($sorted_by, array('quantity', 'income')))
		{
			$sorted_by = 'quantity';
		}
		
		$units_code = $this->config->item('units');
		
		if ($start_date !== NULL && $end_date !== NULL) 
		{
			$response = array();
			$response['data'] = NULL;
			
			$prices = array('*' => array());
			
			$this->load->model('customers_model');
			$this->load->model('veget_model');
			$this->load->model('deliveries_model');
			
			$orders = $this->customers_model->get_orders_date_range('*', $start_date, $end_date);
			if (count($orders) > 0) 
			{
				$vegets = array();
				foreach ($orders as $order) 
				{
					if ($order->vegetables != NULL // Filter the results
							&& ($list_ids === NULL || in_array($order->l_id, $list_ids))
							&& ($customers_excl === NULL || ! in_array($order->c_id, $customers_excl)))
					{
						$vege = json_decode($order->vegetables);
						foreach($vege as $v_id => $v_qtt)
						{
							if ($vegets_excl === NULL || ! in_array($v_id, $vegets_excl)) // Exclude vegetables
							{
								if ( ! isset($vegets[$v_id])) // Create entry in result array
								{
									$vegets[$v_id] =  $per_deliveries ? array() : 0;
								}
								
								if ($per_deliveries && ! isset($vegets[$v_id][$order->delivery_date]))
								{
									$vegets[$v_id][$order->delivery_date] = 0;
								}
								
								if ($per_deliveries)
								{
									$sub_total = &$vegets[$v_id][$order->delivery_date];
								}
								else
								{
									$sub_total = &$vegets[$v_id];
								}
								
								switch($sorted_by)
								{
									case 'quantity':
										$sub_total +=  $v_qtt;
										break;
									case 'income':
										// Build price list for the delivery
										if (! isset($prices[$order->d_id]))
										{
											$delivery = $this->deliveries_model->get($order->d_id);
											if ($delivery != NULL)
											{
												$prices[$order->d_id] = array_column(json_decode($delivery->vegetables), 'price', 'id');
											}
										}
										if (isset($prices[$order->d_id][$v_id]))
										{
											$sub_total +=  ($v_qtt * $prices[$order->d_id][$v_id]);
										}
										else
										{
											if ( ! isset($prices['*'][$v_id]))
											{
												$v = $this->veget_model->get($v_id);
												$prices['*'][$v_id] = $v->price;
											}
											$sub_total +=  ($v_qtt * $prices['*'][$v_id]);
										}
										break;
								}
							}
						}
					}
				}
				
				asort($vegets);
				
				$response['data'] = array();
				foreach ($vegets as $v_id => $total)
				{
					$v = $this->veget_model->get($v_id);
					
					if ($v !== NULL)
					{
						if ($per_deliveries) // One data serie per vegetable
						{
							$data = array(
									'name' => $v->name,
									'data' => array(),
									'unit'	=> element(strtolower($v->unit), $units_code)
								);

							foreach($total as $delivery_date => $subtotal)
							{
								$data['data'][] = array(
									'label'	=> $delivery_date,
									'value'	=> $subtotal
								);
							}

							$response['data'][] = $data;
						}
						else // One data serie for all vegetables
						{
							$response['data'][] = array(
								'label' => $v->name,
								'value' => $total,
								'unit'	=> element(strtolower($v->unit), $units_code)
							);
						}
					}
				}
				
				if ( ! $per_deliveries)
				{
					$count = count($vegets);
					$median = floor($count/2);
					$per80 = floor($count/5);
					$values = array_values($vegets);
					if ( ! $count)
					{
						$response['median'] = 0;
					}
					elseif ($count & 1) // count is odd
					{
						$response['median'] = $values[$median];
						$response['per80'] = $values[$count - $per80];
					}
					else // count is even
					{
						$response['median'] = ($values[$median - 1] + $values[$median]) / 2;
						$response['per80'] = ($values[$count - $per80 - 1] + $values[$count - $per80]) / 2;
					}
				}
				
			}
			if ($response['data'] === NULL)
			{
				$response['success'] = 'false';
				$response['message'] = 'No order found';
			} 
			else 
			{
				$response['success'] = 'true';
			}
		}
		else 
		{
			$response['success'] = 'false';
			$response['message'] = 'Date must be selected.';
		}
		echo json_encode($response);
	}
	
	/**
	 * The page to show the overview of a vegetable: best customers, sales, etc.
	 */
	public function overview_vegetable()
	{
		$data['title'] = "Vegetable overview";
		
		$this->load->model('veget_model');
		$this->load->model('lists_model');
		$this->load->model('customers_model');
		$this->load->helper('form');

		
		$data['vegets_list'] = array_column($this->veget_model->get_list(), 'name', 'id');
		$data['delivery_lists'] = array_column($this->lists_model->get_list(), 'name', 'id');
		$data['customers_list'] = array_column($this->customers_model->get_list(), 'name', 'id');
		
		// Load the templates in order with the data to print
		$this->load->view('templates/header', $data);
		$this->load->view('statistics/overview_vegets', $data);
		$this->load->view('templates/footer', $data);
	}
	
	/**
	 * Endpoint REST API for CanvaJS charts
	 * @POST date 'start'		=> period start date
	 * @POST date 'end'			=> period end date
	 * @POST int 'customersExcl'=> the ids of customers to exclude from the results
	 * @POST int 'listIds'		=> the ids of the list to include in the results, if NULL it selects all lists
	 * @POST int 'vegetId'		=> Filter the results to show only this vegetables
	 */
	public function get_veget_summary()
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
			return;
		}
		
		$start_date = $this->input->post('start');
		$end_date = $this->input->post('end');
		$customers_excl = $this->input->post('customersExcl'); // Exclude these customers
		$list_ids = $this->input->post('listIds'); // Show only those lists, or all if NULL
		$veget_id = $this->input->post('vegetId'); // Filter the results to show only for this vegetable (or all if NULL)
		
		if ($start_date != NULL && $end_date != NULL && $veget_id != NULL) 
		{
			$response = array();
			$response['series'] = NULL;
			
			$this->load->model('customers_model');
			$this->load->model('deliveries_model');
			$this->load->model('veget_model');
			
			$veg = $this->veget_model->get($veget_id, TRUE);
			
			if ($veg != NULL)
			{
				$units_code = $this->config->item('units');
				$veg['unit'] = element(strtolower($veg['unit']), $units_code);
				$veg['url'] = base_url('vegetables/edit/'.$veget_id.'?rdtfrom=statistics/overview_vegetable');
				
				$lists = $this->veget_model->get_delivery_lists($veget_id);
				$veg['avaibility'] = array_column($lists, 'name');
				
				$response['vegetable'] = $veg;
			
				$totals = array();
				$delivery_dates = array();
				$total_qtt = 0;
				$total_income = 0;
				$count_deliveries = 0;
				$count_total_deliveries = 0;
				$count_orders = 0;
				$count_total_orders = 0;
				$count_avaibility = 0;
				$avaibility_deliveries = array();

				//Count for how many deliveries this veget was available
				$all_deliveries = $this->deliveries_model->get_list_date_range($start_date, $end_date);
				foreach($all_deliveries as $delivery)
				{
					if ($list_ids === NULL || in_array($delivery->l_id, $list_ids)) // filter lists
					{
						$vegets = json_decode($delivery->vegetables);
						if ($vegets != NULL)
						{
							if (in_array($veget_id, array_column($vegets, 'id'))) // Available for this delivery
							{
								$count_avaibility++;
								$avaibility_deliveries[] = $delivery->id;
							}
						}
						$count_total_deliveries++;
					}
				}
				
				$orders = $this->customers_model->get_orders_date_range('*', $start_date, $end_date);
				foreach ($orders as $order) 
				{
					if ($order->invoice != NULL // Filter the results
						&& ($list_ids === NULL || in_array($order->l_id, $list_ids))
						&& ($customers_excl === NULL || ! in_array($order->c_id, $customers_excl)))
					{
						$vegets = json_decode($order->vegetables, TRUE);
						if (count($vegets) > 0 && array_key_exists($veget_id, $vegets)) // veget have been ordered
						{
							if ( ! in_array($order->delivery_date, $delivery_dates))
							{
								$delivery_dates[] = $order->delivery_date;
								$count_deliveries++;
							}
							
							$total_qtt += $vegets[$veget_id];
							$count_orders++;

							// Build price list for the delivery
							if ( ! isset($prices[$order->d_id]))
							{
								$delivery = $this->deliveries_model->get($order->d_id);
								if ($delivery != NULL)
								{
									$sub_prices = array_column(json_decode($delivery->vegetables), 'price', 'id');
									if (isset($sub_prices[$veget_id]))
									{
										$prices[$order->d_id] = $sub_prices[$veget_id];
										$total_income += ($vegets[$veget_id] * $prices[$order->d_id]);
									}
									else
									{
										if ( ! isset($prices['*']))
										{
											$v = $this->veget_model->get($veget_id);
											$prices['*'] = $v->price;
										}
										$total_income += ($vegets[$veget_id] * $prices['*']);
									}
								}
							}
							else
							{
								$total_income += ($vegets[$veget_id] * $prices[$order->d_id]);	
							}
						}
						else
						{
							//skip this order
						}
						
						//Count how many potential orders
						if (in_array($order->d_id, $avaibility_deliveries)) // veget was available for this delivery
						{
							$count_total_orders++;
						}
					}
				}
				
				$response['summary'] = array(
					'total_quantity'	=> $total_qtt,
					'total_income'		=> format_kwacha($total_income),
					'per_delivery'		=> array(
							'avg_quantity'		=> $count_deliveries > 0 ? round($total_qtt / $count_deliveries, 2) : 0,
							'avg_income'		=> $count_deliveries > 0 ? format_kwacha($total_income / $count_deliveries) : 0
						),
					'per_order'			=> array(
							'avg_quantity'		=> $count_orders > 0 ? round($total_qtt / $count_orders, 2) : 0,
							'avg_income'		=> $count_orders > 0 ? format_kwacha($total_income / $count_orders) : 0
						),
					'scores'			=> array(
							'avaibility'		=> $count_total_deliveries > 0 ? round($count_avaibility / $count_total_deliveries, 4) : 0,
							'delivery'			=> $count_avaibility > 0 ? round($count_deliveries / $count_avaibility, 4) : 0,
							'order'				=> $count_total_orders > 0 ? round($count_orders / $count_total_orders, 4) : 0
					)
					
				);

				if ($count_orders <= 0)
				{
					$response['success'] = 'false';
					$response['message'] = 'No order found';
				} 
				else 
				{
					$response['success'] = 'true';
				}
				
			}
			else
			{
				$response['success'] = 'false';
				$response['message'] = 'Vegetable not found.';
			}
		}
		else 
		{
			$response['success'] = 'false';
			$response['message'] = 'Date must be selected.';
		}
		echo json_encode($response);
	}
}
