
import boto3
import base64
import time
import os
import sys
import json
from botocore.exceptions import ClientError

def get_secret():

    if "AWS_DEFAULT_PROFILE" not in os.environ:
        print("AWS_DEFAULT_PROFILE not set.", file=sys.stderr)
        sys.exit(1)

    if "AWS_DEFAULT_SECRET" not in os.environ:
        print("AWS_DEFAULT_SECRET not set.", file=sys.stderr)
        sys.exit(1);

    secret_name = os.environ['AWS_DEFAULT_SECRET']
    region_name = "eu-west-2"

    # Create a Secrets Manager client
    session = boto3.session.Session()
    client = session.client(
        service_name='secretsmanager',
        region_name=region_name
    )

    # In this sample we only handle the specific exceptions for the 'GetSecretValue' API.
    # See https://docs.aws.amazon.com/secretsmanager/latest/apireference/API_GetSecretValue.html
    # We rethrow the exception by default.

    try:
        get_secret_value_response = client.get_secret_value(
            SecretId=secret_name
        )
    except ClientError as e:
        if e.response['Error']['Code'] == 'DecryptionFailureException':
            # Secrets Manager can't decrypt the protected secret text using the provided KMS key.
            print(e.response['Error']['Code'], file=sys.stderr);
            sys.exit(1)
        elif e.response['Error']['Code'] == 'InternalServiceErrorException':
            # An error occurred on the server side.
            print(e.response['Error']['Code'], file=sys.stderr);
            sys.exit(1)
        elif e.response['Error']['Code'] == 'InvalidParameterException':
            # You provided an invalid value for a parameter.
            print(e.response['Error']['Code'], file=sys.stderr);
            sys.exit(1)
        elif e.response['Error']['Code'] == 'InvalidRequestException':
            # You provided a parameter value that is not valid for the current state of the resource.
            print(e.response['Error']['Code'], file=sys.stderr);
            sys.exit(1)
        elif e.response['Error']['Code'] == 'ResourceNotFoundException':
            # We can't find the resource that you asked for.
            print(e.response['Error']['Code'], file=sys.stderr);
            sys.exit(1)
    else:
        # Decrypts secret using the associated KMS key.
        # Depending on whether the secret is a string or binary, one of these fields will be populated.
        if 'SecretString' in get_secret_value_response:
            secret = get_secret_value_response['SecretString']
        else:
            decoded_binary_secret = base64.b64decode(get_secret_value_response['SecretBinary'])
            
    # Your code goes here. 
    settings = json.loads(secret);
    
    return (settings['host'], settings['dbname'], settings['username'], settings['password'])

if __name__ == '__main__':
    (db_host, db_name, db_user, db_pass) = get_secret()
    print(db_host, db_name, db_user, db_pass);
