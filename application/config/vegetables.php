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

/*
|--------------------------------------------------------------------------
| Vegetables units
|--------------------------------------------------------------------------
| List of units for the vegetables, used for the collect, invoices, etc.
|
| 'internal_code'	=> 'Display name'
|
*/
$config['units'] = array(
		'kg'	=> 'Kg',
		'100gr'	=> '100g',
		'200gr'	=> '200g',
		'250gr'	=> '250g',
		'350gr'	=> '350g',
		'380gr'	=> '380g',
		'lts' 	=> 'Liters',
		'20lts' => '20Liters',
		'each'	=> 'Each',
		'bunch'	=> 'Bunch',
		'pack'	=> 'Pack',
		'piece' => 'Piece',
		'tray'	=> 'Tray'
	);

/*
|--------------------------------------------------------------------------
| Accouting categories
|--------------------------------------------------------------------------
| List of the accounting categories of the vegetables. 
| Use to generate the accounting after a delivery.
|
| 'internal_code'	=> 'Display name'
|
*/
$config['accounting_cats'] = array(
		'veg'		=> 'Vegetable',
		'cooking_oil'		=> 'Cooking oil',
		'chicken'		=> 'Chicken',
		'eggs_free_ranges'		=> 'Eggs free range',
		'eggs_regular'		=> 'Eggs regular',
		'rabbits'		=> 'Rabbits',
		'rice'		=> 'Rice',
		'pork'		=> 'Pork',
		'cow'		=> 'Cow',
		'bred'		=> 'Bred',
		'peanut_butter'		=> 'Nuts for school',
	);

/*
|--------------------------------------------------------------------------
| Accounting cash expenses categories
|--------------------------------------------------------------------------
| List of the categories for the cash expenses screen. 
| Use to generate the accounting after a delivery.
|
| 'internal_code'	=> 'Display name'
*/
$config['expenses_cats'] = array(
		'diesel'			=> 'Diesel bought',
		'zesco_talk_time'	=> 'Zesco, talk time',
		'park_fee'			=> 'Park fee',
		'other'				=> 'Other'
	);

/*
|--------------------------------------------------------------------------
| Export vegetable list XLSX template
|--------------------------------------------------------------------------
| List of parameters for the xlsx template used to export the vegetable list. 
| This is the one send by email to customers.
|
| To not use a template file, write this line:
| $config['tpl_export_veg_list'] = NULL;
|
*/
$config['tpl_export_veg_list'] = array(
	'filepath'		=> APPPATH.'/views/excel/',
	'filename'		=> 'Vegetable Liste.xlsx',
	'base_row_vege'	=> 15,
	'base_row_other'=> 14,
	'column_name'	=> 'B',
	'column_price'	=> 'C',
	'column_unit'	=> 'D',
	'column_order'	=> 'E',
	'column_total'	=> 'F'
);
// KEEP THIS AS AN EXAMPLE:
//$config['tpl_export_veg_list'] = array(
//	'filepath'		=> APPPATH.'/views/excel/',
//	'filename'		=> 'Vegetable List.xlsx',
//	'base_row_vege'	=> 4, // Row to use to insert vegetables
//	'base_row_other'=> 3, // Row to use to insert other items
//	'column_name'	=> 'A',
//	'column_price'	=> 'B',
//	'column_unit'	=> 'C',
//	'column_order'	=> 'D',
//	'column_total'	=> 'E'
//);

/*
|--------------------------------------------------------------------------
| Import vegetable list XLSX template
|--------------------------------------------------------------------------
| List of parameters used to import the vegetable list into orders. 
| This is the one received by email from customers.
|
| TO USE THE SAME FILE AS EXPORTED USE THIS LINE:
| $config['tpl_import_veg_list'] = $config['tpl_export_veg_list'];
|
*/
$config['tpl_import_veg_list'] = $config['tpl_export_veg_list'];
// KEEP THIS AS AN EXAMPLE:
//$config['tpl_import_veg_list'] = array(
//	'base_row_vege'	=> 2,
//	'base_row_other'=> 1,
//	'column_name'	=> 'A',
//	'column_order'	=> 'D'
//);

/*
|--------------------------------------------------------------------------
| Collect list XLSX template
|--------------------------------------------------------------------------
| List of parameters for the xlsx template used to export the collec list. 
| This is the one given to workers to prepare the craits.
|
*/
$config['tpl_collect_list'] = array(
		'filepath'		=> APPPATH.'/views/excel/',
		'filename'		=> 'collect_list.xlsx',
		'cell_date'		=> 'A1',
		'base_row'		=> 3,
		'base_column'		=> 'B',
		'last_column'		=> 'AE',
		'row_customer'		=> 1,
		'column_desc'		=> 'A',
		'column_unit'		=> 'AG',
		'column_total'		=> 'AF',
	);
// KEEP THIS AS AN EXAMPLE:
//$config['tpl_collect_list'] = array(
//	'filepath'		=> APPPATH.'/views/excel/',
//	'filename'		=> 'collect_list.xlsx',
//	'cell_date'		=> 'A1',
//	'base_row'		=> 3, // From which row insert vegetables
//	'base_column'	=> 'B', // From which column to insert customers
//	'last_column'	=> 'AE',// Until which column to inset customers
//	'row_customer'	=> 1, // On which row to insert customers
//	'column_desc'	=> 'A', // Name/Description of vegetable
//	'column_unit'	=> 'AG', // Unit of vegetables
//	'column_total'	=> 'AF' // Total for the row
//);

/*
|--------------------------------------------------------------------------
| Invoice XLSX template
|--------------------------------------------------------------------------
| List of parameters for the template used to export the invoices 
|
| > Export XLSX to pdf is not working, so the list is in HTML only for the moment.
| > See the file `views/invoices/tpl_invoice.php` for the template.
| > Only the header image can be change with this file.
|
*/
$config['tpl_invoice'] = array(
	'header_img'	=> './assets/images/header.png'
);
/*$config['tpl_invoice'] = array(
	'filepath'		=> APPPATH.'/views/excel/',
	'filename'		=> 'Invoice.xlsx',
	'cell_date'		=> 'D1', // Delivery date
	'cell_name'		=> 'A2', // Customer name
	'cell_num'		=> 'D2', // Invoice number
	'base_row_vege'	=> 6, // From which row insert vegetables
	'base_row_other'=> 5, // From which row insert other items
	'col_desc'		=> 'A', // Name/Description of vegetable
	'col_unit'		=> 'B', // Unit of vegetables
	'col_qty'		=> 'C', // Quantities of vegetables
	'col_price'		=> 'D', // Price of vegetables
	'col_amount'	=> 'E', // Total for the row
	'row_total'		=> 7, // Initial row for the total invoice
	'col_total'		=> 'E', // Column for the total
	'base_row_bal'	=> 9, // Initial row to insert balances
	'col_date_bal'	=> 'C', // Column for balance date
	'col_amount_bal'=> 'E', // Column for balance amount
	'row_big_total'	=> 11 // Initial row for total invoice + balance
);*/

/*
|--------------------------------------------------------------------------
| Password for editinh balance (User profil)
|--------------------------------------------------------------------------
|*/
$config['edit_password'] = 'euro';

/*
|--------------------------------------------------------------------------
| Papaya App version
|--------------------------------------------------------------------------
*/
$config['papaya_version'] = '1.8';