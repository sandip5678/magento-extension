<?php

/*
 * This file is part of composer/semver.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Ess_M2ePro_Model_Requirements_Semver_Constraint_ConstraintInterface as ConstraintInterface;

/**
 * Defines the absence of a constraint.
 */
class Ess_M2ePro_Model_Requirements_Semver_Constraint_EmptyConstraint implements ConstraintInterface
{
    /** @var string */
    protected $_prettyString;

    /**
     * @param ConstraintInterface $provider
     *
     * @return bool
     */
    public function matches(ConstraintInterface $provider)
    {
        return true;
    }

    /**
     * @param $prettyString
     */
    public function setPrettyString($prettyString)
    {
        $this->_prettyString = $prettyString;
    }

    /**
     * @return string
     */
    public function getPrettyString()
    {
        if ($this->_prettyString) {
            return $this->_prettyString;
        }

        return $this->__toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '[]';
    }
}