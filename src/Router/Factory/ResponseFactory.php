<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Router\Factory;

use Laminas\Diactoros\ResponseFactory as LaminasResponseFactory;

class ResponseFactory extends LaminasResponseFactory
{
	public function __construct()
	{
		parent::__construct();
	}
}