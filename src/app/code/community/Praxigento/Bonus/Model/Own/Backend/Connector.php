<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Backend_Connector
    extends Nmmlm_Core_Datasource_Backend_Connector_Base
{
    /**
     * Add funds to the Customer's account.
     *
     * @param  Nmmlm_Core_Wrapper_Customer $wrapper
     * @param  $amount positive value to increase balance
     * @param  $message
     * @param null $currCode
     * @return mixed
     */
    public function creditFundsCredit(Nmmlm_Core_Wrapper_Customer $wrapper, $amount, $message, $currCode = null)
    {
        // TODO: Implement creditFundsCredit() method.
    }

    /**
     * Deduct funds from the Customer's account.
     *
     * @param  Nmmlm_Core_Wrapper_Customer $wrapper
     * @param  $amount positive value to decrease balance
     * @param  $message
     * @param null $currCode
     * @return mixed
     */
    public function creditFundsDebit(Nmmlm_Core_Wrapper_Customer $wrapper, $amount, $message, $currCode = null)
    {
        // TODO: Implement creditFundsDebit() method.
    }

    /**
     * Returns credit balance for the customer.
     *
     * @param Nmmlm_Core_Wrapper_Customer $wrapper
     * @param null $currCode
     * @return number
     */
    public function creditGetBalance(Nmmlm_Core_Wrapper_Customer $wrapper, $currCode = null)
    {
        return 0;
    }

    /**
     * Add new customer to backend. Returns updated customer's wrapper (with backend data) in case of
     * new customer was registered or 'null' otherwise. Flag $isOrderTriggered=true in case of customer replication
     * is called on new order registration.
     *
     * @param Nmmlm_Core_Model_Dict_Customer_Profile $profile
     * @param Nmmlm_Core_Model_Dict_Customer_Address $address
     * @param                                        $password
     * @param                                        $isOrderTriggered
     * @return Nmmlm_Core_Model_Dict_Customer_Profile
     */
    public function customerAddNew(
        Nmmlm_Core_Model_Dict_Customer_Profile $profile,
        Nmmlm_Core_Model_Dict_Customer_Address $address,
        $password = null, $isOrderTriggered = false
    )
    {
        // TODO: Implement customerAddNew() method.
    }

    /**
     * Authenticates customer using Backend API and returns result (true|false).
     *
     * @param $mlmId
     * @param $password
     * @return bool
     */
    public function customerAuthenticate($mlmId, $password)
    {
        // TODO: Implement customerAuthenticate() method.
    }

    /**
     *
     * Returns customer wrapper by 'id' or null if customer is not found. 'id' is backend id in case of parameter
     * 'isBendId' equals to 'true' and 'id' is MLM ID otherwise.
     *
     * @param      $id
     * @param bool $isBendId
     * @return Nmmlm_Core_Model_Dict_Customer_Profile
     */
    public function customerGetProfile($id, $isBendId = true)
    {
        // TODO: Implement customerGetProfile() method.
    }

    /**
     * Return 'true' if $email can be used on backend to registry new customers.
     * @param $email
     * @return boolean
     */
    public function customerIsEmailAllowed($email)
    {
        // TODO: Implement customerIsEmailAllowed() method.
    }

    /**
     * Retrieves (new) password for the customer in case of customer has forgot it.
     *
     * @param $mlmId
     * @param $newPassword
     * @return string password (current or new)
     */
    public function customerPasswordReset($mlmId, $newPassword = null)
    {
        // TODO: Implement customerPasswordReset() method.
    }

    /**
     * Remove customer data from backends.
     *
     * @param Nmmlm_Core_Wrapper_Customer $wrapper
     * @return mixed
     */
    public function customerRemove(Nmmlm_Core_Wrapper_Customer $wrapper)
    {
        // TODO: Implement customerRemove() method.
    }

    /**
     * Update customer data on Magento model save.
     *
     * @param Nmmlm_Core_Wrapper_Customer $wrapper
     * @return mixed
     */
    public function customerUpdate(Nmmlm_Core_Wrapper_Customer $wrapper)
    {
        // TODO: Implement customerUpdate() method.
    }

    /**
     * Returns order's invoices data from backend.
     *
     * @param Nmmlm_Core_Model_Dict_Sales_Order $order
     * @return Nmmlm_Core_Model_Dict_Sales_Invoice
     */
    public function orderGetInvoiceData(Nmmlm_Core_Model_Dict_Sales_Order $order)
    {
        // TODO: Implement orderGetInvoiceData() method.
    }

    /**
     * Returns order's shipment data from backend.
     *
     * @param Nmmlm_Core_Model_Dict_Sales_Order $order
     * @return Nmmlm_Core_Model_Dict_Sales_Shipment
     */
    public function orderGetShipmentData(Nmmlm_Core_Model_Dict_Sales_Order $order)
    {
        // TODO: Implement orderGetShipmentData() method.
    }

    /**
     * Add project specific conditions in order replication selection.
     *
     * @param Varien_Data_Collection_Db $collection
     * @return Mage_Sales_Model_Entity_Order_Collection
     */
    public function orderPropagateCollectionToSync(Varien_Data_Collection_Db $collection)
    {
        // TODO: Implement orderPropagateCollectionToSync() method.
    }

    /**
     * Updates state of the already registered order in the Backend.
     *
     * @param Nmmlm_Core_Model_Dict_Sales_Order $order
     * @return bool
     */
    public function orderUpdateState(Nmmlm_Core_Model_Dict_Sales_Order $order)
    {
        // TODO: Implement orderUpdateState() method.
    }

    /**
     * Finds and returns collection of all products or 'null' if not found. Backend connector implementation is
     * responsible for the collection composition according to the backend rules.
     *
     * @return Nmmlm_Core_Model_Dict_Catalog_Product[] with key 'mageSKU'
     */
    public function productGetAll()
    {
        // TODO: Implement productGetAll() method.
    }

    /**
     * Finds and returns collection of products by SKU or 'null' if not found. There can be multiple entries because
     * one product can belong to the different lots with different expiration dates. Backend connector implementation
     * is responsible for the collection composition according to the backend rules.
     *
     * @param $mageSku
     * @return Nmmlm_Core_Model_Dict_Catalog_Product[] with key 'mageSKU'
     */
    public function productGetBySku($mageSku)
    {
        // TODO: Implement productGetBySku() method.
    }

    /**
     * Converts core Product ID to Magento SKU.
     *
     * @param Nmmlm_Core_Model_Dict_Catalog_ProductId $id
     * @return string
     */
    public function promoConvertIdToMageSku(Nmmlm_Core_Model_Dict_Catalog_ProductId $id)
    {
        // TODO: Implement promoConvertIdToMageSku() method.
    }

    /**
     * Converts Magento SKU to core Product ID.
     *
     * @deprecated TODO: remove this functionality when Nmmlm_Lot will be completed
     *
     * @param $mageSku string
     * @return Nmmlm_Core_Model_Dict_Catalog_ProductId
     */
    public function promoConvertMageSkuToId($mageSku)
    {
        // TODO: Implement promoConvertMageSkuToId() method.
    }

    /**
     * Replicates Magento Credit Memo to other backends.
     *
     * @param Nmmlm_Core_Wrapper_Sales_Order_Creditmemo $wrappedMemo
     * @return mixed
     */
    public function refundReplicate(Nmmlm_Core_Wrapper_Sales_Order_Creditmemo $wrappedMemo)
    {
        // TODO: Implement refundReplicate() method.
    }

    /**
     * Returns 'true' in case of customer exists in backend.
     *
     * @param Nmmlm_Core_Model_Dict_Customer_Profile $profile
     * @return bool
     */
    protected function baseIsCustomerExist(Nmmlm_Core_Model_Dict_Customer_Profile $profile)
    {
        // TODO: Implement baseIsCustomerExist() method.
    }

    /**
     * Adds new order to backend(s).
     *
     * @param Nmmlm_Core_Model_Dict_Sales_Order $order
     * @return Nmmlm_Core_Datasource_Backend_OrderSyncResult
     */
    protected function baseOrderAdd(Nmmlm_Core_Model_Dict_Sales_Order $order)
    {
        // TODO: Implement baseOrderAdd() method.
    }

    /**
     * Adds new customer when new order is added but customer is not exist.
     *
     * @param Nmmlm_Core_Model_Dict_Customer_Profile $profile
     * @param Nmmlm_Core_Model_Dict_Customer_Address $address
     * @return Nmmlm_Core_Model_Dict_Customer_Profile
     */
    protected function baseCustomerAddOnNewOrder(
        Nmmlm_Core_Model_Dict_Customer_Profile $profile,
        Nmmlm_Core_Model_Dict_Customer_Address $address
    )
    {
        // TODO: Implement baseCustomerAddOnNewOrder() method.
    }
}