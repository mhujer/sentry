<?php

namespace Consistence\Sentry\Type;

class FooClass extends \Consistence\ObjectPrototype implements \Consistence\Sentry\SentryAware
{

	private $fooProperty;

	private $children;

	public function __construct()
	{
		$this->children = [];
	}

}
