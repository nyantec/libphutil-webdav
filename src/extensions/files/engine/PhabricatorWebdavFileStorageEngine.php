<?php

final class PhabricatorWebdavFileStorageEngine
	extends PhabricatorFileStorageEngine {

	public function getEngineIdentifier() {
		return 'webdav';
	}

	public function getEnginePriority() {
		return 50;
	}

	public function canWriteFiles() {
		return (
			PhabricatorEnv::getEnvConfig('storage.webdav.base-url') &&
			PhabricatorEnv::getEnvConfig('storage.webdav.username') &&
			PhabricatorEnv::getEnvConfig('storage.webdav.password'));
	}

	private function setupWebdavClient() {
		$libroot = dirname(phutil_get_library_root('libphutil-webdav'));
		require_once $libroot.'/externals/WebdavClient.php';

		$webdav = new WebdavClient;

		$webdav->setBaseUrl(PhabricatorEnv::getEnvConfig('storage.webdav.base-url'));
		$webdav->setUsername(PhabricatorEnv::getEnvConfig('storage.webdav.username'));
		$webdav->setPassword(PhabricatorEnv::getEnvConfig('storage.webdav.password'));

		return $webdav;
	}

	public function writeFile($data, array $params) {
		$webdav = $this->setupWebdavClient();

		$seed = Filesystem::readRandomCharacters(20);
		$parts = array();
		$parts[] = 'phabricator';

		$instance_name = PhabricatorEnv::getEnvConfig('cluster.instance');
		if (strlen($instance_name)) {
			$parts[] = $instance_name;
		}

		$parts[] = substr($seed, 0, 2);
		$parts[] = substr($seed, 2, 2);
		$parts[] = substr($seed, 4);

		$path = '';

		/* Create directory hierarchy */
		for ($i = 0; $i < count($parts) - 1; ++$i) {
			$path .= $parts[$i] . '/';

			try {
				$webdav->mkcol($path);
			} catch (WebdavClientHttpException $e) {
			}
		}

		$path .= end($parts);

		AphrontWriteGuard::willWrite();
		$webdav->put($path, $data);

		return $path;
	}

	public function readFile($handle) {
		$webdav = $this->setupWebdavClient();

		return $webdav->get($handle);
	}

	public function deleteFile($handle) {
		$webdav = $this->setupWebdavClient();

		AphrontWriteGuard::willWrite();
		$webdav->delete($handle);
	}
}
