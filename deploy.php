<?php
define('SEMANTIC_VERSION_REGEX', '/^([0-9]+)\.([0-9]+)\.([0-9]+)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+[0-9A-Za-z-]+)?$/');

function increaseVersion($version, $mode) {
	preg_match(SEMANTIC_VERSION_REGEX, $version, $versionComponents);
	unset($versionComponents[0]);
	switch ($mode) {
		case 'patch':
			$versionComponents[3]++;
			break;
		case 'minor':
			$versionComponents[2]++;
			$versionComponents[3] = 0;
			break;
		case 'major':
			$versionComponents[1]++;
			$versionComponents[2] = 0;
			$versionComponents[3] = 0;
			break;
	}
	return implode('.', $versionComponents);
}

function getComposerMetadata() {
	$composerMetadata = json_decode(file_get_contents('composer.json'));
	$composerMetadata->version = isset($composerMetadata->version) ? $composerMetadata->version : '0.0.0';
	return $composerMetadata;
}

function getGithubCurl() {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, 'Release Script for ' . get('username') . '/' . get('repository'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERPWD, get('username') . ':' . get('password'));
	return $ch;
}

task('release:askPasswort', function(){
	$password = askHiddenResponse('Passwort for ' . get('username') . ':');
	set('password', $password);
});

task('release:increaseVersionPatch', function () {
	$composerMetadata = getComposerMetadata();
	$composerMetadata->version = increaseVersion($composerMetadata->version, 'patch');
	set('version', $composerMetadata->version);
	file_put_contents('composer.json', json_encode($composerMetadata, JSON_PRETTY_PRINT));
});

task('release:increaseVersionMinor', function () {
	$composerMetadata = getComposerMetadata();
	$composerMetadata->version = increaseVersion($composerMetadata->version, 'minor');
	set('version', $composerMetadata->version);
	file_put_contents('composer.json', json_encode($composerMetadata, JSON_PRETTY_PRINT));
});

task('release:increaseVersionMajor', function () {
	$composerMetadata = getComposerMetadata();
	$composerMetadata->version = increaseVersion($composerMetadata->version, 'major');
	set('version', $composerMetadata->version);
	file_put_contents('composer.json', json_encode($composerMetadata, JSON_PRETTY_PRINT));
});

task('release:fetchVersion', function () {
	$composerMetadata = getComposerMetadata();
	set('version', $composerMetadata->version);
});

task('release:commitComposer', function () {
	// writeln('Update and commit version in composer.json');
	runLocally('git add composer.json');
	runLocally('git commit -m "' . get('version') . '"');
});

task('release:tagRelease', function () {
	// writeln('tag current state with provided version number');
	runLocally('git tag "' . get('version') . '"');
});

task('release:pushTags', function () {
	// writeln('push tags to github');
	runLocally('git push origin master');
	runLocally('git push origin --tags');
});

task('release:removeCurrentTagFromRemote', function () {
	runLocally('git tag -d "' . get('version') . '"');
	runLocally('git push origin :refs/tags/' . get('version'));
});

task('release:createPhar', function(){
	if (file_exists('Repository/beard-current.phar')) {
		runLocally('rm Repository/beard-current.phar');
	}
	if (file_exists('Repository/beard-' . get('version') . '.phar')) {
		runLocally('rm Repository/beard-' . get('version') . '.phar');
	}
	runLocally('box build');
	runLocally('cp Repository/beard-current.phar Repository/beard-' . get('version') . '.phar');
});

task('release:setTestVersion', function(){
	set('version', 'test');
});

task('release:moveTestPhar', function(){
	if (file_exists('rm bin/beard-test')) {
		runLocally('rm bin/beard-test');
	}
	runLocally('cp Repository/beard-' . get('version') . '.phar bin/beard-test');
	runLocally('chmod +x bin/beard-test');
});

task('release:updateReleasesManifest', function(){
	$manifest = json_decode(file_get_contents('releases.json'), TRUE);
	foreach ($manifest as $key => $release) {
		if ($release['version'] == get('version')) {
			unset($manifest[$key]);
		}
	}

	$sha1 = sha1_file('Repository/beard-current.phar');
	$file = 'beard-' . get('version') . '.phar';
	$baseUrl = 'https://github.com/' . get('username') . '/' . get('repository') . '/releases/download/';
	$manifest[] = array(
		'name' => 'beard.phar',
		'sha1' => $sha1,
		'url' => $baseUrl . get('version') . '/' . $file,
		'version' => get('version')
	);

	file_put_contents('releases.json', json_encode($manifest, JSON_PRETTY_PRINT));

	runLocally('git add releases.json');
	runLocally('git commit -m "Added Version: ' . get('version') . '"');
	runLocally('git push origin master');
});

task('release:createGithubRelease', function() {
	$ch = getGithubCurl();

	$release = array(
		'tag_name' => get('version'),
		'name' => 'Release: ' . get('version')
	);
	$uri = 'https://api.github.com/repos/' . get('username') . '/' . get('repository') . '/releases';
	curl_setopt($ch, CURLOPT_URL, $uri);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($release));
	curl_setopt($ch, CURLOPT_POST, 1);

	$release = json_decode(curl_exec($ch));
	$releaseId = $release->id;
	set('releaseId', $releaseId);
});

task('release:destroyGithubRelease', function() {
	$ch = getGithubCurl();

	$uri = 'https://api.github.com/repos/' . get('username') . '/' . get('repository') . '/releases/tags/' . get('version');
	curl_setopt($ch, CURLOPT_URL, $uri);

	$release = json_decode(curl_exec($ch));
	$releaseId = $release->id;

	$uri = 'https://api.github.com/repos/' . get('username') . '/' . get('repository') . '/releases/' . $releaseId;
	$ch = getGithubCurl();
	curl_setopt($ch, CURLOPT_URL, $uri);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
	curl_exec($ch);
});

task('release:addPharToRelease', function(){
	$fileName = 'beard-' . get('version') . '.phar';
	$uri = 'https://uploads.github.com/repos/' . get('username') . '/' . get('repository') . '/releases/' . get('releaseId') . '/assets?name=' . $fileName;

	$ch = getGithubCurl();
	curl_setopt($ch, CURLOPT_URL, $uri);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/plain"));
	curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents('Repository/' . $fileName));
	curl_setopt($ch, CURLOPT_POST, 1);

	$result = curl_exec($ch);
	curl_close($ch);
});


set('username', 'mneuhaus');
set('repository', 'Beard');


task('release:createTestPhar', [
	'release:setTestVersion',
    'release:createPhar',
	'release:moveTestPhar'
]);

task('release:patch', [
	'release:askPasswort',
    'release:increaseVersionPatch',
    'release:commitComposer',
    'release:tagRelease',
    'release:pushTags',
    'release:createPhar',
    'release:updateReleasesManifest',
    'release:createGithubRelease',
    'release:addPharToRelease'
]);

task('release:minor', [
	'release:askPasswort',
    'release:increaseVersionMinor',
    'release:commitComposer',
    'release:tagRelease',
    'release:pushTags',
    'release:createPhar',
    'release:updateReleasesManifest',
    'release:createGithubRelease',
    'release:addPharToRelease'
]);

task('release:major', [
	'release:askPasswort',
    'release:increaseVersionMajor',
    'release:commitComposer',
    'release:tagRelease',
    'release:pushTags',
    'release:createPhar',
    'release:updateReleasesManifest',
    'release:createGithubRelease',
    'release:addPharToRelease'
]);

task('release:replaceCurrent', [
	'release:askPasswort',
    'release:fetchVersion',
    'release:destroyGithubRelease',
    'release:removeCurrentTagFromRemote',
    'release:tagRelease',
    'release:pushTags',
    'release:createPhar',
    'release:updateReleasesManifest',
    'release:createGithubRelease',
    'release:addPharToRelease'
]);
