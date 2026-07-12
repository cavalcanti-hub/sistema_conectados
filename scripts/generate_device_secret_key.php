<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit(1);}
if(!function_exists('sodium_crypto_secretbox')){fwrite(STDERR,"libsodium indisponivel.\n");exit(1);}
echo base64_encode(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES)).PHP_EOL;
