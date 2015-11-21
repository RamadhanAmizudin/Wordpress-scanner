<?php

class Config {

    protected static $config = array();

    public static function get($name) {
        if( isset( self::$config[$name] ) ) {
            return self::$config[$name];
        } else {
            return false;
        }
    }

    public static function set($name, $value) {
        self::$config[$name] = $value;
    }

    public static function all() {
        return self::$config;
    }

    public static function handle($argv) {

        if( array_key_exists('url', $argv) ) {
            $argv['url'] = (stripos($argv['url'], 'http') === false) ? 'http://' . $argv['url'] : $argv['url'];
            Config::set('url', $argv['url']);
        }

        if( array_key_exists('u', $argv) ) {
            $argv['u'] = (stripos($argv['u'], 'http') === false) ? 'http://' . $argv['u'] : $argv['u'];
            Config::set('url', $argv['u']);
        }

        if( array_key_exists('proxy', $argv) ) {
            Config::set('proxy', $argv['proxy']);
        }

        if( array_key_exists('proxy-auth', $argv) ) {
            Config::set('proxy_auth', $argv['proxy-auth']);
        }

        if( array_key_exists('ua', $argv) ) {
            Config::set('ua', $argv['ua']);
        }

        if( array_key_exists('user-agent', $argv) ) {
            Config::set('ua', $argv['user-agent']);
        }

        if( array_key_exists('h', $argv) OR array_key_exists('help', $argv) ) {
            Config::set('help', true);
        }

        if( array_key_exists('dp', $argv) OR array_key_exists('discover-plugin', $argv) ) {
            Config::set('dp', true);
        }

        if( array_key_exists('dt', $argv) OR array_key_exists('discover-theme', $argv) ) {
            Config::set('dt', true);
        }

        if( array_key_exists('ep', $argv) OR array_key_exists('enumerate-plugin', $argv)) {
            Config::set('ep', true);
        }

        if( array_key_exists('vp', $argv) OR array_key_exists('vuln-plugin', $argv)) {
            Config::set('vuln-plugin', true);
        }

        if( array_key_exists('et', $argv) OR array_key_exists('enumerate-theme', $argv)) {
            Config::set('et', true);
        }

        if( array_key_exists('vt', $argv) OR array_key_exists('vuln-theme', $argv)) {
            Config::set('vuln-theme', true);
        }

        if( array_key_exists('eu', $argv) OR array_key_exists('enumerate-user', $argv)) {
            Config::set('eu', true);
        }

        if( array_key_exists('d', $argv) OR array_key_exists('default', $argv) ) {
            Config::set('default', true);
        }

        if( array_key_exists('b', $argv) OR array_key_exists('basic', $argv) ) {
            Config::set('basic', true);
        }

        if( array_key_exists('v', $argv) OR array_key_exists('version', $argv) ) {
            Config::set('version', true);
        }

        if( array_key_exists('upgrade', $argv) ) {
            Config::set('upgrade', true);
        }

        if( array_key_exists('wpvulndb', $argv) ) {
            Config::set('wpvulndb', true);
        }

        if( array_key_exists('f', $argv) OR array_key_exists('force', $argv) ) {
            Config::set('force', true);
        }

        if( array_key_exists('bruteforce', $argv) OR array_key_exists('bf', $argv) ) {
            Config::set('bf', true);
        }

        if( array_key_exists('xmlrpc', $argv) OR array_key_exists('x', $argv) ) {
            Config::set('xmlrpc', true);
        }

        if( array_key_exists('protect', $argv) OR array_key_exists('p', $argv) ) {
            Config::set('protected', true);
        }

        if( array_key_exists('feed', $argv) OR array_key_exists('f', $argv) ) {
            Config::set('feed', true);
        }

        if( array_key_exists('user', $argv) ) {
            Config::set('bfuser', $argv['user']);
        }

        if( array_key_exists('U', $argv) ) {
            Config::set('bfuser', $argv['U']);
        }

        if( array_key_exists('iterate', $argv) ) {
            Config::set('iterate', $argv['iterate']);
        }

        if( array_key_exists('i', $argv) ) {
            Config::set('iterate', $argv['i']);
        }

        if( array_key_exists('thread', $argv) ) {
            Config::set('thread', $argv['thread']);
        }

        if( array_key_exists('t', $argv) ) {
            Config::set('thread', $argv['t']);
        }

        if( array_key_exists('ufound', $argv) ) {
            Config::set('ufound', true);
        }

        if( array_key_exists('F', $argv) ) {
            Config::set('ufound', true);
        }

        if( array_key_exists('ubrute', $argv) ) {
            if($argv['ubrute'] === true) {
                Config::set('uwordlist', ROOT_PATH . '/base/data/wordlists.txt');
            } else {
                Config::set('uwordlist', $argv['userbrute']);
            }
        }

        if( array_key_exists('B', $argv) ) {
            if($argv['B'] === true) {
                Config::set('uwordlist', ROOT_PATH . '/base/data/wordlists.txt');
            } else {
                Config::set('uwordlist', $argv['B']);
            }
        }

        if( array_key_exists('wordlist', $argv) ) {
            Config::set('bfwordlist', $argv['wordlist']);
        } else {
            if( Config::get('bf') ) {
                Config::set('bfwordlist', ROOT_PATH . '/base/data/wordlists.txt');
            }
        }

        if( array_key_exists('w', $argv) ) {
            Config::set('bfwordlist', $argv['w']);
        } else {
            if( Config::get('bf') ) {
                Config::set('bfwordlist', ROOT_PATH . '/base/data/wordlists.txt');
            }
        }

        if( array_key_exists('no-log', $argv) ) {
            Config::set('nl', true);
        }

    }
}
