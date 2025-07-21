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
use Psr\Http\Message\UriInterface;

class URL implements UriInterface {
	private const STANDARD_PORTS = [
		'ftp' => 21,
		'ftps' => 990,
		'gopher' => 70,
		'http' => 80,
		'https' => 443,
		'imap' => 143,
		'imaps' => 993,
		'ldap' => 389,
		'ldaps' => 636,
		'nntp' => 119,
		'pop3' => 110,
		'pop3s' => 995,
		'rtsp' => 554,
		'smtp' => 25,
		'ssh' => 22,
		'telnet' => 23,
		'ws' => 80,
		'wss' => 443,
	];

	private string $scheme = '';
	private string $username = '';
	private ?string $password = null;
	private string $host = '';
	private ?int $port = null;
	private string $path = '';
	private string $query = '';
	private string $fragment = '';

	final public function __construct(?string $url = null) {
		if ($url === null || $url === '') {
			return;
		}

		$parts = parse_url($url);
		if ($parts === false) {
			throw new InvalidArgumentException("Unable to parse URL: {$url}");
		}

		$this->setScheme($parts['scheme'] ?? '');
		$this->setUserInfo($parts['user'] ?? '', $parts['pass'] ?? null);
		$this->setHost($parts['host'] ?? '');
		$this->setPort($parts['port'] ?? null);
		$this->setPath($parts['path'] ?? '');
		$this->setQuery($parts['query'] ?? '');
		$this->setFragment($parts['fragment'] ?? '');
	}

	final public function __toString(): string {
		$uri = '';

		if ($this->scheme !== '') {
			$uri .= $this->scheme . ':';
		}

		$authority = $this->getAuthority();
		if ($authority !== '') {
			$uri .= '//' . $authority;
		}

		$uri .= PathResolver::canonicalize($this->path);

		if ($this->query !== '') {
			$uri .= '?' . $this->query;
		}

		if ($this->fragment !== '') {
			$uri .= '#' . $this->fragment;
		}

		return $uri;
	}

	final public static function create(string $url): static {
		return new static($url);
	}

	final public function isAbsolute(): bool {
		if ($this->getScheme() === '') {
			return false;
		}

		if ($this->getAuthority() === '') {
			return false;
		}

		return true;
	}

	final public function isRelative(): bool {
		return !$this->isAbsolute();
	}

	final public function getScheme(): string {
		return $this->scheme;
	}

	final public function getUsername(): string {
		return $this->username;
	}

	final public function getPassword(): ?string {
		return $this->password;
	}

	final public function getUserInfo(): string {
		if ($this->username === '') {
			return '';
		}

		$userInfo = $this->username;
		if ($this->password !== null) {
			$userInfo .= ':' . $this->password;
		}

		return $userInfo;
	}

	final public function getHost(): string {
		return $this->host;
	}

	final public function getPort(): ?int {
		if ($this->port !== null && isset(self::STANDARD_PORTS[$this->scheme]) && $this->port === self::STANDARD_PORTS[$this->scheme]) {
			return null;
		}

		return $this->port;
	}

	final public function getAuthority(): string {
		if ($this->host === '') {
			return '';
		}

		$authority = $this->host;
		$userInfo = $this->getUserInfo();

		if ($userInfo !== '') {
			$authority = $userInfo . '@' . $authority;
		}

		$port = $this->getPort();
		if ($port !== null) {
			$authority .= ':' . $port;
		}

		return $authority;
	}

	final public function getPath(): string {
		return $this->path;
	}

	final public function getQuery(): string {
		return $this->query;
	}

	final public function getFragment(): string {
		return $this->fragment;
	}

	final public function withScheme(string $scheme): self {
		$new = clone $this;
		$new->setScheme($scheme);

		return $new;
	}

	final public function withUsername(string $username): self {
		$new = clone $this;
		$new->setUsername($username);

		return $new;
	}

	final public function withPassword(?string $password): self {
		$new = clone $this;
		$new->setPassword($password);

		return $new;
	}

	final public function withUserInfo(string $user, ?string $password = null): self {
		$new = clone $this;
		$new->setUserInfo($user, $password);

		return $new;
	}

	final public function withHost(string $host): self {
		$new = clone $this;
		$new->setHost($host);

		return $new;
	}

	final public function withPort(?int $port): self {
		$new = clone $this;
		$new->setPort($port);

		return $new;
	}

	final public function withPath(string $path): self {
		$new = clone $this;
		$new->setPath($path);

		return $new;
	}

	final public function withQuery(string $query): self {
		$new = clone $this;
		$new->setQuery($query);

		return $new;
	}

	final public function withFragment(string $fragment): self {
		$new = clone $this;
		$new->setFragment($fragment);

		return $new;
	}

	final public function makeRelative(string|UriInterface|null $base = null): self {
		// If base is null, make root-relative. This means removing authority info.
		if ($base === null) {
			if (PathResolver::isRelative($this->getPath())) {
				throw new InvalidArgumentException('Cannot make a root relative URL from a relative path.');
			}

			return (new self())
				->withPath($this->path)
				->withQuery($this->query)
				->withFragment($this->fragment)
			;
		}

		if (is_string($base)) {
			$base = static::create($base);
		}

		if ($base->getScheme() === '' || $base->getAuthority() === '') {
			throw new InvalidArgumentException('Base URL must be absolute to make a URL relative to it.');
		}

		$targetUrl = $this->isAbsolute() ? $this : $this->makeAbsolute($base);

		// If scheme or authority don't match, we can't make it relative.
		if ($targetUrl->getScheme() !== $base->getScheme() || $targetUrl->getAuthority() !== $base->getAuthority()) {
			throw new InvalidArgumentException('Cannot make URL relative: scheme or authority do not match.');
		}

		// The URLs can be relativized.
		$relativePath = PathResolver::makeRelative($targetUrl->getPath(), $base->getPath());

		// A relative URL is just path, query, and fragment.
		$new = new self();
		$new->setPath($relativePath);
		$new->setQuery($targetUrl->getQuery());
		$new->setFragment($targetUrl->getFragment());

		return $new;
	}

	final public function makeAbsolute(string|UriInterface $base): self {
		if (is_string($base)) {
			$base = static::create($base);
		} elseif (!$base instanceof self) {
			$base = static::create((string) $base);
		}

		if (!$base->isAbsolute()) {
			throw new InvalidArgumentException('Base URL must be absolute.');
		}

		if ($this->isAbsolute()) {
			// Canonicalize the path of an already absolute URL.
			return $this->withPath(PathResolver::canonicalize($this->path));
		}

		// A URL like `//example.com/path` (scheme-relative) only needs a scheme.
		if ($this->getHost() !== '') {
			return $this->withScheme($base->getScheme())->withPath(PathResolver::canonicalize($this->path));
		}

		// At this point, we have a path-relative URL (e.g., 'foo' or '/foo').
		// It needs scheme and authority from the base, and its path resolved.
		$new = $this
			->withScheme($base->getScheme())
			->withHost($base->getHost())
			->withPort($base->getPort())
		;

		$userInfo = $base->getUserInfo();
		if ($userInfo !== '') {
			$new = $new->withUserInfo($base->getUsername(), $base->getPassword());
		}

		$newPath = PathResolver::makeAbsolute($this->getPath(), $base->getPath());

		return $new->withPath($newPath);
	}

	private function setScheme(string $scheme): void {
		// According to RFC 3986, a scheme must start with a letter and can be
		// followed by any combination of letters, digits, plus, period, or hyphen.
		// An empty scheme is also valid and is used to remove the scheme.
		if ($scheme !== '' && !preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*$/', $scheme)) {
			throw new InvalidArgumentException(
				'Invalid scheme: a scheme must start with a letter and can only contain letters, digits, "+", "-", or "."',
			);
		}
		$this->scheme = strtolower($scheme);
	}

	private function setUsername(string $username): void {
		$this->username = $username;
	}

	private function setPassword(?string $password = null): void {
		$this->password = $password;
	}

	private function setUserInfo(string $username, ?string $password = null): void {
		$this->username = $username;
		$this->password = $password;
	}

	private function setHost(string $host): void {
		$this->host = strtolower($host);
	}

	private function setPort(?int $port): void {
		if ($port !== null && ($port < 1 || $port > 65535)) {
			throw new InvalidArgumentException('Invalid port: Port must be between 1 and 65535.');
		}
		$this->port = $port;
	}

	private function setPath(string $path): void {
		$this->path = $path;
	}

	private function setQuery(string $query): void {
		$this->query = $query;
	}

	private function setFragment(string $fragment): void {
		$this->fragment = $fragment;
	}
}
