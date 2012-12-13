<?php
/**
 * User: florin
 * Date: 12/5/12
 * Time: 2:29 PM
 * @property mixed $name
 * @property mixed $description
 * @property mixed $max_uses
 * @property mixed $valid_from
 * @property mixed $valid_to
 * @property mixed $discount_type
 * @property mixed $discount_value
 * @property mixed $active
 */
class Coupon extends BaseItem
{
    const
        DISCOUNT_TYPE_FIXED_AMOUNT = 1,
        DISCOUNT_TYPE_PERCENTAGE = 2,

        TYPE_PARENT = 1,
        TYPE_CHILD = 2,
        TYPE_SINGLE = 3;

    public
        $srl,
        $module_srl,
        $code,
        $parent_srl,
        $uses,
        $member_srl,
        $ip,
        $type;

    private
        $name,
        $description,
        $max_uses,
        $valid_from,
        $valid_to,
        $discount_type,
        $discount_value,
        $active;

    /** @var CouponRepository */
    public $repo;

    public function __construct($data = NULL)
    {
        $this->setMeta('privates', array(
            'name',
            'description',
            'max_uses',
            'valid_from',
            'valid_to',
            'discount_type',
            'discount_value',
            'active'
        ));
        return parent::__construct($data);
    }

    public function generateCode($length=10, $type=RandomGenerator::TYPE_ALPHANUM, $pattern='X', $separateEvery=0, $separator='-')
    {
        //save random generation data in memory
        $this->setMeta('random', array(
            'length' => $length,
            'type' => $type,
            'pattern' => $pattern,
            'separateEvery' => $separateEvery,
            'separator' => $separator
        ));
        return $this->code = RandomGenerator::generateOne($length, $type, $pattern, $separateEvery, $separator);
    }

    public function generateBulk($number, $length=10, $type=RandomGenerator::TYPE_ALPHANUM, $pattern='X', $separateEvery=0, $separator='-')
    {
        if (!$this->isPersisted()) throw new ShopException('Current coupon is not persisted, can\'t be used as parent');
        $bulk = array();
        $i = 0;
        while ($i < $number) {
            $c = new Coupon();
            //$c->copy($this);
            $c->generateCode($length, $type, $pattern, $separateEvery, $separator);
            $c->module_srl = $this->module_srl;
            $c->parent_srl = $this->srl;
            $c->type = self::TYPE_CHILD;
            //save will throw ShopException if code is not unique
            try {
                $c->save();
            }
            catch (ShopException $e) {
                continue;
            }
            $i++;
            $bulk[] = $c;
        }
        return $bulk;
    }

    // we need to check for code unicity and stuff
    public function insert($query='insert%E')
    {
        if ($this->code) {
            $existentCoupons = $this->repo->getByCode($this->code, $this->module_srl);
            if ($existentCoupons && !empty($existentCoupons)) {
                if ($r = $this->getMeta('random')) {
                    $this->generateCode($r['length'], $r['type'], $r['pattern'], $r['separateEvery'], $r['separator']);
                }
                else {
                    throw new ShopException('Code already exists');
                }
            }
        }
        else {
            if ($this->parent_srl) {
                throw new ShopException('Child coupon must have a code at insert');
            }
        }
        return parent::insert($query);
    }

    public function getChildren($module_srl)
    {
        if (!$this->isPersisted()) throw new ShopException('A not persisted coupon group cannot have children');
        if (!$this->isGroup()) throw new ShopException('A non parent coupon cannot have children');
        $params = array('module_srl' => $module_srl, 'parent_srl' => $this->srl);
        $coupons = $this->repo->get(null, 'getCoupons', null, $params);
        return $coupons;
    }

    /**
     * @return Coupon|null
     * @throws ShopException
     */
    public function getParent()
    {
        if ($this->isChild()) return $this->repo->get($this->parent_srl);
    }

    public function isGroup()
    {
        return $this->type == self::TYPE_PARENT;
    }

    public function isSingle()
    {
        return $this->type == self::TYPE_SINGLE;
    }

    public function isChild()
    {
        return $this->type == self::TYPE_CHILD;
    }

    public function useOnce($withSave = false)
    {
        $this->uses += 1;
        if ($this->uses > $this->max_uses) return false;
        if ($withSave) $this->save();
        return true;
    }

    public function __get($name)
    {
        if (in_array($name, $this->getMeta('privates'))) {
            if (!isset($this->$name) || ($this->$name == 'null' && ($name == 'valid_from' || $name == 'valid_to'))) {
                if ($parent = $this->getParent()) {
                    return $parent->$name;
                }
            }
            return $this->$name;
        }
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->getMeta('privates'))) $this->$name = $value;
    }

}