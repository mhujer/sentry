<?php

namespace Consistence\Sentry\Metadata;

use Consistence\Type\ArrayType\ArrayType;
use Consistence\Type\Type;

class ClassMetadata extends \Consistence\ObjectPrototype
{

	/** @var string */
	private $name;

	/** @var \Consistence\Sentry\Metadata\PropertyMetadata[] */
	private $properties;

	/**
	 * @param string $name
	 * @param \Consistence\Sentry\Metadata\PropertyMetadata[] $properties
	 */
	public function __construct($name, array $properties)
	{
		Type::checkType($name, 'string');
		$this->name = $name;
		$this->properties = $properties;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return \Consistence\Sentry\Metadata\PropertyMetadata[]
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	/**
	 * @param string $propertyName
	 * @return \Consistence\Sentry\Metadata\PropertyMetadata
	 */
	public function getPropertyByName($propertyName)
	{
		try {
			return ArrayType::getValueByCallback(
				$this->getProperties(),
				function (PropertyMetadata $propertyMetadata) use ($propertyName) {
					return $propertyMetadata->getName() === $propertyName;
				}
			);
		} catch (\Consistence\Type\ArrayType\ElementDoesNotExistException $e) {
			throw new \Consistence\Sentry\Metadata\PropertyNotFoundException($this->getName(), $propertyName, $e);
		}
	}

	/**
	 * @param string $methodName
	 * @param \Consistence\Sentry\Metadata\Visibility $requiredVisibility
	 * @return \Consistence\Sentry\Metadata\SentryMethodSearchResult
	 */
	public function getSentryMethodByNameAndRequiredVisibility($methodName, Visibility $requiredVisibility)
	{
		foreach ($this->getProperties() as $property) {
			try {
				$sentryMethod = $property->getSentryMethodByNameAndRequiredVisibility($methodName, $requiredVisibility);
				return new SentryMethodSearchResult($sentryMethod, $property);
			} catch (\Consistence\Sentry\Metadata\MethodNotFoundForPropertyException $e) {
				// continue
			}
		}

		throw new \Consistence\Sentry\Metadata\MethodNotFoundException($methodName, $this->getName());
	}

}
