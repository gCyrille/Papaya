<div class="ui stackable grid container">
	<h1 class="header sixteen wide column">About Papaya</h1>
	<div class="row">
		<p class="three wide column"><strong>What is Papaya?</strong></p>
		<p class="thirteen wide column">Papaya is a software developed to help in the management of the deliveries of vegetables. It has been design for the exclusive use by Chikowa Youth Development Centre.</p>
	</div>
	<div class="sixteen wide column">
		<img class="ui big image centered" src="<?php echo base_url('assets/images/header.png'); ?>">
	</div>
	<div class="row">
		<p class="three wide column"><strong>License</strong></p>
		<div class="thirteen wide column">
			<p>This software is under the MIT License (MIT)</p>

			<p>Copyright (c) 2018 - 2019, Chikowa Youth Development Centre</p>

			<p>Permission is hereby granted, free of charge, to any person obtaining a copy
			of this software and associated documentation files (the "Software"), to deal
			in the Software without restriction, including without limitation the rights
			to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
			copies of the Software, and to permit persons to whom the Software is
			furnished to do so, subject to the following conditions:</p>

			<p>The above copyright notice and this permission notice shall be included in
			all copies or substantial portions of the Software.</p>

			<p>THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
			IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
			FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
			AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
			LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
			OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
			THE SOFTWARE.</p>
		</div>
	</div>
	<div class="row">
		<p class="three wide column"><strong>Technologies</strong></p>
		<p class="thirteen wide column">Papaya is made with PHP and HTML and use the development frameworks <a href="https://codeigniter.com/">CodeIgniter</a> and <a href="http://semantic-ui.com">Semantic-UI</a>.<br />
			Papaya also use a third party library to produce Excel files: <u>PhpSpreadsheet</u>. This library is under LGPL license and is not modified. The code source is available <a href="https://github.com/PHPOffice/PhpSpreadsheet">here</a>.</p>
	</div>
	<div class="row">
		<p class="three wide column"><strong>Requirements</strong></p>
		<p class="thirteen wide column">
			To use this software you need an Apacha Server with PHP 5.6 or newer, and a Mysql server (5.1+). <br />
			The simplest way is to use <a href="http://www.apachefriends.org">XAMPP software</a> (7.1.7 or newer) with Apacha and Mysql. 
		</p>
	</div>
	<div class="row">
		<p class="three wide column"><strong>Authors</strong></p>
		<p class="thirteen wide column">Papaya has been developed in 2018 by <?php echo safe_mailto('contact@cyrilleg.me', 'Cyrille Gandon'); ?> with the help of Hubert Rime, Thomas Ménard and Barthélémy Fuseau. </p>
	</div>
	<div class="row">
		<p class="three wide column"><strong>Download</strong></p>
		<div class="thirteen wide column">
			<p>If you need to reinstall Papaya in a new environnment you can download this actual installation: </p>
			<a class="ui basic button" href="<?php echo base_url('administration/download_app'); ?>"><i class="icon download"></i> Download Papaya</a>
			<span class="ui small compact message">Don't forget to backup the config file and the database if you want to use it with the new installation!</span>
		</div>
	</div>
</div>