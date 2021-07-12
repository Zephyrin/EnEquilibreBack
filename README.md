# En Équilibre API

## Configuration

### Generate the SSH Keys for production:

```bash
$ openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
$ openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```
Then update *.env* and write the key into *JWT_PASSPHRASE*

Update *.env.local.php* with ```composer dump-env prod```

### Update the superadmin password

# Update

`cd /srv/http/EnEquilibreBack`
`sudo git pull`
`sudo APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear`
`sudo chown -R http .`
`sudo systemctl restart http`
