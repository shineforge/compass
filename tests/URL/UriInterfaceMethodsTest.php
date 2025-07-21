<?php

/*
 * This file is part of the Compass project, a modern URL manipulation library for PHP.
 * This project was developed with the assistance of generative AI code tools.
 *
 * (c) Shine United LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Compass\Tests\URL;

use Compass\URL;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(URL::class)]
final class UriInterfaceMethodsTest extends TestCase {
	/**
	 * @param array<string, string|int|null> $expected
	 */
	#[DataProvider('componentsProvider')]
	public function testGetComponents(string $url, array $expected): void {
		$uri = new URL($url);

		self::assertSame($expected['scheme'], $uri->getScheme());
		self::assertSame($expected['userInfo'], $uri->getUserInfo());
		self::assertSame($expected['host'], $uri->getHost());
		self::assertSame($expected['port'], $uri->getPort());
		self::assertSame($expected['path'], $uri->getPath());
		self::assertSame($expected['query'], $uri->getQuery());
		self::assertSame($expected['fragment'], $uri->getFragment());
	}

	/**
	 * @return iterable<string, array{string, array<string, string|int|null>}>
	 */
	public static function componentsProvider(): iterable {
		return [
			'full url' => [
				'https://user:pass@example.com:8080/p/a/t/h?query=1#fragment',
				[
					'scheme' => 'https',
					'userInfo' => 'user:pass',
					'host' => 'example.com',
					'port' => 8080,
					'path' => '/p/a/t/h',
					'query' => 'query=1',
					'fragment' => 'fragment',
				],
			],
			'http with standard port' => [
				'http://example.com:80/',
				[
					'scheme' => 'http',
					'userInfo' => '',
					'host' => 'example.com',
					'port' => null, // Standard port should be null
					'path' => '/',
					'query' => '',
					'fragment' => '',
				],
			],
			'no authority' => [
				'mailto:user@example.com',
				[
					'scheme' => 'mailto',
					'userInfo' => '',
					'host' => '',
					'port' => null,
					'path' => 'user@example.com',
					'query' => '',
					'fragment' => '',
				],
			],
			'scheme-relative' => [
				'//example.com/path',
				[
					'scheme' => '',
					'userInfo' => '',
					'host' => 'example.com',
					'port' => null,
					'path' => '/path',
					'query' => '',
					'fragment' => '',
				],
			],
			'empty components' => [
				'/path/only',
				[
					'scheme' => '',
					'userInfo' => '',
					'host' => '',
					'port' => null,
					'path' => '/path/only',
					'query' => '',
					'fragment' => '',
				],
			],
			'host only' => [
				'http://example.com',
				[
					'scheme' => 'http',
					'userInfo' => '',
					'host' => 'example.com',
					'port' => null,
					'path' => '', // Path should be empty, not '/'
					'query' => '',
					'fragment' => '',
				],
			],
			'path with dot segments' => [
				'http://example.com/a/b/../c/./d/',
				[
					'scheme' => 'http',
					'userInfo' => '',
					'host' => 'example.com',
					'port' => null,
					'path' => '/a/b/../c/./d/', // getPath() should return the raw path
					'query' => '',
					'fragment' => '',
				],
			],
		];
	}

	#[DataProvider('authorityProvider')]
	public function testGetAuthority(string $url, string $expectedAuthority): void {
		$uri = new URL($url);
		self::assertSame($expectedAuthority, $uri->getAuthority());
	}

	/**
	 * @return iterable<string, array{string, string}>
	 */
	public static function authorityProvider(): iterable {
		return [
			'no authority' => ['/path', ''],
			'host only' => ['//example.com', 'example.com'],
			'host and port' => ['//example.com:8080', 'example.com:8080'],
			'host and standard http port' => ['http://example.com:80', 'example.com'],
			'host and standard https port' => ['https://example.com:443', 'example.com'],
			'host and standard ftp port' => ['ftp://example.com:21', 'example.com'],
			'host and user' => ['//user@example.com', 'user@example.com'],
			'full authority' => ['https://user:pass@example.com:8080', 'user:pass@example.com:8080'],
		];
	}

	#[DataProvider('toStringProvider')]
	public function testToString(string $url, string $expectedString): void {
		$uri = new URL($url);
		self::assertSame($expectedString, (string) $uri);
	}

	/**
	 * @return iterable<string, array{string, string}>
	 */
	public static function toStringProvider(): iterable {
		return [
			'full' => ['http://user:pass@example.com:8080/p?q#f', 'http://user:pass@example.com:8080/p?q#f'],
			'standard https port' => ['https://example.com:443/path', 'https://example.com/path'],
			'standard ws port' => ['ws://example.com:80/socket', 'ws://example.com/socket'],
			'host only' => ['http://example.com', 'http://example.com'],
			'scheme relative' => ['//example.com', '//example.com'],
			'path with dot segments' => ['http://example.com/a/../b', 'http://example.com/b'], // __toString should canonicalize
			'path only' => ['/path', '/path'],
			'empty' => ['', ''],
		];
	}

	public function testWithScheme(): void {
		$uri = new URL('http://example.com');
		$newUri = $uri->withScheme('HTTPS');

		self::assertNotSame($uri, $newUri);
		self::assertSame('http', $uri->getScheme());
		self::assertSame('https', $newUri->getScheme());
		self::assertSame('https://example.com', (string) $newUri);

		$schemeRelativeUri = $newUri->withScheme('');
		self::assertSame('', $schemeRelativeUri->getScheme());
		self::assertSame('//example.com', (string) $schemeRelativeUri);
	}

	#[DataProvider('invalidSchemeProvider')]
	public function testWithSchemeThrowsExceptionForInvalidScheme(string $scheme): void {
		$uri = new URL('http://example.com');
		$this->expectException(InvalidArgumentException::class);
		$uri->withScheme($scheme);
	}

	/**
	 * @return iterable<string, array{string}>
	 */
	public static function invalidSchemeProvider(): iterable {
		return [
			'starts with digit' => ['1http'],
			'contains underscore' => ['my_scheme'],
			'contains colon' => ['http:'],
			'contains space' => ['http '],
		];
	}

	public function testWithUserInfo(): void {
		$uri = new URL('http://example.com/');

		$newUri = $uri->withUserInfo('user', 'pass');
		self::assertNotSame($uri, $newUri);
		self::assertSame('', $uri->getUserInfo());
		self::assertSame('user:pass', $newUri->getUserInfo());
		self::assertSame('http://user:pass@example.com/', (string) $newUri);

		$newUri2 = $newUri->withUserInfo('newuser');
		self::assertSame('newuser', $newUri2->getUserInfo());
		self::assertSame('http://newuser@example.com/', (string) $newUri2);

		$newUri3 = $newUri2->withUserInfo('');
		self::assertSame('', $newUri3->getUserInfo());
		self::assertSame('http://example.com/', (string) $newUri3);
	}

	public function testWithHost(): void {
		$uri = new URL('http://example.com');
		$newUri = $uri->withHost('EXAMPLE.ORG');

		self::assertNotSame($uri, $newUri);
		self::assertSame('example.com', $uri->getHost());
		self::assertSame('example.org', $newUri->getHost());
		self::assertSame('http://example.org', (string) $newUri);
	}

	public function testWithPort(): void {
		$uri = new URL('http://example.com');

		$newUri = $uri->withPort(8080);
		self::assertNotSame($uri, $newUri);
		self::assertNull($uri->getPort());
		self::assertSame(8080, $newUri->getPort());
		self::assertSame('http://example.com:8080', (string) $newUri);

		$newUri2 = $newUri->withPort(null);
		self::assertNull($newUri2->getPort());
		self::assertSame('http://example.com', (string) $newUri2);

		$newUri3 = $uri->withPort(80);
		self::assertNull($newUri3->getPort());
		self::assertSame('http://example.com', (string) $newUri3);
	}

	#[DataProvider('invalidPortProvider')]
	public function testWithPortThrowsExceptionOnInvalidPort(int $port): void {
		$uri = new URL('http://example.com');
		$this->expectException(InvalidArgumentException::class);
		$uri->withPort($port);
	}

	/**
	 * @return array<string, array{int}>
	 */
	public static function invalidPortProvider(): iterable {
		return [
			'port too low' => [0],
			'port too high' => [65536],
		];
	}

	public function testWithPath(): void {
		$uri = new URL('http://example.com/path');
		$newUri = $uri->withPath('/new/path');

		self::assertNotSame($uri, $newUri);
		self::assertSame('/path', $uri->getPath());
		self::assertSame('/new/path', $newUri->getPath());
		self::assertSame('http://example.com/new/path', (string) $newUri);
	}

	public function testWithQuery(): void {
		$uri = new URL('http://example.com?a=1');
		$newUri = $uri->withQuery('b=2&c=3');

		self::assertNotSame($uri, $newUri);
		self::assertSame('a=1', $uri->getQuery());
		self::assertSame('b=2&c=3', $newUri->getQuery());
		self::assertSame('http://example.com?b=2&c=3', (string) $newUri);
	}

	public function testWithFragment(): void {
		$uri = new URL('http://example.com#one');
		$newUri = $uri->withFragment('two');

		self::assertNotSame($uri, $newUri);
		self::assertSame('one', $uri->getFragment());
		self::assertSame('two', $newUri->getFragment());
		self::assertSame('http://example.com#two', (string) $newUri);
	}
}
