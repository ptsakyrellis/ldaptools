<?php
/**
 * This file is part of the LdapTools package.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LdapTools\Utilities;

use LdapTools\Query\LdapQueryBuilder;

/**
 * Intended to be used with attribute converters that utilize options and current attributes to do some of their work.
 *
 * @author Chad Sikorra <Chad.Sikorra@gmail.com>
 */
trait ConverterUtilitiesTrait
{
    /**
     * {@inheritdoc}
     */
    abstract public function getAttribute();

    /**
     * {@inheritdoc}
     */
    abstract public function getDn();

    /**
     * {@inheritdoc}
     */
    abstract public function getLdapConnection();

    /**
     * If the current attribute does not exist in the array, then throw an error.
     *
     * @param $options
     */
    protected function validateCurrentAttribute(array $options)
    {
        if (!array_key_exists(strtolower($this->getAttribute()), array_change_key_case($options))) {
            throw new \RuntimeException(
                sprintf('You must first define "%s" in the options for this converter.', $this->getAttribute())
            );
        }
    }

    /**
     * Get the value of an array key in a case-insensitive way.
     *
     * @param array $options
     * @param string $key
     */
    protected function getArrayValue(array $options, $key)
    {
        return array_change_key_case($options)[strtolower($key)];
    }

    /**
     * This can be called to retrieve the current value of an attribute from LDAP.
     *
     * @param string $attribute The attribute name to query for.
     * @param string $dn The name LDAP expects for the distinguished name attribute.
     * @return array|string
     */
    protected function getCurrentLdapAttributeValue($attribute, $dn = 'distinguishedName')
    {
        if (!$this->getDn() || !$this->getLdapConnection()) {
            throw new \RuntimeException(sprintf('Unable to query for the current "%s" attribute.', $attribute));
        }

        $query = new LdapQueryBuilder($this->getLdapConnection());
        $result = $query->select($attribute)
            ->where([$dn => $this->getDn()])
            ->getLdapQuery()
            ->execute();

        if ($result->count() == 0) {
            throw new \RuntimeException(sprintf('Unable to find LDAP object: %s', $this->getDn()));
        }
        /** @var \LdapTools\Object\LdapObject $object */
        $object = $result->toArray()[0];

        return $object->has($attribute) ? $object->get($attribute) : null;
    }
}
