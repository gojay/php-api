<?php
namespace App\Controller;

class TestController extends BaseController
{
	public function index()
	{
		$request = $this->app->request();

		$classUpload = class_exists('App\Upload\Upload');
        $config = ([
            'class_upload' 	=> var_export($classUpload, true),
            'BASE_PATH'		=> BASE_PATH,
            'BASE_URL'		=> BASE_URL,
            'UPLOAD_PATH'	=> UPLOAD_PATH,
            'ENV' 			=> $_SERVER['REMOTE_ADDR']
        ]);

        $password = 'gojay86';
        /**
         * In this case, we want to increase the default cost for BCRYPT to 12.
         * Note that we also switched to BCRYPT, which will always be 60 characters.
         */
        $options = [
            'cost' => 9,
            'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM)
        ];
        $password_encrypt = password_hash($password, PASSWORD_BCRYPT, $options);
        $password_verify  = password_verify($password, $password_encrypt) ? 'VALID' : 'NOT VALID';

		echo json_encode([
			'request' => [
				'path' => $request->getPath(),
				'root' => $request->getRootUri(),
				'resource' => $request->getResourceUri()
			],
			'config' => $config,
			'passwords' => [
	        	'original' 	=> $password,
	        	// 'options'	=> $options,
	        	'encrypt'  	=> $password_encrypt,
	        	'verify'	=> $password_verify,
	        ],
            'random' => microtime(),
			'consumer' => [
				'key' => $this->generateKey(true),
				'secret' => $this->generateKey()
			]
		]);
	}

	public function generateKey ( $unique = false )
    {
        $key = md5(uniqid(rand(), true));
        if ($unique)
        {
            list($usec,$sec) = explode(' ',microtime());
            $key .= dechex($usec).dechex($sec);
        }
        return $key;
    }

    public function verification()
    {
        $curlResponse = '{"access_token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6IjU3NDFiNGY3Y2IwYWMzY2Q4OGM4OTRkY2Q4YWUxYjk3YmVkZTdhNDciLCJjbGllbnRfaWQiOiIxNDEzMDk4MzMxIiwidXNlcl9pZCI6IjEiLCJleHBpcmVzIjoxNDEzNzEwOTkwLCJ0b2tlbl90eXBlIjoiYmVhcmVyIiwic2NvcGUiOiJjbGllbnRzY29wZTEgY2xpZW50c2NvcGUyIn0.N2uOUbYdop8n5mtIx2zw2cMNEYzDh0QzC_k8h8wACqr_pQxBowliLfTIJBUNKZvYUOhVop8rYE1GuS-NHT8LJbUgiWu1HaCpd_sbOCQ871kIXNEuaXYXz7nKrR8iAvFPMTP-X2V6tZJUtAo6CU9eZnQYAdBrjC5LzjFWY3-CvBuauUNK4JXqWpD3-5gdPD0CoGjJL1PZVXeYuJ2AUNVgbSL4X5Ko9pfwPZxT7zJLJRHcWduTBcI25eTHxs3ECL7hOexeafWEq501AYKweqBJK5JvLQDLcZW9b4fPO1GuRpjpo-92oUkxaW2NhYLnzq2lSIzXmsroPdX7zIYmR0G_Fw","expires_in":3600,"token_type":"bearer","scope":"clientscope1 clientscope2"}';
        $curlResponse = '{"access_token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6ImU1ODkxMDdkODcwZWJmN2Q0NTE2NzI0MDY3NTkzMTA0YjlmYTY0NzEiLCJjbGllbnRfaWQiOiIxNDEzMDk4MzQ0IiwidXNlcl9pZCI6IjIiLCJleHBpcmVzIjoxNDEzNzE1MjkzLCJ0b2tlbl90eXBlIjoiYmVhcmVyIiwic2NvcGUiOiJjbGllbnRzY29wZTEifQ.wFRMn9vjgvtoMPg_lXws8FIzVT_6C7KO2UVwMN3HG15RNGkv66mw8dT9Irx4nuO-PE-A608Z6EXjova5nPXs2wxo5scgZ0H1zhbDF9EKIkLosp05EdLuL0l6rzbeM_NBWk27Kv36A2BqDGkbHv3LpiCBrRZAQFwAc02W1_qcbQNUS5NSDm4iJg5XlcGrkYzPyESxMlNC4a_8l6w2wG1xy7VJH9LhD6iKHBevcQRoNi4h1NMlsY2hAvTJ_0sEAXzFlyPKjHO4D2P95yNyl0BskdlLiTBCc0AhHoMvZMPd5WSjnLSNCNPS4uf3uufVTrb-hbSTtqcxoLSDa8_3K3ClwA","expires_in":3600,"token_type":"bearer","scope":"clientscope1"}';
        $curlResponse = '{"access_token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6IjFiMTI5NWE4NTgyOWIxOGE1MzM3MDI2MDQ3Zjg5ZjQwNDkwNjNlYjgiLCJjbGllbnRfaWQiOiIxNDEzMDk4MzQ0IiwidXNlcl9pZCI6IjIiLCJleHBpcmVzIjoxNDEzOTU1NTc5LCJ0b2tlbl90eXBlIjoiQmVhcmVyIiwic2NvcGUiOiJjbGllbnRzY29wZTEifQ.SkKe3nMm7C8cXXZZuHZue--Z-wDTOr3z-uavHC1LVHQFtAJa_sDPsMShZIncwXBJKky_Z_ShpExlcoavOawRVavn2Q1PMRngz7IoMxYdQrfLIR2Vc-7yh2LT6MOb68Z6Dt2mv0gA4adC8w8oGyg4q4TFRxCRSKp2bZ6gMdKRp-62aMxlbPSYr6YYdhDxILVRKo4zaDUs-YUVujqUlwUw_yzBeMVxiCKMqdAczPye8nLJbSrP7zUkxaQ-YDVjzBCvO0cGpjHFKQ1Kw6rybFOdWyt-5yX6aO2GFc5Sy-ykobl60EngZflGdPsYUz5zN-yimTftM8aLiRn49GcbQfzxoA","expires_in":3600,"token_type":"Bearer","scope":"clientscope1"}';
        
        $token = json_decode($curlResponse);

        $crypto_token = $token->access_token;

        $separator = '.';

        if (2 !== substr_count($crypto_token, $separator)) {
            throw new Exception("Incorrect access token format");
        }

        list($header, $payload, $signature) = explode($separator, $crypto_token);

        $decoded_signature = $this->urlSafeB64Decode($signature);

        // The header and payload are signed together
        $payload_to_verify = utf8_decode($header . $separator . $payload);

        // however you want to load your public key
        // $public_key = file_get_contents(__DIR__.'/../../SSH/1413098331_pubkey.pem');
        // $public_key = file_get_contents(__DIR__.'/../../SSH/1413098344_pubkey.pem');

        // // default is SHA256
        // $verified = openssl_verify($payload_to_verify, $decoded_signature, $public_key, 'sha256');

        // if ($verified !== 1) {
        //     throw new \Exception("Cannot verify signature");
        // }

        // output the Crypto Token payload
        var_dump(base64_decode($payload));
        die;
    }

    private function urlSafeB64Decode($b64)
    {
        $b64 = str_replace(array('-', '_'),
                array('+', '/'),
                $b64);

        return base64_decode($b64);
    }

    public function jwt()
    {
        $private_key = file_get_contents(__DIR__.'/../../SSH/1413098331_privkey.pem');
        $client_id   = '1413098331';
        $user_id     = '1';
        $grant_type  = 'urn:ietf:params:oauth:grant-type:jwt-bearer';

        $jwt = $this->generateJWT($private_key, $client_id, $user_id, 'http://localhost:8080');

        echo $jwt;
    }

    /**
     * Generate a JWT
     *
     * @param $privateKey The private key to use to sign the token
     * @param $iss The issuer, usually the client_id
     * @param $sub The subject, usually a user_id
     * @param $aud The audience, usually the URI for the oauth server
     * @param $exp The expiration date. If the current time is greater than the exp, the JWT is invalid
     * @param $nbf The "not before" time. If the current time is less than the nbf, the JWT is invalid
     * @param $jti The "jwt token identifier", or nonce for this JWT
     *
     * @return string
     */
    private function generateJWT($privateKey, $iss, $sub, $aud, $exp = null, $nbf = null, $jti = null)
    {
        if (!$exp) {
            $exp = time() + 1000;
        }

        $params = array(
            'iss' => $iss,
            'sub' => $sub,
            'aud' => $aud,
            'exp' => $exp,
            'iat' => time(),
        );

        if ($nbf) {
            $params['nbf'] = $nbf;
        }

        if ($jti) {
            $params['jti'] = $jti;
        }

        $jwtUtil = new \OAuth2\Encryption\Jwt();

        return $jwtUtil->encode($params, $privateKey, 'RS256');
    }

}