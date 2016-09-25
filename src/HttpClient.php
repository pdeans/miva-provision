<?php

namespace pdeans\Miva\Provision;

class HttpClient
{
	protected $prv_url;
	protected $prv_token;

	public function __construct($url, $token)
	{
		$this->setPrvUrl($url);
		$this->setPrvToken($token);
	}

	public function setPrvUrl($url)
	{
		$this->prv_url = $url;
	}

	public function getPrvUrl()
	{
		return $this->prv_url;
	}

	public function setPrvToken($token)
	{
		$this->prv_token = $token;
	}

	public function getPrvToken()
	{
		return $this->prv_token;
	}

	protected function minifyRequest($request)
	{
		return preg_replace('/\s{2,}|[\r\n\t]+/', '', $request);
	}

	public function sendRequest($request, $minify = false)
	{
		// Check if request was passed in
		if (!$request) {
			throw new \Exception('No data was passed into the provision request');
		}

		// Minify the request
		if ($minify) {
			$request = $this->minifyRequest($request);
		}

		// Create a new curl handler
		$ch = curl_init($this->prv_url);

		// Set the curl handler options
		curl_setopt_array($ch, array(
			CURLOPT_POST           => 1,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_POSTFIELDS     => $request,
			CURLOPT_HTTPHEADER     => array(
				'Content-type: text/xml',
				'MMProvision-Access-Token: ' . $this->prv_token,
				'Content-length: ' . strlen($request)
			),
		));

		// Make curl request, store response data and status code
		$response = curl_exec($ch);
		$status   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// If there was a curl error
		if (curl_errno($ch)) {
			throw new \Exception('Curl Error: ('.curl_error($ch).')', curl_errno($ch));
		}

		// Close the curl handler
		curl_close($ch);

		// Bad status response from curl request
		if ($status >= 400 && $status <= 599) {
			throw new \Exception('Curl request responded with bad status ('.$status.')', $status);
		}

		return $this->getResponseData($response);
	}

	protected function getResponseData($response)
	{
		$errors   = array();
		$warnings = array();

		// Parse the xml response
		$xml = simplexml_load_string($response);

		if ($xml->Error) {
			foreach ($xml->Error as $error) {
				$error_data = array(
					'message' => (string)$error,
				);

				foreach ($error->attributes() as $name => $code) {
					$error_data[(string)$name] = (string)$code;
				}

				$errors[] = $error_data;
			}
		}

		if ($xml->Message) {
			foreach ($xml->Message as $warning) {
				$warning_data = array(
					'message' => (string)$warning,
				);

				foreach ($warning->attributes() as $name => $value) {
					$warning_data[(string)$name] = (string)$value;
				}

				$warnings[] = $warning_data;
			}
		}

		return array(
			'raw'      => $response,
			'errors'   => $errors,
			'warnings' => $warnings,
		);
	}
}