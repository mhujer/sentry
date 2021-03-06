<?php

declare(strict_types = 1);

namespace Consistence\Sentry\SentryIdentificatorParser;

use Consistence\Sentry\Metadata\SentryIdentificator;

class SentryIdentificatorParserTest extends \PHPUnit\Framework\TestCase
{

	/**
	 * @return mixed[][]
	 */
	public function matchesProvider(): array
	{
		return [
			[new SentryIdentificator('string'), 'string', false, false, null],
			[new SentryIdentificator('string[]'), 'string', true, false, null],
			[new SentryIdentificator('string|NULL'), 'string', false, true, null],
			[new SentryIdentificator('string|null'), 'string', false, true, null],
			[new SentryIdentificator('string[]|NULL'), 'string', true, true, null],
			[new SentryIdentificator('string[]|null'), 'string', true, true, null],
			[new SentryIdentificator('string[][]'), 'string', true, false, null],
			[new SentryIdentificator('string[][]|null'), 'string', true, true, null],
			[new SentryIdentificator('Foo'), 'Foo', false, false, null],
			[new SentryIdentificator('\Foo'), 'Foo', false, false, null],
			[new SentryIdentificator('Foo\Bar'), 'Foo\Bar', false, false, null],
			[new SentryIdentificator('\Foo\Bar'), 'Foo\Bar', false, false, null],
			[new SentryIdentificator('\Foo\Bar[]'), 'Foo\Bar', true, false, null],
			[new SentryIdentificator('\Foo\Bar[]|null'), 'Foo\Bar', true, true, null],
			[new SentryIdentificator('\Foo\Bar foobar'), 'Foo\Bar', false, false, null],
			[new SentryIdentificator('\Foo\Bar nullable'), 'Foo\Bar', false, false, null],
			[new SentryIdentificator('\Collection of \Foo\Bar'), 'Collection', false, false, null],
			[new SentryIdentificator('Foo::Bar'), 'Bar', false, false, 'Foo'],
			[new SentryIdentificator('\Foo::\Bar'), 'Bar', false, false, 'Foo'],
			[new SentryIdentificator('Long\Class\Name\Which\Tests\The\Backtracking\Limit::Bar'), 'Bar', false, false, 'Long\Class\Name\Which\Tests\The\Backtracking\Limit'],
		];
	}

	/**
	 * @return string[][]
	 */
	public function doesNotMatchProvider(): array
	{
		return [
			[''],
			['Long\Class\Name\Which\Tests\The\Backtracking\Limit::'],
		];
	}

	/**
	 * @dataProvider matchesProvider
	 *
	 * @param \Consistence\Sentry\Metadata\SentryIdentificator $sentryIdentificator
	 * @param string $expectedType
	 * @param bool $expectedMany
	 * @param bool $expectedNullable
	 * @param string|null $sourceClass
	 */
	public function testMatch(
		SentryIdentificator $sentryIdentificator,
		string $expectedType,
		bool $expectedMany,
		bool $expectedNullable,
		$sourceClass
	)
	{
		$parser = new SentryIdentificatorParser();
		$result = $parser->parse($sentryIdentificator);
		$this->assertInstanceOf(SentryIdentificatorParseResult::class, $result);
		$this->assertSame($sentryIdentificator, $result->getSentryIdentificator());
		$this->assertSame($expectedType, $result->getType());
		$this->assertSame($expectedMany, $result->isMany());
		$this->assertSame($expectedNullable, $result->isNullable());
		$this->assertSame($sourceClass, $result->getSourceClass());
	}

	/**
	 * @dataProvider doesNotMatchProvider
	 *
	 * @param string $pattern
	 */
	public function testDoesNotMatch(string $pattern)
	{
		$parser = new SentryIdentificatorParser();
		$sentryIdentificator = new SentryIdentificator($pattern);
		try {
			$parser->parse($sentryIdentificator);
			$this->fail();
		} catch (\Consistence\Sentry\SentryIdentificatorParser\PatternDoesNotMatchException $e) {
			$this->assertSame($sentryIdentificator, $e->getSentryIdentificator());
		}
	}

}
