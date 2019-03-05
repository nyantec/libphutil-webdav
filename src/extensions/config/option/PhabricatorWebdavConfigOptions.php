<?php

final class PhabricatorWebdavConfigOptions
	extends PhabricatorApplicationConfigOptions {

	public function getName() {
		return pht('WebDAV');
	}

	public function getDescription() {
		return pht('Configure integration with WebDAV File Storage Engine.');
	}

	public function getIcon() {
		return 'fa-cloud-upload';
	}

	public function getGroup() {
		return 'files';
	}

	public function getOptions() {
		return array(
			$this->newOption('storage.webdav.base-url', 'string', null)
				->setDescription(pht('Base URL.')),
			$this->newOption('storage.webdav.username', 'string', null)
				->setDescription(pht('Authentication user name.')),
			$this->newOption('storage.webdav.password', 'string', null)
				->setDescription(pht('Authentication password.')));
	}
}
