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

	public function sendRequest($request)
	{
		// Check if request was passed in
		if (!$request) {
			throw new \Exception('No data was passed into the provision request');
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

		// Create response object to hold response data
		$response_obj = new \stdClass;

		// Convert the xml response into simple xml element
		$sxe_response = simplexml_load_string($response);

		$response_obj->status   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$response_obj->xml      = $response;
		$response_obj->response = $sxe_response;

		// If there was a curl error
		if (curl_errno($ch)) {
			$response_obj->curl_errorno = curl_errno($ch);
			$response_obj->curl_error   = curl_error($ch);
		}

		// Close the curl handler
		curl_close($ch);

		// Check for response errors
		$errors = $this->checkErrors($sxe_response);

		if (!empty($errors)) {
			$response_obj->errors = $errors;
		}

		// Check for response warnings
		$warnings = $this->checkWarnings($sxe_response);

		if (!empty($warnings)) {
			$response_obj->warnings = $warnings;
		}

		return $response_obj;
	}
	protected function checkErrors(\SimpleXMLElement $response)
	{
		$errors = array();

		if ($response->Error) {
			foreach ($response->Error as $error) {
				$error_data = array(
					'message' => (string)$error,
				);

				foreach ($error->attributes() as $name => $code) {
					$error_data[(string)$name] = (string)$code;
				}

				$errors[] = $error_data;
			}
		}

		return $errors;
	}
	protected function checkWarnings(\SimpleXMLElement $response)
	{
		$warnings = array();

		if ($response->Message) {
			foreach ($response->Message as $warning) {
				$warning_data = array(
					'message' => (string)$warning,
				);

				foreach ($warning->attributes() as $name => $value) {
					$warning_data[(string)$name] = (string)$value;
				}

				$warnings[] = $warning_data;
			}
		}

		return $warnings;
	}
}