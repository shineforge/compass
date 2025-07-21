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

namespace Compass;

use InvalidArgumentException;

/**
 * PathResolver class provides methods to resolve and manipulate URL paths.
 */
final class PathResolver {
	/**
	 * Checks if a path is absolute.
	 *
	 * An absolute path is defined as one beginning with a forward slash '/'.
	 *
	 * @param string $path the path to check
	 *
	 * @return bool true if the path is absolute, false otherwise
	 */
	public static function isAbsolute(string $path): bool {
		// Check if the path starts with a slash (indicating it's absolute to the root of the domain)
		if (str_starts_with($path, '/')) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if a path is relative.
	 *
	 * A relative path is any path that is not absolute.
	 *
	 * @param string $path the path to check
	 *
	 * @return bool true if the path is relative, false otherwise
	 */
	public static function isRelative(string $path): bool {
		if (self::isAbsolute($path)) {
			return false;
		}

		return true;
	}

	/**
	 * Resolves '..' and '.' segments in a path and returns a canonical representation.
	 *
	 * This method cleans up a path by:
	 * - Resolving parent directory segments ('..').
	 * - Resolving current directory segments ('.').
	 * - Removing multiple consecutive slashes.
	 *
	 * It correctly handles both absolute and relative paths, preserving the intent of a
	 * trailing slash which typically indicates a directory.
	 *
	 * For example:
	 * - '/a/b/../c/' becomes '/a/c/'
	 * - 'a/./b/../' becomes './'
	 *
	 * @param string $path the path to canonicalize
	 *
	 * @return string the canonicalized path
	 */
	public static function canonicalize(string $path): string {
		if ($path === '') {
			return '';
		}

		$isAbsolute = self::isAbsolute($path);

		// A path that ends in a forward slash is potentially different from one that does not.
		$hasTrailingSlash = substr($path, -1) === '/';

		$segments = explode('/', $path);
		$output = [];

		foreach ($segments as $segment) {
			// Ignore empty segments (from multiple slashes) and current directory references.
			if ($segment === '' || $segment === '.') {
				continue;
			}

			if ($segment === '..') {
				// If we have a segment to pop, and it's not '..', pop it.
				// This allows for sequences like '../../'.
				if (count($output) > 0 && end($output) !== '..') {
					array_pop($output);
				} elseif (!$isAbsolute) {
					// For relative paths, we can have leading '..' segments.
					$output[] = '..';
				}
				// If it's an absolute path, '..' at the root has no effect.
			} else {
				$output[] = $segment;
			}
		}

		$result = implode('/', $output);

		// An empty result means we've resolved to the current directory.
		if ($result === '') {
			// For absolute paths, this is the root directory.
			if ($isAbsolute) {
				return '/';
			}

			// For relative paths, it's './' if it was a directory path, otherwise ''.
			return $hasTrailingSlash ? './' : '';
		}

		// Add leading slash for absolute paths.
		if ($isAbsolute) {
			$result = '/' . $result;
		}

		// Add trailing slash if it was a directory path.
		if ($hasTrailingSlash && $result !== '/') {
			$result .= '/';
		}

		return $result;
	}

	/**
	 * Creates a relative path from a base path to a target path.
	 *
	 * @param string $path the target path
	 * @param string $base the base path to make the target path relative to
	 *
	 * @return string the relative path
	 *
	 * @throws InvalidArgumentException if paths are incompatible (one absolute, one relative)
	 */
	public static function makeRelative(string $path, string $base): string {
		// 1. Compatibility check: cannot resolve between absolute and relative paths.
		if (self::isAbsolute($path) !== self::isAbsolute($base)) {
			throw new InvalidArgumentException('Cannot make a relative path between an absolute and a relative path.');
		}

		// 2. Canonicalize paths to handle '..' and '.' segments consistently.
		$path = self::canonicalize($path);
		$base = self::canonicalize($base);

		// 3. Get segments for both paths.
		// Filter out empty segments from leading/trailing/multiple slashes.
		$fromSegs = array_values(array_filter(explode('/', $base)));
		$toSegs = array_values(array_filter(explode('/', $path)));

		// If the base path points to a file (no trailing '/'), resolve from its directory.
		// We use the original $base string to check for a trailing slash.
		if ($base !== '/' && !str_ends_with($base, '/')) {
			array_pop($fromSegs);
		}

		// 4. Remove common leading segments.
		while (isset($fromSegs[0], $toSegs[0]) && $fromSegs[0] === $toSegs[0]) {
			array_shift($fromSegs);
			array_shift($toSegs);
		}

		// 5. Construct the relative path.
		$relativeParts = array_merge(
			array_fill(0, count($fromSegs), '..'),
			$toSegs,
		);

		// If the target path is an ancestor directory of the base, ensure the
		// resulting relative path ends with a trailing slash to indicate a directory.
		if ($path !== '/' && empty($toSegs) && str_ends_with($path, '/')) {
			$relativeParts[] = '';
		}

		if (empty($relativeParts)) {
			// This occurs when the path and base are the same directory.
			return '.';
		}

		return implode('/', $relativeParts);
	}

	/**
	 * Makes a path absolute by resolving it against a base path.
	 *
	 * @param string $path The path to make absolute. Can be relative or absolute.
	 * @param string $base the absolute base path to resolve against
	 *
	 * @return string the resulting absolute path
	 *
	 * @throws InvalidArgumentException if the base path is not absolute
	 */
	public static function makeAbsolute(string $path, string $base): string {
		// If the path is already absolute, just canonicalize and return it.
		if (self::isAbsolute($path)) {
			return self::canonicalize($path);
		}

		// A relative path cannot be resolved to an absolute one against a relative base.
		if (self::isRelative($base)) {
			throw new InvalidArgumentException('Base path must be absolute to resolve an absolute path.');
		}

		// If the base path points to a file (no trailing '/'), resolve from its directory.
		$baseDirectory = (substr($base, -1) === '/') ? $base : dirname($base);

		// If path is '.' or '', it refers to the base directory. We must ensure the
		// result is treated as a directory path (ends with a slash).
		if ($path === '.' || $path === '') {
			// If baseDirectory is not just '/', ensure it ends with a slash.
			if ($baseDirectory !== '/' && substr($baseDirectory, -1) !== '/') {
				$baseDirectory .= '/';
			}

			return self::canonicalize($baseDirectory);
		}

		// Combine the base directory and the relative path, then canonicalize to resolve '..' etc.
		return self::canonicalize(rtrim($baseDirectory, '/') . '/' . $path);
	}
}
