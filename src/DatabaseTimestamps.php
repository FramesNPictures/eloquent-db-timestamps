<?php

namespace Fnp\Eloquent;

use Fnp\Eloquent\Exception\GrammarInvalid;
use Fnp\Eloquent\Exception\GrammarNotFoundException;
use Illuminate\Database\Query\Expression;

/**
 * Class DatabaseTimestamps
 *
 * @package Fnp\Eloquent
 *
 * @method \Illuminate\Database\Connection getConnection() Current Connection
 * @property mixed $attributes Model Attributes
 */
trait DatabaseTimestamps
{
    protected $updatedField     = 'updated_at';
    protected $createdField     = 'created_at';
    private   $timestampCommand = NULL;

    public function getUpdatedAtAttribute($value)
    {
        return $value;
    }

    public function getCreatedAtAttribute($value)
    {
        return $value;
    }

    public function setUpdatedAtAttribute($value)
    {
        $timestampCommand                        = $this->getTimestampCommand();
        $this->attributes[ $this->updatedField ] = new Expression($timestampCommand);
    }

    public function setCreatedAtAttribute($value)
    {
        $timestampCommand                        = $this->getTimestampCommand();
        $this->attributes[ $this->createdField ] = new Expression($timestampCommand);
    }

    private function getTimestampCommand()
    {
        if ($this->timestampCommand) {
            return $this->timestampCommand;
        }

        /** @var Grammar $grammarClass */
        $driver       = $this->getConnection()->getConfig('driver');
        $grammarClass = __NAMESPACE__ . '\\Grammar\\' . ucfirst(strtolower($driver));

        if (!class_exists($grammarClass, TRUE)) {
            throw new GrammarNotFoundException(sprintf('%s grammar not exists.', ucfirst($driver)));
        }

        if (!in_array(Grammar::class, class_implements($grammarClass, TRUE))) {
            throw new GrammarInvalid(sprintf('%s grammar is invalid.', $grammarClass));
        }

        return $grammarClass::timestamp();
    }
}