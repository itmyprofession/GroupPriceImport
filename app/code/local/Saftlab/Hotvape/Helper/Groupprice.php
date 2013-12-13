<?PHP

/**
 * Groupprice Helper
 * 
 * @category    Saftlab
 * @package     Saftlab_Hotvape
 * @author      Santosh Moktan <santosh@amplecube.com>
 */
class Saftlab_Hotvape_Helper_Groupprice extends Mage_Core_Helper_Abstract
{

    protected $_resource;
    protected $_read;
    protected $_write;
    protected $_successMessage;
    protected $_failureMessage;

    /**
     * Import Group Price
     * 
     * @param type $rows
     */
    public function import($rows)
    {
        $this->_init();

        foreach ($rows as $row) {
            try {
                // whether product exist or not
                if ($this->_checkIfProductExists(trim($row[0]))) {
                    // trim data
                    $row = array_map('trim', $row);

                    if ($this->_checkForDuplicateGroupPrice($row[0], $row[2], $row[1])) {
                        if ($this->_updateGroupPrices($row)) {
                            $this->_successMessage .= "<p>Product with id $row[0] updated with group price $row[3]</p>";
                        } else {
                            throw new Exception("Product with id $row[0] cannot be updated.");
                        }
                    } else {
                        if ($this->_saveGroupPrices($row)) {
                            $this->_successMessage .= "<p>Product with id $row[0] successfully saved with group price $row[3]</p>";
                        } else {
                            throw new Exception("Product with id $row[0] cannot be saved.");
                        }
                    }
                } else {
                    throw new Exception("Product with id $row[0] doest exist..");
                }
            } catch (Exception $e) {
                $this->_failureMessage .= "<p>" . $e->getMessage() . "</p>";
            }
        }
    }

    /**
     * Initialization
     */
    protected function _init()
    {
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_read = $this->_getConnection('core_read');
        $this->_write = $this->_getConnection('core/write');
    }

    /**
     * Get connection
     * 
     * @param type $type
     * @return type
     */
    protected function _getConnection($type = 'core_read')
    {
        return $this->_resource->getConnection($type);
    }

    /**
     * Get table name
     * 
     * @param type $tableName
     * @return type
     */
    protected function _getTableName($tableName)
    {
        return $this->_resource->getTableName($tableName);
    }

    /**
     * Check whether product exist with given productId
     * 
     * @param type $entityId ProductId
     * @return boolean
     */
    protected function _checkIfProductExists($entityId)
    {
        $sql = "SELECT COUNT(*) AS count_no FROM " . $this->_getTableName('catalog_product_entity') . " WHERE entity_id = ?";
        $count = $this->_read->fetchOne($sql, array($entityId));
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check for duplicate group price
     * 
     * @param type $entityId            Product Id
     * @param type $customerGroupId     Customer Group Id
     * @param type $websiteId           Website Id
     */
    protected function _checkForDuplicateGroupPrice($entityId, $customerGroupId, $websiteId)
    {

        $sql = "SELECT COUNT(*) AS count_no FROM " . $this->_getTableName('catalog_product_entity_group_price') .
                " WHERE entity_id = ? && customer_group_id = ? && website_id = ?";
        $count = $this->_read->fetchOne($sql, array($entityId, $customerGroupId, $websiteId));
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Save Group Price
     * 
     * @param type $data
     */
    protected function _saveGroupPrices($data)
    {
        $sql = "INSERT INTO " . $this->_getTableName('catalog_product_entity_group_price') .
                " (`value_id`, `entity_id`, `all_groups`, `customer_group_id`, `value`, `website_id`) " .
                " values(?, ?, ?, ?, ?, ?)";

        return $this->_write->query($sql, array(null, $data[0], 0, $data[2], $data[3], $data[1]));
    }

    /**
     * Update group price
     * 
     * @param type $data
     */
    protected function _updateGroupPrices($data)
    {
        $sql = "UPDATE " . $this->_getTableName('catalog_product_entity_group_price') . " cpegp
                SET  cpegp.value = ?, cpegp.all_groups=?                
            WHERE  cpegp.entity_id = ?
            AND cpegp.customer_group_id = ?
            AND cpegp.website_id = ?";

        return $this->_write->query($sql, array($data[3], 0, $data[0], $data[2], $data[1]));
    }

    /**
     * Get sucess Message
     * 
     * @return type
     */
    public function getSuccessMessage()
    {
        return $this->_successMessage;
    }

    /**
     * Get failure message
     * 
     * @return type
     */
    public function getFailureMessage()
    {
        return $this->_failureMessage;
    }

}
