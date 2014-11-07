# Creating a Public and Private Key Pair

# private key
openssl genrsa -out 1413098344_privkey.pem 2048

# public key
openssl rsa -in 1413098344_privkey.pem -pubout -out 1413098344_pubkey.pem