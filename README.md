#### Introduction

A Wordpress Scanner written in PHP, focus on vulnerability assessment and security audit of misconfiguration in the Wordpress installation.
Wordpress Scanner is capable of finding the flaws in the Wordpress installation and will provide all the information regarding the vulnerability.
Wordpress Scanner is not a tool for code auditing, it performs "black box" scanning for the Wordpress powered web application.

The basic security check will review a WordPress installation for common security related mis-configurations. Testing with the basic check option uses regular web requests.
The system downloads a handful of pages from the target site, then performs analysis on the resulting html source.

#### Usage
```
Usage: php app.php [options]

Options:
        -h,   --help                    Show this help message.
        -u,   --url                     Target URL (e.g. "http://mywp.com/")
        -f,   --force                   Ignore if target is not wordpress.
        -v,   --version                 Check for available version
        --upgrade                       Upgrade to newer version
        --wpvulndb                      Use WPVulnDB API Instead of local database. (Powered by wpvulndb.com API)
        --no-log                        Disable Logging

Request:
        --ua, --user-agent              Set user-agent, default: random user agent
        -t,   --thread                  numbers of threads, default: 10
        --proxy                         Set proxy. eg: protocol://[username:password@]host:port

Scanning:
        -d,   --default                 Default scanning mode
                                        Equivalent to --dp,--dt,--b option
        -b,   --basic                   Show basic information about target
                                        Eg: robots.txt path, check multisite, registration enable, readme file
        --dp, --discover-plugin         Discover plugin(s) via html source
        --dt, --discover-theme          Discover theme(s) via html source

Plugin/Theme Enumeration:
        --ep, --enumerate-plugin        Enumerate plugins
        --et, --enumerate-theme         Enumerate themes
        --vp, --vuln-plugin             Enumerate vulnerable plugins only
        --vt, --vuln-theme              Enumerate vulnerable themes only

User Enumeration:
        --eu, --enumerate-user          Enumerate users
        -i,   --iterate                 numbers of iteration, default: 10
        -f,   --feed                    Enumerate through rss feeds, default: author pages
        -B,   --ubrute                  Set wordlist file(full path) to bruteforce username, default will use built-in wordlist
        -p,   --protect                 Check if the site is protected before bruteforcing, use with -B or --ubrute

Bruteforce:
        --bf, --bruteforce              Bruteforce Mode
        -x, --xmlrpc                    Bruteforce through XMLRPC interface.
        -p, --protect                   Check if the site is protected before bruteforcing.
        -F, --ufound                    Set username to enumerated users.
        -U, --user                      Set username or file containing user lists.
        -w, --wordlist                  Set wordlist file(full path), default will use built-in wordlist.
```

#### Requirements

- At least PHP 5.4
- PHP cURL Extension
- PHP JSON Extension


#### Installation

##### Windows

```Download http://windows.php.net/downloads/releases/php-5.4.41-Win32-VC9-x86.zip```

```Tick cURL Extension on installation step```

##### Ubuntu/Debian-based

```sudo apt-get install php5 php5-curl php5-json```

##### Mac OSX

```curl -s http://php-osx.liip.ch/install.sh | bash -s 5.4```

#### To Do List
- Rewrite code to be more modular
- Unit Tests
- Add Web UI
- Add custom wordpress directory(wp-content and wp-plugin)
- Vulnerability Database (currently using https://wpvulndb.com)


### Contribution

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Make your changes
4. Commit your changes (`git commit -am 'Added some feature'`)
5. Push to the branch (`git push origin my-new-feature`)
6. Create new Pull Request
7. Pat yourself on the back for being so awesome

### License

MIT License. Copyright (c) 2015 Ahmad Ramadhan Amizudin. See [License](https://github.com/RamadhanAmizudin/Wordpress-scanner/blob/master/LICENSE.txt).

### Contacts

ramadhan.amizudin at gmail dot com


