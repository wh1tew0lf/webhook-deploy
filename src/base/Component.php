<?php

namespace wh1te_w0lf\webhook_deploy\base;


abstract class Component {
    protected $_id = '';

    private static $__components = [];

    protected function __construct(array $config) {
        foreach ($config as $setting => $value) {
            $value = is_array($value) ? static::fabric($value) : $value;
            if (method_exists($this, 'set' . ucfirst($setting))) {
                $this->{'set' . ucfirst($setting)}($value);
            } elseif (property_exists($this, "_{$setting}")) {
                $this->{"_{$setting}"} = $value;
            }
        }

        if (empty($this->_id)) {
            $this->_id = uniqid('comp_');
        }
    }

    public static function fabric(array $config) {
        if (!empty($config['id']) && !empty(self::$__components[$config['id']])) {
            return self::$__components[$config['id']];
        }

        if (!isset($config['class']) || !class_exists($config['class'])) {
            $object = new static($config);
        } else {
            $object = new $config['class']($config);
        }

        self::$__components[$object->_id] = $object;

        return $object;
    }
}