#### LICENSE
  
Wordpress Vulnerability Scanner 

The MIT License (MIT)

Copyright (c) 2015 Ahmad Ramadhan Amizudin

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

ramadhan.amizudin at gmail dot com  

#### Arguments
```
Usage: php app.php [options]

Options:
        -h, --help                      Show this help message.
        -u, --url                       Target URL (e.g. "http://mywp.com/")
        -f, --force                     Ignore if target is not wordpress.
        --wpvulndb                      Use WPVulnDB API Instead of local database. (Powered by wpvulndb.com API)

Request:
        --ua, --user-agent              Set user-agent, default: random user agent
        --proxy                         Set proxy. eg: protocol://[username:password@]host:port

Scanning:
        -d, --default                   Default scanning mode
                                        Equivalent to --dp,--dt,--b option
        -b, --basic                     Show basic information about target
                                        Eg: robots.txt path, check multisite, registration enable, readme file
        --dp, --discover-plugin         Discover plugin(s) via html source
        --dt, --discover-theme          Discover theme(s) via html source
        --ep, --enumerate-plugin                Enumerate plugins
        --et, --enumerate-theme         Enumerate themes
        --eu, --enumerate-user          Enumerate users
        --bf, --bruteforce              Bruteforce Mode

Bruteforce:
        -x, --xmlrpc                    Bruteforce through XMLRPC interface.
        -p, --protect                   Check if the site is protected before bruteforcing.
        -U, --user                      Set username or file containing user lists.
        -w, --wordlist                  Set wordlist file(full path), default will use built-in wordlist.
```

#### Requirement
  
- At least PHP 5.3  
- PHP cURL Extension  
- PHP JSON Extension  


#### Install

*Installing on Windows*  

```Download http://windows.php.net/downloads/releases/php-5.3.21-Win32-VC9-x86.msi```  

```Tick cURL Extension on installation step```  

#### To Do List
- Rewrite code to be more modular
- Unit Tests
- Add Proxy Support
- Add Web UI
- Add Password audit support
- Add custom wordpress directory(wp-content and wp-plugin)
- Add support for static user agent(currently random)
- Vulnerability Database (currently using https://wpvulndb.com)


### Contributing

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Make your changes
4. Commit your changes (`git commit -am 'Added some feature'`)
5. Push to the branch (`git push origin my-new-feature`)
6. Create new Pull Request
7. Pat yourself on the back for being so awesome

