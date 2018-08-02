<?php

namespace Core\Libraries;

class Session
{
    public static function all()
    {
        if (isset($_SESSION)) {
            return $_SESSION;
        }

        return false;
    }

    public static function has($key)
    {
        if (isset($_SESSION)) {
            if (isset($_SESSION[$key]) && !empty($_SESSION[$key])) {
                return true;
            }
        }

        return false;
    }

    public static function exists($key)
    {
        if (isset($_SESSION)) {
            if (isset($_SESSION[$key])) {
                return true;
            }
        }

        return false;
    }

    public static function get($key)
    {
        if (isset($_SESSION)) {
            if (isset($_SESSION[$key])) {
                return $_SESSION[$key];
            }
        }

        return false;
    }

    public static function put($key, $value)
    {
        if (isset($_SESSION)) {
            $_SESSION[$key] = $value;

            return true;
        }

        return false;
    }

    public static function push($key, $value)
    {
        if (isset($_SESSION)) {
            if (isset($_SESSION[$key])) {
                $_SESSION[$key] = $value;

                return true;
            }
        }

        return false;
    }

    public static function pull($key)
    {
        if (isset($_SESSION)) {
            if (isset($_SESSION[$key])) {
                $temp = $_SESSION[$key];
                unset($_SESSION[$key]);

                return $temp;
            }
        }

        return false;
    }

    public static function flash($key, $message)
    {
        if (isset($_SESSION)) {
            if (isset($_SESSION[$key])) {
                return self::pull($key);
            } else {
                return self::put($key, $message);
            }
        }

        return false;
    }

    public static function regenerate()
    {
        if (isset($_SESSION)) {
            if (session_id() !== '') {
                session_regenerate_id(true);

                return true;
            }
        }

        return false;
    }

    public static function forget($key)
    {
        if (isset($_SESSION)) {
            if (isset($_SESSION[$key])) {
                unset($_SESSION[$key]);

                return true;
            }
        }

        return false;
    }

    public static function flush()
    {
        if (isset($_SESSION)) {
            session_unset();
            session_destroy();

            return true;
        }

        return false;
    }
}