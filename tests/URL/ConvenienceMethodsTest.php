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
final class ConvenienceMethodsTest extends TestCase {
	public function testCreate(): void {
		$url = 'https://example.com/path';
		$urlObj = URL::create($url);
		self::assertInstanceOf(URL::class, $urlObj);
		self::assertSame('https://example.com/path', (string) $urlObj);
	}

	#[DataProvider('absoluteRelativeProvider')]
	public function testIsAbsoluteAndIsRelative(string $url, bool $isAbsolute): void {
		$uri = new URL($url);
		self::assertSame($isAbsolute, $uri->isAbsolute());
		self::assertSame(!$isAbsolute, $uri->isRelative());
	}

	/**
	 * @return iterable<string, array{string, bool}>
	 */
	public static function absoluteRelativeProvider(): iterable {
		return [
			'absolute http' => ['http://example.com/path', true],
			'absolute https' => ['https://example.com', true],
			'scheme-relative' => ['//example.com', false],
			'root-relative' => ['/path/to/file', false],
			'path-relative' => ['file.html', false],
			'absolute with relative path component' => ['http://example.com/a/../b', true],
		];
	}

	public function testGetUsernameAndPassword(): void {
		$uriWithBoth = new URL('https://user:pass@example.com');
		self::assertSame('user', $uriWithBoth->getUsername());
		self::assertSame('pass', $uriWithBoth->getPassword());

		$uriWithUser = new URL('https://user@example.com');
		self::assertSame('user', $uriWithUser->getUsername());
		self::assertNull($uriWithUser->getPassword());

		$uriWithNone = new URL('https://example.com');
		self::assertSame('', $uriWithNone->getUsername());
		self::assertNull($uriWithNone->getPassword());
	}

	public function testWithUsername(): void {
		$uri = new URL('http://user:pass@example.com');
		$newUri = $uri->withUsername('newuser');

		self::assertNotSame($uri, $newUri);
		self::assertSame('user', $uri->getUsername());
		self::assertSame('newuser', $newUri->getUsername());
		self::assertSame('pass', $newUri->getPassword(), 'Password should be preserved');
		self::assertSame('http://newuser:pass@example.com', (string) $newUri);
	}

	public function testWithPassword(): void {
		$uri = new URL('http://user:pass@example.com');
		$newUri = $uri->withPassword('newpass');

		self::assertNotSame($uri, $newUri);
		self::assertSame('pass', $uri->getPassword());
		self::assertSame('newpass', $newUri->getPassword());
		self::assertSame('user', $newUri->getUsername(), 'Username should be preserved');
		self::assertSame('http://user:newpass@example.com', (string) $newUri);

		$newUri2 = $newUri->withPassword(null);
		self::assertNull($newUri2->getPassword());
		self::assertSame('http://user@example.com', (string) $newUri2);
	}

	#[DataProvider('makeAbsoluteProvider')]
	public function testMakeAbsolute(string $relative, string $base, string $expected): void {
		$relativeUri = new URL($relative);
		$absoluteUri = $relativeUri->makeAbsolute($base);
		self::assertSame($expected, (string) $absoluteUri);
	}

	/**
	 * @return iterable<string, array{string, string, string}>
	 */
	public static function makeAbsoluteProvider(): iterable {
		return [
			'path-relative' => ['c.html', 'http://a.com/b/d.html', 'http://a.com/b/c.html'],
			'root-relative' => ['/c.html', 'http://a.com/b/d.html', 'http://a.com/c.html'],
			'scheme-relative' => ['//b.com/c.html', 'http://a.com/d.html', 'http://b.com/c.html'],
			'complex relative path' => ['../c/d', 'http://a.com/b/x/y.html', 'http://a.com/b/c/d'],
		];
	}

	public function testMakeAbsoluteThrowsOnRelativeBase(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Base URL must be absolute.');
		(new URL('path'))->makeAbsolute('/base');
	}

	#[DataProvider('makeRelativeProvider')]
	public function testMakeRelative(string $url, ?string $base, string $expected): void {
		$urlObj = new URL($url);
		$relative = $urlObj->makeRelative($base);
		self::assertSame($expected, (string) $relative);
	}

	/**
	 * @return iterable<string, array{string, string|null, string}>
	 */
	public static function makeRelativeProvider(): iterable {
		return [
			'same path file' => ['http://a.com/b/c', 'http://a.com/b/c', 'c'],
			'subdirectory' => ['http://a.com/b/c', 'http://a.com/b/', 'c'],
			'parent directory' => ['http://a.com/c', 'http://a.com/b/', '../c'],
			'up two levels and down' => ['http://a.com/x/y', 'http://a.com/b/c/d', '../../x/y'],
			'root relative' => ['http://a.com/b/c?q=1#f', null, '/b/c?q=1#f'],
		];
	}

	public function testMakeRelativeThrowsOnMismatch(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Cannot make URL relative: scheme or authority do not match.');
		(new URL('http://b.com/1'))->makeRelative('http://a.com/2');
	}

	public function testMakeRelativeThrowsOnRelativePathForRootRelative(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Cannot make a root relative URL from a relative path.');
		(new URL('b/c'))->makeRelative(null);
	}
}
