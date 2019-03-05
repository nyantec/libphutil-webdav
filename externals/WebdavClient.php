<?php

final class WebdavClientCurlException extends Exception {
	public function __construct($errno) {
		parent::__construct(curl_strerror($errno), $errno);
	}
}

final class WebdavClientHttpException extends Exception {
	public function __construct($status) {
		parent::__construct('HTTP status ' . $status, $status);
	}
}

class WebdavClient {
	protected $curl;
	protected $baseUrl;

	public function __construct() {
		$this->curl = curl_init();

		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
	}

	public function __destruct() {
		curl_close($this->curl);
	}

	public function setBaseUrl(string $url) {
		if (strncmp($url, 'https://', 8) != 0) {
			throw new Exception('HTTPS required');
		}

		$this->baseUrl = $url;
	}

	public function setUsername(string $username) {
		curl_setopt($this->curl, CURLOPT_USERNAME, $username);
	}

	public function setPassword(string $password) {
		curl_setopt($this->curl, CURLOPT_PASSWORD, $password);
	}

	protected function setUrl(string $path) {
		if (!$this->baseUrl) {
			throw new Exception('No base URL specified');
		}

		curl_setopt($this->curl, CURLOPT_URL, $this->baseUrl . $path);
	}

	protected function performRequest(string $method, string $path, $body = null) {
		$this->setUrl($path);

		curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);

		if ($body) {
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
		}

		$response = curl_exec($this->curl);

		if ($errno = curl_errno($this->curl)) {
			throw new WebdavClientCurlException($errno);
		}

		$status = curl_getinfo($this->curl, CURLINFO_RESPONSE_CODE);
		if ($status >= 400) {
			throw new WebdavClientHttpException($status);
		}

		return $response;
	}

	public function get(string $path) {
		$this->performRequest('GET', $path);
	}

	public function put(string $path, $body) {
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/octet-stream'
		));

		$this->performRequest('PUT', $path, $body);
	}

	public function mkcol(string $path) {
		$this->performRequest('MKCOL', $path);
	}

	public function delete(string $path) {
		$this->performRequest('DELETE', $path);
	}
}
