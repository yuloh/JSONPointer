<?php
declare(strict_types=1);

namespace gamringer\JSONPointer;

use gamringer\JSONPointer\Access\Accesses;
use gamringer\JSONPointer\Access\ArrayAccessor;
use gamringer\JSONPointer\Access\ObjectAccessor;
use gamringer\JSONPointer\Exception;

class Pointer
{
    private $target;

    private $accessorCollection;

    public function __construct(&...$targets)
    {
        $target = new VoidValue();
        if (!empty($targets)) {
            $target = &$targets[0];
        }
        $this->setTarget($target);

        $this->accessorCollection = new AccessorCollection();
    }

    public function getAccessorCollection(): AccessorCollection
    {
        return $this->accessorCollection;
    }

    public function setTarget(&$target)
    {
        $this->target = &$target;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function has(string $path)
    {
        try {
            $this->reference($path)->hasValue();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function get(string $path)
    {
        return $this->reference($path)->getValue();
    }

    public function set(string $path, &$value)
    {
        return $this->reference($path)->setValue($value);
    }

    public function insert(string $path, &$value)
    {
        return $this->reference($path)->insertValue($value);
    }

    public function remove(string $path)
    {
        return $this->reference($path)->unsetValue();
    }

    private function reference(string $path): ReferencedValue
    {
        $path = $this->getCleanPath($path);
        if (empty($path)) {
            return new ReferencedValue($this->target);
        }

        $this->assertTarget();

        return $this->walk($path);
    }

    private function unescape(string $token): string
    {
        $token = (string) $token;

        if (preg_match('/~[^01]/', $token)) {
            throw new Exception('Invalid pointer syntax');
        }

        $token = str_replace('~1', '/', $token);
        $token = str_replace('~0', '~', $token);

        return $token;
    }

    private function getCleanPath(string $path): string
    {
        $path = (string) $path;

        $path = $this->getRepresentedPath($path);

        if (!empty($path) && $path[0] !== '/') {
            throw new Exception('Invalid pointer syntax');
        }

        return $path;
    }

    private function getRepresentedPath(string $path): string
    {
        if (substr($path, 0, 1) === '#') {
            return urldecode(substr($path, 1));
        }

        return stripslashes($path);
    }

    private function walk(string $path): ReferencedValue
    {
        $target = &$this->target;
        $tokens = explode('/', substr($path, 1));

        $accessor = null;

        while (($token = array_shift($tokens)) !== null) {
            $accessor = $this->accessorCollection->getAccessorFor($target);
            $token = $this->unescape($token);

            if (empty($tokens)) {
                break;
            }

            $target = &$this->fetchTokenTargetFrom($target, $token, $accessor);
        }

        return new ReferencedValue($target, $token, $accessor);
    }

    private function &fetchTokenTargetFrom(&$target, string $token, Accesses $accessor)
    {
        $result = &$accessor->getValue($target, $token);

        return $result;
    }

    private function assertTarget()
    {
        if ($this->target instanceof VoidValue) {
            throw new Exception('No target defined');
        }
    }
}
