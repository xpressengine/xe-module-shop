<?php

abstract class APIAbstract
{
    public function request($url, $data, $skip_ssl_verify = false)
    {
		if(is_string($data))
		{
			$post_string = $data;
		}
		else
		{
			$post_string = http_build_query($data);
		}
        if(__DEBUG__)
        {
            ShopLogger::log('REQUEST ' . $url . ' ' . $post_string);
        }

        // Request
        $request = curl_init($url);
        curl_setopt($request, CURLOPT_HEADER, 0);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_POSTFIELDS, $post_string);

		if($skip_ssl_verify)
		{
			curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);
		}
		else
		{
			curl_setopt($request, CURLOPT_SSL_VERIFYPEER, TRUE);
		}

        $response = curl_exec($request);
		if(!$response)
		{
			$error = curl_error($request);
			throw new APIException("CURL error: " . $error);
		}
        if(__DEBUG__)
        {
            ShopLogger::log('RESPONSE ' . $response);
        }

        curl_close ($request);
        return $response;
    }
}