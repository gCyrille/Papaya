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

/**
 * Returns information about a week
 * 
 * @return      array
 */
function week_info()
{
	return array(
		'days' => array(
			'sunday',
			'monday',
			'tuesday',
			'wednesday',
			'thursday',
			'friday',
			'saturday'
		),
		'abbrevdays' => array(
			'sun',
			'mon',
			'tue',
			'wed',
			'thu',
			'fri',
			'sat'
		),
		'daysinweek' => 7
	);
}

function date_regex()
{
	return '/^(?P<date0>\d{1,2}(\/|-)\d{2}(\/|-)(\d{2}|\d{4}))$|^(?P<date1>\d{1,2}(\/|-)\d{2})$|^(?P<date2>\d{4}(\/|-)\d{2}(\/|-)(\d{2}))$/';
}

function mysql_to_nice_date($mysqldate)
{
	$date = new DateTime($mysqldate);
	return $date->format('d F Y');
}

function mysql_to_suff_date($mysqldate)
{
	$date = new DateTime($mysqldate);
	return $date->format('j\<\s\u\p\>S\<\/\s\u\p\> F Y');
}