<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * The contents of this file draw heavily from XP Framework
 * https://github.com/xp-framework/xp-framework
 *
 * Copyright (c) 2001-2014, XP-Framework Team
 * All rights reserved.
 * https://github.com/xp-framework/xp-framework/blob/master/core/src/main/php/LICENCE
 */
namespace stubbles\values;

use InvalidArgumentException;
use LogicException;
use RuntimeException;
use SensitiveParameter;
use Throwable;
/**
 * Secret provides a reasonable secure storage for security-sensitive
 * lists of characters, such as passwords.
 *
 * It prevents accidentially revealing them in output, by var_dump()ing,
 * echo()ing, or casting the object to array. All these cases will not
 * show the password, nor the crypt of it.
 *
 * However, it is not safe to consider this implementation secure in a crypto-
 * graphically sense, because it does not care for a very strong encryption,
 * and it does share the encryption key with all instances of it in a single
 * PHP instance.
 *
 * When using this class, you must make sure not to extract the secured string
 * and pass it to a place where an exception might occur, as it might be exposed
 * as method argument.
 *
 * @since 4.0.0
 */
class Secret
{
    /**
     * backing: openssl
     */
    const BACKING_OPENSSL   = 'openssl';
    /**
     * backing: base64
     */
    const BACKING_PLAINTEXT = 'base64';
    /** @var array<string,true|string> */
    private static array $payloads = [];
    /** @var array<string,int> */
    private static array $lengths = [];
    /**
     * callable to encrypt data with before storing it
     *
     * @var  \Closure
     */
    private static $encrypt;
    /**
     * callable to decrypt data with before returning it from store
     *
     * @var  \Closure
     */
    private static $decrypt;
    /**
     * id of instance
     *
     * @var  string
     */
    private $id;

    /**
     * static initializer
     */
    static function __static(): void
    {
        if (extension_loaded(self::BACKING_OPENSSL)) {
            self::useOpenSslBacking();
        } else {
            self::usePlaintextBacking();
        }
    }

    /**
     * select en-/decryption mechanism
     *
     * @throws  InvalidArgumentException  when given backing is unknown
     * @throws  LogicException  when trying to change the backing while there are still secure strings in the store
     */
    public static function switchBacking(string $type): void
    {
        if (count(self::$payloads) > 0) {
            throw new LogicException('Can not switch backing while secured strings are stored');
        }

        switch ($type) {
            case self::BACKING_OPENSSL:
                self::useOpenSslBacking();
                break;

            case self::BACKING_PLAINTEXT:
                self::usePlaintextBacking();
                break;

            case '__none':
                self::$encrypt = fn() => throw new \Exception('No backing set');
                self::$decrypt = fn() => null;
                break;

            case '__none_error':
                self::$encrypt = fn() => throw new \Error('No backing set');
                self::$decrypt = fn() => null;
                break;

            default:
                throw new InvalidArgumentException('Unknown backing ' . $type);
        }
    }

    /**
     * switches backing to openssl
     *
     * @throws RuntimeException when openssl extension not available
     */
    private static function useOpenSslBacking(): void
    {
        if (!extension_loaded(self::BACKING_OPENSSL)) {
            throw new RuntimeException('Can not use openssl backing, extension openssl not available');
        }

        $key = md5(uniqid());
        $cypherIvLength = openssl_cipher_iv_length('AES-128-CBC');
        if (false === $cypherIvLength) {
          throw new RuntimeException('Can not calculate cypher iv length using method "des"');
        }

        $iv  = openssl_random_pseudo_bytes($cypherIvLength);
        self::$encrypt = fn($value) => openssl_encrypt($value, 'AES-128-CBC', $key,  0, $iv);
        self::$decrypt = fn($value) => openssl_decrypt($value, 'AES-128-CBC', $key,  0, $iv);
    }

    /**
     * switches backing to base64 encoding
     *
     * Of course this still allows to reveal the secured string, but at least
     * it allows to use Secret transparantly.
     */
    private static function usePlaintextBacking(): void
    {
        self::$encrypt = fn($value) => base64_encode($value);
        self::$decrypt = fn($value) => base64_decode($value);
    }

    /**
     * constructor
     */
    private final function __construct()
    {
        $this->id = uniqid('', true);
    }

    /**
     * creates an instance for given characters
     *
     * @throws  InvalidArgumentException
     */
    public static function create(#[SensitiveParameter] string|self|null $string): self
    {
        if ($string instanceof self) {
            return $string;
        }

        if (empty($string)) {
            throw new InvalidArgumentException(
                'Given string was null or empty, if you explicitly want to'
                . ' create a Secret with value null use'
                . ' Secret::forNull()'
            );
        }

        $self = new static();
        try {
            $encrypt = self::$encrypt;
            self::$payloads[$self->id] = $encrypt($string);
            self::$lengths[$self->id] = mb_strlen($string);
        } catch (Throwable $t) {
            $t = null;
            // This intentionally catches *ALL* exceptions, in order not to fail
            // and produce a stacktrace containing arguments on the stack that
            // were supposed to be protected.
            unset(self::$payloads[$self->id]);
            unset(self::$lengths[$self->id]);
        }

        $string = str_repeat('*', strlen($string));
        $string = null;
        return $self;
    }

    /**
     * explicitly create an instance where the actual string is null
     */
    public static function forNull(): self
    {
        $self = new static();
        self::$payloads[$self->id] = true;
        self::$lengths[$self->id] = 0;
        return $self;
    }

    /**
     * Destructor; removes references from crypted storage for this instance.
     */
    public function __destruct()
    {
        unset(self::$payloads[$this->id]);
        unset(self::$lengths[$this->id]);
    }

    /**
     * checks whether actual value is null
     */
    public function isNull(): bool
    {
        return true === self::$payloads[$this->id];
    }

    /**
     * checks if instance contains a string, i.e. encryption did not fail
     */
    public function isContained(): bool
    {
        return isset(self::$payloads[$this->id]);
    }

    /**
     * retrieve secured characters
     *
     * This should be called at the latest possible moment to avoid unneccessary
     * revealing of the value to be intended stored secure.
     *
     * @throws LogicException in case the secure string can not be found
     */
    public function unveil(): ?string
    {
        if (!$this->isContained()) {
           throw new LogicException('An error occurred during string encryption.');
        }

        if ($this->isNull()) {
            return null;
        }

        $decrypt = self::$decrypt;
        return $decrypt(self::$payloads[$this->id]);
    }

    /**
     * returns a substring of the secured string as a new Secret instance
     *
     * If no $length is provided the substring will be from start position to
     * end of the current secret.
     */
    public function substring(int $start, int $length = null): self
    {
        if ($this->isNull()) {
            return $this;
        }

        $unveiled = $this->unveil();
        if ($unveiled === null) {
          return self::forNull();
        }

        return self::create(substr($unveiled, $start, $length));
    }

    /**
     * returns length of string
     */
    public function length(): int
    {
        return self::$lengths[$this->id];
    }

    /**
     * prevent serialization
     *
     * @throws LogicException
     */
    public function __sleep()
    {
        throw new LogicException('Cannot serialize instances of ' . get_class($this));
    }

    /**
     * override regular __toString() output
     */
    public function __toString(): string
    {
        return get_class($this) . " {\n}\n";
    }
}
Secret::__static();
