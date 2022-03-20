<?php

require_once 'common.php';
require 'vendor/autoload.php';

use Aws\SecretsManager\SecretsManagerClient;
use Aws\Exception\AwsException;

/**
 * In this sample we only handle the specific exceptions for the 'GetSecretValue' API.
 * See https://docs.aws.amazon.com/secretsmanager/latest/apireference/API_GetSecretValue.html
 * We rethrow the exception by default.
 *
 * This code expects that you have AWS credentials set up per:
 * https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials.html
 */

class Vault
{
    public function __construct($profile, $region, $secret)
    {
        $client = new SecretsManagerClient([
            'profile' => $profile,
            'version' => '2017-10-17',
            'region' => $region,
        ]);

        try {
            $result = $client->getSecretValue([
                'SecretId' => $secret,
            ]);
        } catch (AwsException $e) {
            $error = $e->getAwsErrorCode();
            if ($error == 'DecryptionFailureException') {
                // Secrets Manager can't decrypt the protected secret text using the provided AWS KMS key.
                throw $e;
            }
            if ($error == 'InternalServiceErrorException') {
                // An error occurred on the server side.
                throw $e;
            }
            if ($error == 'InvalidParameterException') {
                // You provided an invalid value for a parameter.
                throw $e;
            }
            if ($error == 'InvalidRequestException') {
                // You provided a parameter value that is not valid for the current state of the resource.
                throw $e;
            }
            if ($error == 'ResourceNotFoundException') {
                // We can't find the resource that you asked for.
                throw $e;
            }
        }
        // Decrypts secret using the associated KMS CMK.
        // Depending on whether the secret is a string or binary, one of these fields will be populated.
        if (isset($result['SecretString'])) {
            $secret = $result['SecretString'];
        } else {
            $secret = base64_decode($result['SecretBinary']);
        }
        $this->contents = json_decode($secret, true);
    }
}

class Config
{
    public $settings = [];
    public function __construct()
    {
        $path = project_config_path();
        $ini = parse_ini_file($path, true);
        if (!$ini) {
            throw new Exception("parse_ini_file");
        }

        $this->settings = $ini;
        if ($ini['aws']['password_vault'] == true) {
            try {
                $vault = new Vault($ini['aws']['profile'], $ini['aws']['region'], $ini['aws']['secret']);
                $this->settings['database']['db_host'] = $vault->contents['host'];
                $this->settings['database']['db_name'] = $vault->contents['dbname'];
                $this->settings['database']['db_user'] = $vault->contents['username'];
                $this->settings['database']['db_pass'] = $vault->contents['password'];
            } catch (Exception $e) {
                error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
            }
        } 
    }
}
