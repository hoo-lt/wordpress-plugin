<?php

/**
 * One-time scaffold setup.
 *
 * Rewrites the placeholder namespace and identifiers for your new plugin,
 * then removes itself. Runs automatically after `composer create-project`,
 * or manually with `php scripts/setup.php`.
 */

$root = dirname(__DIR__);

$interactive = stream_isatty(STDIN) && getenv('COMPOSER_NO_INTERACTION') === false;

$ask = static function (string $label, string $default) use ($interactive): string {
	if (!$interactive) {
		return $default; // CI / --no-interaction: take the default
	}

	fwrite(STDOUT, sprintf('%s [%s]: ', $label, $default));

	$line = fgets(STDIN);
	if ($line === false) {
		return $default;
	}

	$line = trim($line);

	return $line === '' ? $default : $line;
};

$studly   = static fn (string $value): string => str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
$titleize = static fn (string $value): string => ucwords(str_replace(['-', '_'], ' ', $value));

// ---- gather identifiers (defaults derived from the directory name) ----

$slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', basename($root)));
$slug = trim($slug, '-');

$slug      = $ask('Plugin slug (text domain)', $slug);
$namespace = $ask('PHP namespace', $studly($slug));
$package   = $ask('Composer package (vendor/name)', 'your-vendor/' . $slug);
$name      = $ask('Plugin display name', $titleize($slug));

$migrationKey = str_replace('-', '_', $slug) . '_migrations';

// ---- rewrite source files (namespace + identifiers) ----

$rewrite = static function (string $contents) use ($namespace, $package, $slug, $name, $migrationKey): string {
	// Replace the plugin namespace without touching the framework namespace
	// (Hoo\WordPressPlugin is a prefix of Hoo\WordPressPluginFramework).
	$token    = "\x00FRAMEWORK\x00";
	$contents = str_replace('Hoo\\WordPressPluginFramework', $token, $contents);
	$contents = str_replace('Hoo\\WordPressPlugin', $namespace, $contents);
	$contents = str_replace($token, 'Hoo\\WordPressPluginFramework', $contents);

	return str_replace(
		[
			'wordpress-plugin.php',
			'hoo-lt/wordpress-plugin',
			"'wordpress-plugin'",
			'wordpress_plugin_migrations',
			'Plugin Name: WordPress Plugin',
			'WordPress Plugin',
		],
		[
			$slug . '.php',
			$package,
			"'" . $slug . "'",
			$migrationKey,
			'Plugin Name: ' . $name,
			$name,
		],
		$contents,
	);
};

$directory = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
$pruned    = new RecursiveCallbackFilterIterator($directory, static function (SplFileInfo $current): bool {
	if ($current->isDir()) {
		return !in_array($current->getFilename(), ['vendor', '.git', 'node_modules'], true);
	}

	return true;
});

foreach (new RecursiveIteratorIterator($pruned) as $file) {
	$path = $file->getPathname();

	if ($path === __FILE__ || basename($path) === 'composer.json') {
		continue;
	}
	if (!in_array(strtolower($file->getExtension()), ['php', 'md'], true)) {
		continue;
	}

	$contents = (string) file_get_contents($path);
	$updated  = $rewrite($contents);

	if ($updated !== $contents) {
		file_put_contents($path, $updated);
	}
}

// ---- rewrite composer.json (JSON-aware) ----

$composerPath = $root . '/composer.json';
$composer     = json_decode((string) file_get_contents($composerPath), true);

$composer['name']              = $package;
$composer['autoload']['psr-4'] = [$namespace . '\\' => 'src/'];
$composer['autoload']['files'] = ['functions/functions.php'];

unset($composer['scripts']['post-create-project-cmd']);
if (isset($composer['scripts']) && $composer['scripts'] === []) {
	unset($composer['scripts']);
}

file_put_contents(
	$composerPath,
	json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n",
);

// ---- rename the entry file to match the slug ----

$entry  = $root . '/wordpress-plugin.php';
$target = $root . '/' . $slug . '.php';
if (is_file($entry) && $entry !== $target) {
	rename($entry, $target);
}

// ---- clean up: remove this script ----

fwrite(STDOUT, sprintf(
	"\nConfigured: namespace %s, package %s, entry %s.php\nRun `composer dump-autoload` to refresh the autoloader.\n",
	$namespace,
	$package,
	$slug,
));

@unlink(__FILE__);
@rmdir(__DIR__); // remove scripts/ if now empty
