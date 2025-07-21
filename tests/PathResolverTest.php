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

namespace Compass\Tests;

use Compass\PathResolver;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(PathResolver::class)]
final class PathResolverTest extends TestCase {
	/**
	 * Tests the isAbsolute method with various path inputs.
	 *
	 * @param string $path     the path to test
	 * @param bool   $expected the expected boolean outcome
	 */
	#[DataProvider('isAbsoluteProvider')]
	public function testIsAbsolute(string $path, bool $expected): void {
		self::assertSame($expected, PathResolver::isAbsolute($path));
	}

	/**
	 * Data provider for testIsAbsolute.
	 *
	 * @return array<string, array{string, bool}> an array of test cases, each with a path and the expected boolean result
	 */
	public static function isAbsoluteProvider(): array {
		return [
			'absolute path' => ['/a/b/c', true],
			'root path' => ['/', true],
			'relative path' => ['a/b/c', false],
			'empty path' => ['', false],
			'path with ..' => ['../a/b', false],
		];
	}

	/**
	 * Tests the isRelative method with various path inputs.
	 *
	 * @param string $path     the path to test
	 * @param bool   $expected the expected boolean outcome
	 */
	#[DataProvider('isRelativeProvider')]
	public function testIsRelative(string $path, bool $expected): void {
		self::assertSame($expected, PathResolver::isRelative($path));
	}

	/**
	 * Data provider for testIsRelative.
	 *
	 * @return array<string, array{string, bool}> an array of test cases, each with a path and the expected boolean result
	 */
	public static function isRelativeProvider(): array {
		return [
			'absolute path' => ['/a/b/c', false],
			'root path' => ['/', false],
			'relative path' => ['a/b/c', true],
			'empty path' => ['', true],
			'path with ..' => ['../a/b', true],
		];
	}

	/**
	 * Tests the canonicalize method with various path inputs.
	 *
	 * @param string $path     the path to test
	 * @param string $expected the expected canonicalized path
	 */
	#[DataProvider('canonicalizeProvider')]
	public function testCanonicalize(string $path, string $expected): void {
		self::assertSame($expected, PathResolver::canonicalize($path));
	}

	/**
	 * Data provider for testCanonicalize.
	 *
	 * @return array<string, array{string, string}> an array of test cases, each with a path and its expected canonicalized form
	 */
	public static function canonicalizeProvider(): array {
		return [
			'empty path' => ['', ''],
			'root path' => ['/', '/'],
			'simple absolute' => ['/a/b/c', '/a/b/c'],
			'simple relative' => ['a/b/c', 'a/b/c'],
			'trailing slash absolute' => ['/a/b/', '/a/b/'],
			'trailing slash relative' => ['a/b/', 'a/b/'],
			'multiple slashes' => ['/a//b/c', '/a/b/c'],
			'dot segments' => ['/a/./b/../c', '/a/c'],
			'relative dot segments' => ['a/./b', 'a/b'],
			'leading dot segment' => ['./a', 'a'],
			'parent segments at root' => ['/../a', '/a'],
			'parent segments relative' => ['../../a', '../../a'],
			'complex relative' => ['a/b/../../c/./d', 'c/d'],
			'resolves to root' => ['/a/..', '/'],
			'resolves to current dir' => ['a/..', ''],
			'resolves to current dir with slash' => ['a/../', './'],
			'just a dot' => ['.', ''],
			'just a dot with slash' => ['./', './'],
			'just a parent' => ['..', '..'],
			'just a parent with slash' => ['../', '../'],
			'very complex' => ['/a/b/./../c/.././d/', '/a/d/'],
		];
	}

	/**
	 * Tests the makeRelative method with various path and base inputs.
	 *
	 * @param string $path     the target path
	 * @param string $base     the base path
	 * @param string $expected the expected relative path
	 */
	#[DataProvider('makeRelativeProvider')]
	public function testMakeRelative(string $path, string $base, string $expected): void {
		self::assertSame($expected, PathResolver::makeRelative($path, $base));
	}

	/**
	 * Data provider for testMakeRelative.
	 *
	 * @return array<string, array{string, string, string}> an array of test cases, each with a target path, a base path, and the expected relative path
	 */
	public static function makeRelativeProvider(): array {
		return [
			'same path' => ['/a/b/c', '/a/b/c', 'c'],
			'simple subdirectory' => ['/a/b/c', '/a/b/', 'c'],
			'from file base' => ['/a/b/c', '/a/b/file', 'c'],
			'up one level' => ['/a/c', '/a/b/', '../c'],
			'up multiple levels' => ['/c/d', '/a/b/', '../../c/d'],
			'from root' => ['/a/b', '/', 'a/b'],
			'to root' => ['/', '/a/b', '..'],
			'to root with file' => ['/file', '/a/b', '../file'],
			'relative paths' => ['a/b/c', 'a/d', 'b/c'],
			'relative paths 2' => ['a/b/c', 'a/d/', '../b/c'],
			'complex relative' => ['../a/b', '../a/c', 'b'],
			'complex relative 2' => ['../a/b', '../a/c/', '../b'],
			'base is deeper than path' => ['/a/b', '/a/b/c/d/', '../..'],
			'base is deeper than path 2' => ['/a/b/', '/a/b/c/d/', '../../'],
			'path with trailing dot' => ['/a/b/c.', '/a/b/', 'c.'],
			'base with trailing dot' => ['/a/b/c', '/a/b/base.', 'c'],
			'both with trailing dot' => ['/a/b/c.', '/a/b/base.', 'c.'],
		];
	}

	/**
	 * Tests that makeRelative throws an exception when attempting to create a relative path between an absolute and a relative path.
	 */
	public function testMakeRelativeThrowsException(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Cannot make a relative path between an absolute and a relative path.');
		PathResolver::makeRelative('/a/b', 'c/d');
	}

	public function testMakeRelativeThrowsExceptionFlipped(): void {
		// Tests that makeRelative throws an exception when attempting to create a relative path between a relative and an absolute path (flipped arguments).
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Cannot make a relative path between an absolute and a relative path.');
		PathResolver::makeRelative('a/b', '/c/d');
	}

	/**
	 * Tests the makeAbsolute method with various path and base inputs.
	 *
	 * @param string $path     the path to make absolute
	 * @param string $base     the base path
	 * @param string $expected the expected absolute path
	 */
	#[DataProvider('makeAbsoluteProvider')]
	public function testMakeAbsolute(string $path, string $base, string $expected): void {
		self::assertSame($expected, PathResolver::makeAbsolute($path, $base));
	}

	/**
	 * Data provider for testMakeAbsolute.
	 *
	 * @return array<string, array{string, string, string}> an array of test cases, each with a relative path, a base path, and the expected absolute path
	 */
	public static function makeAbsoluteProvider(): array {
		return [
			'already absolute' => ['/a/b', '/c/d', '/a/b'],
			'simple relative' => ['b/c', '/a/', '/a/b/c'],
			'from file base' => ['c', '/a/b', '/a/c'],
			'with parent segment' => ['../c', '/a/b/', '/a/c'],
			'from root' => ['a', '/', '/a'],
			'complex' => ['../../d', '/a/b/c/', '/a/d'],
			'empty path' => ['', '/a/b/', '/a/b/'],
			'dot path' => ['.', '/a/b/', '/a/b/'],
			'combine with file' => ['img.jpg', '/path/to/page.html', '/path/to/img.jpg'],
		];
	}

	/**
	 * Tests that makeAbsolute throws an exception when the base path is not absolute.
	 */
	public function testMakeAbsoluteThrowsException(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Base path must be absolute to resolve an absolute path.');
		PathResolver::makeAbsolute('a/b', 'c/d');
	}
}
