=== EIMSKIP ===
Contributors: smartmediais
Tags: shipping, EIMSKIP, TVG express,Eimskip, icelandic shipping, shipping rates, woocommerce, sendingar
Requires at least: 4.3
Tested up to: 6.5.4
Stable tag: 2.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Tenging við EIMSKIP

== Description ==
EIMSKIP er hraðflutningaþjónusta til og frá Íslandi sem sérhæfir sig í þjónustu við vefverslanir.
Öll afgreiðsla og meðferð vörunnar er í forgangi og afhendingartími frá því að varan kemur til landsins er mun skemmri en í almennum flugsendingum.
EIMSKIP byggist á „Door-To-Door“ afhendingu: Varan er sótt, send, tollafgreidd og loks afhent viðtakanda.

EIMSKIP is a shipping service to and from Iceland that specializes in serving webshops.
All processing and handling of the goods are prioritized, and the delivery time from the arrival of the goods to the country is much shorter than in regular air freight shipments.
EIMSKIP is based on a "Door-To-Door" delivery: The goods are collected, shipped, customs cleared, and finally delivered to the recipient.

== Third-Party Service Integration ==
Please note that the EIMSKIP Plugin relies on a third-party service, namely EIMSKIP, to provide its shipping functionality. By using this plugin, you acknowledge and agree to the following:

* The EIMSKIP Plugin requires the use of the EIMSKIP shipping service to calculate shipping rates, handle pickups, process customs clearance, and deliver packages.
* Your data, including shipping addresses and order details, may be transmitted to EIMSKIP for the purpose of facilitating the shipping process.
* EIMSKIP's terms of use and privacy policies apply to the use of their services. You can access their terms of use and privacy policies through the following links:
EIMSKIP Terms of Use https://www.eimskip.com/terms-and-conditions/
EIMSKIP Privacy Policy https://www.eimskip.com/privacy-policy/
It is important to review and understand EIMSKIP's terms of use and privacy policies to ensure compliance with their guidelines and regulations. By using the EIMSKIP Plugin, you are responsible for ensuring that any legal requirements regarding data transmissions and privacy are met.

Our plugin leverages a connection to retrieve dynamic data from our optimized server, ensuring lightning-fast performance and reliable information delivery.
Rest assured, we've carefully hosted a static JSON file on the highly scalable and dependable AWS S3 infrastructure, located at s3.amazonaws.com. See Privacy policy at https://aws.amazon.com/privacy/

Our feature-rich WordPress plugin offers seamless integration with EIMSKIP, a trusted logistics partner, enabling you to effortlessly create shipments directly from your WordPress website.
By securely transmitting information to EIMSKIP's advanced platform, our plugin streamlines your shipping process and enhances efficiency.
Experience the convenience of instant shipment creation and enjoy the benefits of a robust logistics solution.

This plugin integrates with PrintNode to provide printing functionality. It allows you to connect to your PrintNode account and send print jobs to your configured printers. For more information about PrintNode and its services, please visit [PrintNode's website](https://www.printnode.com/). [PrintNode's Privacy Policy](https://www.printnode.com/en/privacy).


== Installation ==
1. Download the plugin and activate it.
2. Go to EIMSKIP and fill out the sender's information.
3. Go to EIMSKIP > API KEYS and insert data provided from EIMSKIP.
4. Go to WooCommerce > Settings > Shipping.
5. Enable the EIMSKIP shipping method.

== Changelog ==

= 2.2.2 =
- Confirmed compatibility with the latest version.

= 2.2.1 =
- Update for latest API connection to Eimskip

= 2.1.3 =
- Fixed plugin does not have a valid header.

= 2.1.2 =
- Updated to comply with coding standards.

= 2.1.1 =
- Updated to comply with coding standards.

= 2.1.0 =
- Updated to comply with coding standards.

= 2.0.6 =
- Confirmed compatibility with the latest version.

= 2.0.5 =
- Fixed translation issue.

= 2.0.4 =
- Fixed translation issue.

= 2.0.3 =
- Fixed translation issue.

= 2.0.2 =
- Fixed various bugs.

= 2.0.1 =
- Renamed the plugin to Eimskip.

= 1.2.20 =
- Fixed bugs for older PHP versions.

= 1.2.19 =
- Weight calculation with more than one item order and weight unit config

= 1.2.16 =
- Fix when only delivery point is in checkout and select is hidden

= 1.2.15 =
- Bugfixes

= 1.2.14 =
- Added trigger update_checkout when billing or shipping address or postcode is changed.

= 1.2.13 =
- Fixed if shipping classes returned WP_Error

= 1.2.12 =
- Version fix

= 1.2.11 =
- Fixed - shipping could not create automatically if weight empty

= 1.2.10 =
- Changed so the shipping titles in change order is set from settings

= 1.2.9 =
- Changed so only enabled shippings are shown inside Order

= 1.2.8 =
- Added possibility to skip dimension price on shipping rate

= 1.2.7 =
- Hide select for EIMSKIP box until shipping checked
- Added which EIMSKIP box in order overview

= 1.2.6 =
- Fix - if height / width / length is empty

= 1.2.5 =
- Added validation for pickup boxes in checkout

= 1.2.4 =
- Added possibility to clear the transient for EIMSKIP shipping options

= 1.2.3 =
- Added settings so the EIMSKIP shipping will be displayed on top of shipping methods in cart/checkout

= 1.2.2 =
- Added postcode extra price for DAT

= 1.2.1 =
- Fix if shipping has no max weight

= 1.2.0 =
- Added dimension weight calculation

= 1.1.9 =
- Translation fix

= 1.1.8 =
- Added package dimension check.
- Added shipping status in view order in my account

= 1.1.7 =
- Added possibility to customize shipping description

= 1.1.6 =
- Added possibility to select printer and send printnode request from order overview.

= 1.1.5 =
- Fixed how total cart price is calculated for free shipping

= 1.1.4 =
- Fixed validator for recipient phonenumber

= 1.1.3 =
- Changed how shipping class price is calculated when free shipping is skipped

= 1.1.2 =
- Added description under checked EIMSKIP shipping in checkout
- Added opening time to postboxes

= 1.1.1 =
- translate fix

= 1.1.0 =
- Added shipping class rules to EIMSKIP options
- Bug fixes

= 1.0.11 =
 - Use webservice instead of static array for shipping methods, postboxes and prices.

= 1.0.10 =
 - Fixed so kennitala field is not required to enable the EIMSKIP shipping

= 1.0.9 =
 - Added delivery points
 - Changes so Method title has placeholder value if no title is set

= 1.0.8 =
Temporary removed delivery points because of covid. Changed title of delivery points.

= 1.0.7 =
Added checkbox in Product data -> shipping so EIMSKIP shipping can be blocked for product

= 1.0.6 =
Fallback shipping price if using special prices and the field is empty

= 1.0.5 =
Added checkbox in Product data -> shipping so free shipping can be skipped for product

= 1.0.4 =
Added connection to PrintNode for automatic printing

= 1.0.3 =
Changed how shipment status is displayed in order metabox

= 1.0.2 =
Fix when shipping is created twice

= 1.0.1 =
Fixed how shipment is displayed in order metabox

= 1.0.0 =
Initial Release