<?php

declare(strict_types = 1);

namespace Consistence\Sentry\Type;

class MissingArgumentException extends \Consistence\PhpException
{

	/** @var mixed[] */
	private $args;

	/** @var int */
	private $requiredCountOfArguments;

	/**
	 * @param mixed[] $args
	 * @param int $requiredCountOfArguments
	 * @param \Throwable|null $previous
	 */
	public function __construct(array $args, int $requiredCountOfArguments, \Throwable $previous = null)
	{
		parent::__construct(
			sprintf('Sentry requires at least %d arguments, %d given', $requiredCountOfArguments, count($args)),
			$previous
		);
		$this->args = $args;
		$this->requiredCountOfArguments = $requiredCountOfArguments;
	}

	/**
	 * @return mixed[]
	 */
	public function getArgs(): array
	{
		return $this->args;
	}

	public function getRequiredCountOfArguments(): int
	{
		return $this->requiredCountOfArguments;
	}

}
