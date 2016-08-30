<?php
namespace Articus\DataTransfer\Mapper;

interface MapperInterface
{
	/**
	 * @param array $array
	 * @return array
	 */
	public function __invoke(array $array);
}