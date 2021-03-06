<?php

declare(strict_types = 1);

namespace Consistence\Sentry\Metadata;

class PropertyMetadataTest extends \PHPUnit\Framework\TestCase
{

	public function testCreate()
	{
		$targetClass = 'BarClass';
		$sentryIdentificator = new SentryIdentificator($targetClass);
		$sentryMethods = [
			new SentryMethod(
				new SentryAccess('get'),
				'fooMethod',
				Visibility::get(Visibility::VISIBILITY_PUBLIC)
			),
		];
		$bidirectionalAssociation = new BidirectionalAssociation(
			$targetClass,
			'barProperty',
			BidirectionalAssociationType::get(BidirectionalAssociationType::ONE),
			[
				new SentryMethod(
					new SentryAccess('get'),
					'barMethod',
					Visibility::get(Visibility::VISIBILITY_PUBLIC)
				),
			]
		);
		$property = new PropertyMetadata(
			'fooProperty',
			'FooClass',
			$targetClass,
			$sentryIdentificator,
			false,
			$sentryMethods,
			$bidirectionalAssociation
		);

		$this->assertSame('fooProperty', $property->getName());
		$this->assertSame('FooClass', $property->getClassName());
		$this->assertSame($targetClass, $property->getType());
		$this->assertSame($sentryIdentificator, $property->getSentryIdentificator());
		$this->assertSame(false, $property->isNullable());
		$this->assertSame($sentryMethods, $property->getSentryMethods());
		$this->assertSame($bidirectionalAssociation, $property->getBidirectionalAssociation());
	}

	public function testCreateScalar()
	{
		$sentryIdentificator = new SentryIdentificator('int');
		$property = new PropertyMetadata(
			'fooProperty',
			'FooClass',
			'int',
			$sentryIdentificator,
			false,
			[],
			null
		);

		$this->assertSame('fooProperty', $property->getName());
		$this->assertSame('FooClass', $property->getClassName());
		$this->assertSame('int', $property->getType());
		$this->assertSame($sentryIdentificator, $property->getSentryIdentificator());
		$this->assertSame(false, $property->isNullable());
		$this->assertEmpty($property->getSentryMethods());
		$this->assertNull($property->getBidirectionalAssociation());
	}

	public function testGetSentryMethodByAccess()
	{
		$targetClass = 'BarClass';
		$sentryIdentificator = new SentryIdentificator($targetClass);
		$getMethod = new SentryMethod(
			new SentryAccess('get'),
			'getFoo',
			Visibility::get(Visibility::VISIBILITY_PUBLIC)
		);
		$setMethod = new SentryMethod(
			new SentryAccess('set'),
			'setFoo',
			Visibility::get(Visibility::VISIBILITY_PUBLIC)
		);
		$property = new PropertyMetadata(
			'fooProperty',
			'FooClass',
			$targetClass,
			$sentryIdentificator,
			false,
			[
				$getMethod,
				$setMethod,
			],
			null
		);

		$this->assertSame($setMethod, $property->getSentryMethodByAccessAndRequiredVisibility(
			new SentryAccess('set'),
			Visibility::get(Visibility::VISIBILITY_PRIVATE)
		));
	}

	public function testGetSentryMethodByAccessNotFound()
	{
		$targetClass = 'BarClass';
		$sentryIdentificator = new SentryIdentificator($targetClass);
		$setMethod = new SentryMethod(
			new SentryAccess('set'),
			'setFoo',
			Visibility::get(Visibility::VISIBILITY_PRIVATE)
		);
		$property = new PropertyMetadata(
			'fooProperty',
			'FooClass',
			$targetClass,
			$sentryIdentificator,
			false,
			[
				$setMethod,
			],
			null
		);

		$this->expectException(\Consistence\Sentry\Metadata\NoSuitableMethodException::class);

		$property->getSentryMethodByAccessAndRequiredVisibility(
			new SentryAccess('set'),
			Visibility::get(Visibility::VISIBILITY_PUBLIC)
		);
	}

	public function testGetSentryMethodByNameAndRequiredVisibility()
	{
		$sentryIdentificator = new SentryIdentificator('string');
		$fooMethod = new SentryMethod(
			new SentryAccess('get'),
			'fooMethod',
			Visibility::get(Visibility::VISIBILITY_PUBLIC)
		);
		$property = new PropertyMetadata(
			'fooProperty',
			'FooClass',
			'string',
			$sentryIdentificator,
			false,
			[$fooMethod],
			null
		);

		$this->assertSame($fooMethod, $property->getSentryMethodByNameAndRequiredVisibility(
			'fooMethod',
			Visibility::get(Visibility::VISIBILITY_PRIVATE)
		));
	}

	public function testGetSentryMethodByNameAndRequiredVisibilityMethodNotFound()
	{
		$property = new PropertyMetadata(
			'fooProperty',
			'FooClass',
			'string',
			new SentryIdentificator('string'),
			false,
			[],
			null
		);

		try {
			$property->getSentryMethodByNameAndRequiredVisibility(
				'fooMethod',
				Visibility::get(Visibility::VISIBILITY_PUBLIC)
			);
			$this->fail();
		} catch (\Consistence\Sentry\Metadata\MethodNotFoundForPropertyException $e) {
			$this->assertSame('FooClass', $e->getClassName());
			$this->assertSame('fooProperty', $e->getPropertyName());
			$this->assertSame('fooMethod', $e->getMethodName());
		}
	}

	public function testGetSentryMethodByNameAndRequiredVisibilityCaseInsensitive()
	{
		$sentryIdentificator = new SentryIdentificator('string');
		$fooMethod = new SentryMethod(
			new SentryAccess('get'),
			'fooMethod',
			Visibility::get(Visibility::VISIBILITY_PUBLIC)
		);
		$property = new PropertyMetadata(
			'fooProperty',
			'FooClass',
			'string',
			$sentryIdentificator,
			false,
			[$fooMethod],
			null
		);

		$this->assertSame($fooMethod, $property->getSentryMethodByNameAndRequiredVisibility(
			'FOOmethod',
			Visibility::get(Visibility::VISIBILITY_PRIVATE)
		));
	}

	public function testGetDefinedSentryAccess()
	{
		$targetClass = 'BarClass';
		$sentryIdentificator = new SentryIdentificator($targetClass);
		$privateSetMethod = new SentryMethod(
			new SentryAccess('set'),
			'setFoo',
			Visibility::get(Visibility::VISIBILITY_PRIVATE)
		);
		$publicSetMethod = new SentryMethod(
			new SentryAccess('set'),
			'setFoo',
			Visibility::get(Visibility::VISIBILITY_PUBLIC)
		);
		$property = new PropertyMetadata(
			'fooProperty',
			'FooClass',
			$targetClass,
			$sentryIdentificator,
			false,
			[
				$privateSetMethod,
				$publicSetMethod,
			],
			null
		);

		$this->assertEquals([new SentryAccess('set')], $property->getDefinedSentryAccess());
	}

}
