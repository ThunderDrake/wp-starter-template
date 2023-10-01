<?php

use AxelotTP\Helper;
use AxelotTP\Site;

function ct(): Site {
	return Site::getInstance();
}

function cth(): Helper {
	return Helper::getInstance();
}
