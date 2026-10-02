Installation
These steps describe the local manual installation used for the exercise. Use an existing Magento installation; this repository is a module, not a complete Magento application.
1. Place the module files inside app/code/Doit/CustomShipping/. The registration.php file must be directly inside that directory.
2. Run from the Magento root:
php bin/magento module:enable Doit_CustomShipping
php bin/magento setup:upgrade
php bin/magento cache:clean
3. Configure the carrier in Admin as described below.
The implementation was developed in the author's Magento Open Source 2.4.8-p4 learning environment. This is not a compatibility certification for other versions or installations. No Composer installation workflow is documented for this development checkpoint.
Admin configuration
Navigate to Stores → Configuration → Sales → Delivery Methods → Doit Delivery.
Setting	Purpose
Enabled	Enable or disable the carrier; disabled by default
Carrier Title	Label identifying the carrier
Method Title	Label for Standard Delivery
Price Per Unit	Price multiplied by the request's package quantity
Sort Order	Display ordering
Ship to Applicable Countries	All allowed countries or specific countries
Ship to Specific Countries	Allowed destinations when specific-country mode is selected
Express Surcharge	Additional charge per order for Express

php bin/magento cache:clean config
