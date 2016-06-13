# SSL支持

---

本章将详细讲解如何制作证书以及如何开启Swoole的SSL的单向、双向认证。

## 准备工作
选择任意路径，执行如下命令创建文件夹结构

```shell
mkdir ca
cd ca
mkdir private
mkdir server
mkdir newcerts
```

在ca目录下创建`openssl.conf`文件，文件内容如下
```
[ ca ]  
default_ca      = foo                   # The default ca section  
   
[ foo ]  
dir            = /path/to/ca         # top dir  
database       = /path/to/ca/index.txt          # index file.  
new_certs_dir  = /path/to/ca/newcerts           # new certs dir  
   
certificate    = /path/to/ca/private/ca.crt         # The CA cert  
serial         = /path/to/ca/serial             # serial no file  
private_key    = /path/to/ca/private/ca.key  # CA private key  
RANDFILE       = /path/to/ca/private/.rand      # random number file  
   
default_days   = 365                     # how long to certify for  
default_crl_days= 30                     # how long before next CRL  
default_md     = md5                     # message digest method to use  
unique_subject = no                      # Set to 'no' to allow creation of  
                                         # several ctificates with same subject.  
policy         = policy_any              # default policy  
   
[ policy_any ]  
countryName = match  
stateOrProvinceName = match  
organizationName = match  
organizationalUnitName = match  
localityName            = optional  
commonName              = optional  
emailAddress            = optional
```

其中，`/path/to/ca/`是ca目录的绝对路径。

## 创建ca证书

在ca目录下创建一个shell脚本，命名为`new_ca.sh`。文件内容如下：
```bash
#!/bin/sh  
openssl genrsa -out private/ca.key  
openssl req -new -key private/ca.key -out private/ca.csr  
openssl x509 -req -days 365 -in private/ca.csr -signkey private/ca.key -out private/ca.crt  
echo FACE > serial  
touch index.txt  
openssl ca -gencrl -out private/ca.crl -crldays 7 -config "./openssl.conf"
```

执行`sh new_ca.sh`命令，创建ca证书。生成的证书存放于private目录中。

> **注意**
在创建ca证书的过程中，需要输入一些信息。其中，countryName、stateOrProvinceName、organizationName、organizationalUnitName这四个选项的内容必须要填写，并且需要记住。在生成后续的证书过程中，要保证这四个选项的内容一致。

## 创建服务端证书
在ca目录下创建一个shell脚本，命名为`new_server.sh`。文件内容如下：
```bash
#!/bin/sh  
openssl genrsa -out server/server.key  
openssl req -new -key server/server.key -out server/server.csr  
openssl ca -in server/server.csr -cert private/ca.crt -keyfile private/ca.key -out server/server.crt -config "./openssl.conf"  
```
执行`sh new_ca.sh`命令，创建ca证书。生成的证书存放于server目录中。

## 创建客户端证书
在ca目录下创建一个shell脚本，命名为`new_client.sh`。文件内容如下：
```bash
#!/bin/sh  

base="./"  
mkdir -p $base/users/  
openssl genrsa -des3 -out $base/users/client.key 1024  
openssl req -new -key $base/users/client.key -out $base/users/client.csr  
openssl ca -in $base/users/client.csr -cert $base/private/ca.crt -keyfile $base/private/ca.key -out $base/users/client.crt -config "./openssl.conf"  
openssl pkcs12 -export -clcerts -in $base/users/client.crt -inkey $base/users/client.key -out $base/users/client.p12  
```
执行`sh new_ca.sh`命令，创建ca证书。生成的证书存放于users目录中。
进入users目录，可以看到有一个`client.p12`文件，这个就是客户端可用的证书了，但是这个证书是不能在php中使用的，因此需要做一次转换。命令如下：

```shell
openssl pkcs12 -clcerts -nokeys -out cer.pem -in client.p12
openssl pkcs12 -nocerts -out key.pem -in client.p12
```
以上两个命令会生成cer.pem和key.pem两个文件。其中，生成key.pem时会要求设置密码，这里记为`client_pwd`

> **注意**
如果在创建客户端证书时，就已经给client.p12设置了密码，那么在转换格式的时候，需要输入密码进行转换

## 最终结果
以上步骤执行结束后，会得到不少文件，其中需要用的文件如下表所示：
| 文件名 | 路径 | 说明 |
| --- | --- | --- |
| ca.crt | ca/private/ | ca证书 |
| server.crt | ca/server/ | 服务器端证书 |
| server.key | ca/server/ | 服务器端秘钥 |
| cer.pem | ca/client/ | 客户端证书 |
| key.pem | ca/client/ | 客户端秘钥 |

# SSL单向认证

## Swoole开启SSL
Swoole开启SSL功能需要如下参数：

```php
$server = new swoole_server("127.0.0.1", "9501" , SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL );
$server = new swoole_http_server("127.0.0.1", "9501" , SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL );
```
并在swoole的配置选项中增加如下两个选项：

```php
$server->set(array(
    'ssl_cert_file' => '/path/to/server.crt',
    'ssl_key_file' =>  '/path/to/server.key',
));
```

这时，swoole服务器就已经开启了单向SSL认证，可以通过`https://127.0.0.1:9501/`进行访问。

# SSL双向认证

## 服务器端设置
双向认证指服务器也要对发起请求的客户端进行认证，只有通过认证的客户端才能进行访问。
为了开启SSL双向认证，swoole需要额外的配置参数如下：

```php
$server->set(array(
    'ssl_cert_file' => '/path/to/server.crt',
    'ssl_key_file' =>  '/path/to/server.key',
    'ssl_client_cert_file' => '/path/to/ca.crt',
    'ssl_verify_depth' => 10,
));
```

## 客户端设置
这里我们使用CURL进行https请求的发起。
首先，需要配置php.ini,增加如下配置：

```
curl.cainfo=/path/to/ca.crt
```

发起curl请求时，增加如下配置项：
```php
$ch = curl_init();

curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '2'); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);   // 只信任CA颁布的证书
curl_setopt($ch, CURLOPT_SSLCERT, "/path/to/cer.pem");
curl_setopt($ch, CURLOPT_SSLKEY,  "/path/to/key.pem");
curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
curl_setopt($ch, CURLOPT_SSLCERTPASSWD, '******'); // 创建客户端证书时标记的client_pwd密码
```

这时，就可以发起一次https请求，并且被swoole服务器验证通过了。





