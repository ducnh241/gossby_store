-- MariaDB dump 10.17  Distrib 10.4.8-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: 9prints_authyshop
-- ------------------------------------------------------
-- Server version	10.4.8-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `osc_alias`
--

DROP TABLE IF EXISTS `osc_alias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_alias` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(45) NOT NULL,
  `module_key` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `lang_key` varchar(2) NOT NULL,
  `destination` varchar(255) NOT NULL,
  PRIMARY KEY (`item_id`),
  UNIQUE KEY `alias_key_UNIQUE` (`slug`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_alias`
--

LOCK TABLES `osc_alias` WRITE;
/*!40000 ALTER TABLE `osc_alias` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_alias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_backend_bookmark`
--

DROP TABLE IF EXISTS `osc_backend_bookmark`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_backend_bookmark` (
  `bookmark_id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_key` varchar(2) NOT NULL DEFAULT '',
  `member_id` int(11) NOT NULL,
  `module_key` varchar(100) NOT NULL,
  `bookmark_key` varchar(255) NOT NULL,
  `bookmark_extra_key` varchar(255) NOT NULL,
  `icon` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `url_params` text NOT NULL,
  `permission_key` varchar(255) NOT NULL,
  PRIMARY KEY (`bookmark_id`),
  UNIQUE KEY `lang_key_3` (`lang_key`,`module_key`,`bookmark_key`,`bookmark_extra_key`),
  KEY `member_id` (`member_id`),
  KEY `module_key` (`module_key`),
  KEY `lang_key` (`lang_key`),
  KEY `lang_key_2` (`lang_key`,`member_id`),
  KEY `bookmark_key` (`bookmark_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_backend_bookmark`
--

LOCK TABLES `osc_backend_bookmark` WRITE;
/*!40000 ALTER TABLE `osc_backend_bookmark` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_backend_bookmark` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_backend_index`
--

DROP TABLE IF EXISTS `osc_backend_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_backend_index` (
  `index_id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_key` varchar(2) NOT NULL,
  `ukey` varchar(255) NOT NULL,
  `module_key` varchar(100) NOT NULL,
  `item_group` varchar(50) NOT NULL DEFAULT 'default',
  `item_id` int(11) NOT NULL,
  `filter_data` varchar(255) NOT NULL,
  `item_data` text NOT NULL,
  `keywords` text NOT NULL,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`index_id`),
  UNIQUE KEY `ukey` (`ukey`),
  KEY `index` (`lang_key`,`module_key`,`item_group`,`item_id`),
  KEY `filter_data` (`filter_data`),
  FULLTEXT KEY `keywords` (`keywords`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_backend_index`
--

LOCK TABLES `osc_backend_index` WRITE;
/*!40000 ALTER TABLE `osc_backend_index` DISABLE KEYS */;
INSERT INTO `osc_backend_index` VALUES (1,'','navigation/navigation-1','navigation','navigation',1,'','[]','acme bar',1569486771,1569486771),(2,'','navigation/navigation-2','navigation','navigation',2,'','[]','chief menu',1569486803,1569486961),(3,'','navigation/navigation-3','navigation','navigation',3,'','[]','footer menu',1569486844,1569487489),(4,'','catalog/collection-1','catalog','collection',1,';collect_method:auto;','[]','best selling',1569486912,1569486912),(5,'','catalog/collection-2','catalog','collection',2,';collect_method:auto;','[]','new arrivals',1569486940,1569486940),(6,'','page/page-1','page','page',1,'','[]','faqs howdo one change or cancel my orderinthe consequence y\'all wish to cancel your club please contact united states inside 12 hours uponconfirmation of club at store_email_address please exist advised that anycancellations subsequently 12 hours upon blessing of club volition no longer exist allowedand volition not exist entertain.pleasenote that any orders that accept already been packed or shipped cannot becanceled.what payment methods make y\'all acceptweaccept all major credit cards visa mastercard amex and paypal payments wedo not accept personal checks coin orders directly depository financial institution transfers debit cardpayments or cash on delivery.can one change my transportation address subsequently placing an orderif y\'all change your transportation address pleasecontact united states atstore_email_addresswithin 12 hours subsequently placing customerservice staffs volition match your club and confirm asap.pleasebe advised that your transportation address cannot exist revised subsequently the club hasbeen processed or shipped kindly update your transportation address to yourresidential address instead of your vocational address equally nosotros make not know howlong the destinations customs department volition accept the package on hold.when volition one become my tracking numberonce the club has been processed h5n1 trackingnumber normally takes 1-3 occupation days to exist generated please accept bill toallow one to three occupation days for your tracking data to exist updated if youhave not received your tracking issue inside three occupation days or if thetracking condition is not available inside one to two occupation days fromthe fourth dimension y\'all accept received your tracking issue kindly mail united states an electronic mail atstore_email_addresshow make one track my orderyoucan merely click on track your club on acme ofstore_namepage enter your trackingnumber to match your club status.how long does delivery taketheprocessing fourth dimension for orders is 1-3 occupation days once the shipment is alreadyloaded on the airplane estimated delivery is 5-8 days for the united united states and7-10 days for other countries.pleasetake bill that there are approximately unforeseen circumstances such equally customs delaysthat nosotros are unable to dominance on our end equally well equally approximately delays if there is anupcoming vacation season.how make one render an itemifyou are not happy alongside your buy and wish to render an detail please contactus inside thirty days from receiving your club please supply your club numberas well equally the argue for your render our customer service team volition reviewthe render request and volition mail further instructions if the render isapproved.fora list of concluding sale items please meet our returns policy all returns must bein master condition alongside packaging intact.will one exist charged alongside customs and taxestheprices displayed on our site are tax-free in united states dollars which means y\'all may beliable to pay for duties and taxes once y\'all receive your order.importtaxes duties and related customs fees may exist charged once your club arrivesat its concluding finish which is determined by your local customs office.paymentof these charges and taxes are your responsibility and volition not exist covered byus nosotros are not responsible for delays caused by the customs department in yourcountry for further details of the charges please contact your local customsoffice.i necessitate my club fast make y\'all guys supply expedited shippingifyou desire to receive your club early on than the casual delivery fourth dimension pleasecontact united states equally soon equally y\'all home nosotros volition change the transportation method for you.but y\'all volition pay extra for the transportation service.when volition one receive my refundallrefunds volition exist credited to your master class of payment if y\'all paid bycredit or debit menu refunds volition exist sent to the card-issuing depository financial institution inside 5-10business days of receipt of the returned detail or cancellation request pleasecontact the card-issuing depository financial institution alongside questions approximately when the credit volition beposted to your account.if y\'all havent received acredit for your render however heres what to make contact the bankcredit cardcompany information technology may accept approximately fourth dimension earlier the refund to y\'all',1569486977,1569488022),(7,'','page/page-2','page','page',2,'','[]','payment methods payment methodsmethods of payment available are based onthe nation where the club is from and the full sum on the bill.creditdebit cardyour creditdebit menu volition automaticallybe charged by our fiscal service provider your club volition get down processingafter all necessary verifications accept been completed.paypalthe transaction volition exist processed bypaypal an external fiscal service provider during the checkout processyou volition exist redirected to paypal and pay alongside your bill there your datawill not exist transferred to store_name subsequently paypal has completed verificationyour club volition then exist processed.currencywhen shopping on our website paymentswill exist processed in usd if your credit menu firm or depository financial institution uses h5n1 differentcurrency the concluding transaction price may differ due to currency exchangerates please contact your payment provider for further information.paymentcashon delivery buy club isnt possible at store_name',1569487229,1569488095),(8,'','page/page-3','page','page',3,'','[]','privacy policy privacy policysection1 what make nosotros make alongside your informationwhenyou buy something from store_name equally function of the buying and sellingprocess nosotros collect the personal data y\'all pass united states such equally your nameaddress and electronic mail address besides y\'all volition exist required to providestore_nameinformation regarding your credit menu or another payment instrument yourepresent and warrant to united states that such data is correct and that y\'all areauthorized to function the payment instrument.whenyou browse our shop nosotros besides automatically receive your computers internetprotocol ip address to supply united states alongside data that helps united states teach aboutyour browser and operating system.emailmarketing if applicable alongside your permission nosotros may mail y\'all emails aboutour shop new products and other updates.section2 consenthowdo y\'all become my consentwhenyou supply united states alongside personal data to complete h5n1 transaction verify yourcredit menu home an club arrange for h5n1 delivery or render h5n1 buy weimply that y\'all consent to our collecting information technology and using information technology for that specificreason only.ifwe necessitate for your personal data for h5n1 secondary argue like marketing wewill either necessitate y\'all immediately for your expressed consent or supply y\'all alongside anopportunity to order no.ourpolicy explains what data nosotros collect on the website how nosotros function or sharethis data and how nosotros keep such data by using this websiteyou signify your acceptance of this policy if y\'all make not match to the price ofthis policy in whole or function y\'all should not function this website please notethat this policy applies entirely concerning the data collected on thewebsite and not any data received or obtained through other methods orsources.howdo one remove my consentifafter y\'all opt-in y\'all change your heed y\'all may remove your consent for united states tocontact y\'all for the continued collection function or disclosure of yourinformation at any fourth dimension by contacting united states atstore_email_addresssection3 disclosurewemay unwrap your personal data if nosotros are required by police to make then or ifyou violate our price of service.section4 third-party servicesingeneral the third-party providers used by united states volition entirely collect function anddisclose your data to the extent necessary to let them to perform theservices they supply to us.howevercertain third-party service providers such equally payment gateways and otherpayment transaction processors accept their privacy policies concerning theinformation nosotros are required to supply to them for your purchase-relatedtransactions.forthese providers nosotros recommend that y\'all read their privacy policies then y\'all canunderstand the manner in which these providers volition manage your personalinformation.inparticular remember that certain providers may exist located in or havefacilities that are located in h5n1 unlike jurisdiction than either y\'all or us.so if y\'all elect to continue alongside h5n1 transaction that involves the services of athird-party service provider then your data may become discipline to thelaws of the jurisdictions in which that service provider or its facilitiesare located.asan case if y\'all are located in canada and your transaction is processed bya payment gateway located in the united united states then your personal informationused in completing that transaction may exist discipline to disclosure nether unitedstates legislation including the patriot act.onceyou exit our stores website or are redirected to h5n1 third-party site orapplication y\'all are no longer governed by this privacy policy or our websitesterms of service.linkswhenyou click on links on our shop they may directly y\'all away from our site nosotros arenot responsible for the privacy practices of other sites and encourage y\'all toread their privacy statements.section5 securitytoprotect your personal data nosotros accept reasonable precautions and followindustry best practices to brand certain information technology is not inappropriately lost misusedaccessed disclosed altered or destroyed.ifyou supply united states alongside your credit menu data the data is encryptedusing secure socket layer engineering ssl and stored alongside aes-256 encryption.although no method of transmission over the internet or electronic storage is100 condom nosotros follow all pci-dss requirements and implement additionalgenerally accepted industry standards.at store_name nosotros never share our customers data alongside the third party in any manner weuse the data that y\'all supply for such purposes equally responding to yourrequests customizing future shopping for y\'all improving our stores andcommunicating alongside y\'all nosotros always attempt to personalize and continually improveyourstore_nameshopping experience.section6 cookiesweuse cookies engineering to shop data on your calculator using thefunctionality of your browser h5n1 lot of websites make this because cookies allowthe website publisher to make useful things like finding out whether the computerand probably its user has visited the site earlier y\'all displace normally modifyyour browser to prevent cookie function merely if y\'all make this the service and thewebsite may not function correctly the data stored in the cookie is usedto identify y\'all this enables united states to function an efficient service and to trackthe patterns of demeanor of visitors to the website.alsoin the class of serving advertisements to this website if any third-partyadvertisers or ad servers may home or recognize h5n1 unique cookie on yourbrowser the function of cookies by such third party advertisers or ad servers isnot discipline to this policy merely is discipline to their respective privacy policies.please bill that function of the website is neither intended for nor directed tochildren nether the historic period of 18.section7 changes to this privacy policywereserve the correct to change this privacy policy at any fourth dimension then please reviewit frequently changes and clarifications volition accept consequence immediately upontheir posting on the website depending on the nature of the change nosotros mayannounce the change h5n1 on the homepage of the website or b by electronic mail if wehave your electronic mail address however in any consequence by continuing to function thewebsite following any changes y\'all volition exist deemed to accept agreed to suchchanges if y\'all make not match alongside the price of this policy equally information technology may beamended from fourth dimension to fourth dimension in whole or function y\'all must displace your function of thewebsite.questionsand contact informationifyou would like to access correct better or delete any personal data wehave approximately y\'all register h5n1 charge or merely desire more data contact ourprivacy compliance officeholder atstore_email_addressstore_nameservices proprietary rightsservicecontent software and trademarks y\'all are entirely authorized to function the store_name service to engage in occupation transactions alongside store_name y\'all may not function anyautomated engineering to scrape mine or assemble any data from store_name service or otherwise access the pages of store_name service for any unauthorizedpurpose if store_name service blocks y\'all from accessing store_name service includingby blocking your ip address y\'all match not to implement any measures tocircumvent such blocking e.g by masking your ip address or using h5n1 proxy ipaddress the engineering and software underlying the store_name service ordistributed in connection in summation to that are the property of store_name ouraffiliates and our partners the software y\'all match not to imitate modifycreate h5n1 derivative function of reverse engineer reverse assemble or otherwiseattempt to detect any beginning code sell assign sublicense or otherwisetransfer any correct in the software.store_name service may incorporate images artwork fonts and other content or featuresservice content that are protected by intellectual property rights andlaws except equally expressly authorized by store_name y\'all match not to change copyframe rent lease loan sell distribute or make derivative plant based onthe store_name service or the service content in whole or in function any function of the store_name service or the service content other than equally specifically authorized herein isstrictly prohibited store_name reserves any rights not expressly granted herein.the store_name mention and logos are trademarks and service marks of store_name collectivelythe store_name trademarks other firm product and service names and logosused and displayed via the store_name service may exist trademarks or service marks oftheir respective owners who may or may not endorse or exist affiliated alongside orconnected to store_name nothing in these price of service or the store_name serviceshould exist construed equally granting any license or correct to function any of store_name trademarks displayed on the store_name service without our prior writtenpermission in each case all goodwill generated from the function of store_name trademarks volition inure to store_names exclusive benefit.thirdparty fabric nether no circumstances volition store_name exist liable in any manner for anycontent or material of any third parties including users including merely notlimited to for any errors or omissions in any content or for any loss ordamage of any kind incurred equally h5n1 consequence of the function of any such content ormaterials to the maximum extent permitted nether applicable police the thirdparty providers of such content and material are express and intended thirdparty beneficiaries of these price of services alongside respect to their contentand materials.store_name may save content and may besides unwrap content if required to make then by lawor in the adept religion belief that such preservation or disclosure is reasonablynecessary to h5n1 comply alongside legal procedure applicable laws or governmentrequests b enforce these price of service c answer to claims that anycontent violates the rights of third parties or d protect the rightsproperty or personal condom of store_name its users or the populace',1569487364,1569488331),(9,'','page/page-4','page','page',4,'','[]','returns refund policy render policy1 not happy alongside your orderif y\'all are not happy alongside your purchase30 days from date of that y\'all received the product in like-new condition withno visible clothing and tear y\'all buyer volition exist the one who is responsible forpaying for the transportation costs for returning detail if not covered by our warrantyagainst manufacturer defects and central is not due to our error.2 damaged items or low-qualityif the product is defective or does notwork properly please kindly let united states know for the fastest resolution pleasecontact united states viastore_email_addressincluding h5n1 photo demonstrating the poorquality or the damaged area of the detail the about optimal pictures are on aflat surface alongside the tag and mistake clearly displayed well mail youreplacements equally soon equally nosotros confirmed the situation no necessitate to render thedefective ones nosotros volition function this data to aid y\'all alongside your club andeliminate errors in the future.cancellation1 canceling unshipped-out ordersif y\'all are canceling your club which has notyet to exist shipped out please kindly contact united states via store_email_address for thefastest resolution please include your club issue thanks2 cancelling shipped-out orderif y\'all are canceling orders when yourparcel has already been shipped out or on its manner to h5n1 finish pleasecontact united states and then kindly refuse to accept the package since nosotros are not able tocall information technology back at that fourth dimension nosotros volition refund your payment subsequently deducting shippingcosts refund volition exist issued equally soon equally package begins to return.warrantythis warranty entirely covers manufacturingdefects and does not coverdamage caused by accidentimproper carenormal clothing and tearbreak down of colors and material due tosun exposureaftermarket modificationsplease bill no returnsexchanges forproducts alongside water exposure volition exist accepted.refund policyif y\'all feel that the product youvepurchased does not meet the requirements y\'all accept in heed then y\'all make accept theoption to request h5n1 refund.below are the weather nether whichrefund volition exist granted.you displace become h5n1 full refund ifif the product y\'all purchased iscompletely non-functional.if y\'all did not receive your productwithin thirty occupation days subsequently the date y\'all placed your order.please bill the refund volition become back toyour bill in 5-10 occupation days.shipping costsyou volition exist responsible for paying foryour own transportation costs for returning detail transportation costs are non-refundable.if y\'all receive h5n1 refund the price of render transportation volition exist deducted from yourrefund.if y\'all are transportation an detail over 100 youshould see using h5n1 trackable transportation service or purchasing shippinginsurance thank youdamagedlow-quality itemfor the fastest resolution please includea photo demonstrating the hapless quality or the damaged area of the item.ideally the pictures should exist on h5n1 apartment surface alongside the tag and errorclearly displayed.we volition function this data to aid youwith your club and to prevent repeated errors in the future.if youhave other concerns and inquiries kindly mail h5n1 mail to store_email_address',1569487374,1569488368),(10,'','page/page-5','page','page',5,'','[]','transportation policy countries nosotros canship nosotros transport worldwide.deliverytime when placing your club nosotros see these factors when calculating the estimateddelivery dateorderprocessing the sum of timeit takes for united states to cook your club for transportation subsequently your payment isauthorized and verified this typically takes 1-3 occupation days.note processing fourth dimension for customizedpersonalized may accept longer information technology normally takes five to ten occupation days.transit fourth dimension the sum of fourth dimension information technology takes your club to exit ourwarehouse and arrive at the local delivery carrier information technology may accept from 5-10business days.estimated transportation shippingcharges are estimated due to place and weight the minimum transportation fee willbe 6.99.pleasenote that these are estimated delivery times only.pleaseensure all delivery data is correct if there is incorrect or missinginformation nosotros may exist required to contact y\'all for the update on the deliveryinformation which displace campaign delays in delivering your club delays may alsooccur equally h5n1 consequence of customs clearance.please fill in youraddress in all details otherwise the package nosotros mail to y\'all volition exist returnedto united states or nosotros volition merely ignore your request to salvage everyone the trouble',1569487384,1569487388),(11,'','page/page-6','page','page',6,'','[]','price of service overviewstore_name operates this website throughout thesite the price nosotros united states and our mention to store_name.store_nameoffersthis website including all data tools and services available fromthis site to y\'all the user conditioned upon your acceptance of all termsconditions policies and notices stated here.byvisiting our site or purchasing something from united states y\'all engage in our serviceand match to exist jump by the following price and weather price of serviceterms including those additional price and weather and policiesreferenced herein or available by hyperlink these price of service use toall users of the site including without limitation users who are browsersvendors customers merchants and or contributors of content.pleaseread these price of service carefully earlier accessing or using our website byaccessing or using any function of the site y\'all match to exist jump by these termsof service if y\'all make not match to all the price and weather of this agreementthen y\'all may not access the website or function any services if these price ofservice are considered an offer acceptance is expressly express to these termsof service.anynew features or tools which are added to the stream shop shall besides exist subjectto the price of service y\'all displace review the about stream version of the termsof service at any fourth dimension on this page nosotros reserve the correct to update change orreplace any function of these price of service by posting updates or changes to ourwebsite information technology is your responsibility to match this page periodically for changes.your continued function of or access to the site following the posting of anychanges constitutes acceptance of those changes.ourstore is hosted on shopify inc they supply united states alongside an online e-commerceplatform that allows united states to sell our products and services to you.section1 online shop termsbyagreeing to these price of service y\'all represent that y\'all are at least the ageof majority in your country or state of residence or that y\'all are the historic period ofmajority in your country or state of residence and y\'all accept given united states yourconsent to let any of your child dependents to function this site.youmay not function our products for any illegal or unauthorized function nor may youin the function of the service violate any laws in your jurisdiction including butnot express to copyright laws.youmust not transmit any worms or viruses or any code of h5n1 destructive nature.abreach or violation of any of the price volition consequence in immediate result ofyour services.section2 general conditionswereserve the correct to refuse service to anyone for any argue at any time.youunderstand that your content not including credit menu data may betransferred unencrypted and necessitate h5n1 transmissions over diverse networksand b changes to arrange and arrange to technical requirements of connectingsystems or devices credit menu data is always encrypted during transferover networks.youagree not to reproduce duplicate imitate sell resell or exploit any part ofthe service function of the service or access to the service or any contact on thewebsite through which the service is provided without express wrote permissionby us.theheadings used in this agreement are included for convenience entirely and volition notlimit or otherwise affect these terms.section3 accuracy completeness and timeliness of informationweare not responsible if data made available on this site is not accuratecomplete or stream the fabric on this site is provided for generalinformation entirely and should not exist relied upon or used equally the sole footing formaking decisions without consulting chief more accurate more complete ormore timely sources of data any reliance on the fabric on this siteis at your own risk.thissite may incorporate specific historical data historical informationnecessarily is not stream and is provided for your reference entirely nosotros reservethe correct to change the contents of this site at any fourth dimension merely nosotros accept noobligation to update any data on our website y\'all match that information technology is yourresponsibility to monitor changes to our site.section4 modifications to the service and pricespricesfor our products are discipline to change without notice.wereserve the correct at any fourth dimension to change or discontinue the service or any partor content thereof without detect at any time.weshall not exist liable to y\'all or any third-party for any change pricechange pause or discontinuance of the service.section5 products or services if applicablecertainproducts or services may exist available entirely online through the website.these products or services may accept express quantities and are discipline to returnor central entirely according to our render policy.wehave made every attempt to display equally accurately equally possible the colors andimages of our products that look at the shop nosotros cannot guarantee that yourcomputer monitors display of any coloring material volition exist accurate.wereserve the correct merely are not obligated to restrict the sales of our products orservices to any person geographic region or jurisdiction nosotros may practice thisright on h5n1 case-by-case footing nosotros reserve the correct to restrict the quantities ofany products or services that nosotros offer all descriptions of products or productpricing are discipline to change at any fourth dimension without detect at the solediscretion of united states nosotros reserve the correct to discontinue any product at any time.any offer for any product or service made on this site is void whereprohibited.wedo not warrant that the quality of any products services data orother fabric purchased or obtained by y\'all volition meet your expectations orthat any errors in the service volition exist corrected.section6 accuracy of billing and bill informationwereserve the correct to refuse any club y\'all home alongside united states nosotros may in our solediscretion restrict or cancel quantities purchased per person per family oreach club these restrictions may include orders placed by or nether the samecustomer bill the same credit menu or orders that function the equal billing orshipping address if nosotros brand h5n1 change to or cancel an club nosotros may try tonotify y\'all by contacting the electronic mail or billing addressphone issue provided atthe fourth dimension the club was made nosotros reserve the correct to restrict or prohibit ordersthat in our sole judgment look to exist placed by dealers resellers ordistributors.youagree to supply stream complete and accurate buy and bill informationfor all purchases made at our shop y\'all match to promptly update your accountand other data including your electronic mail address and credit menu numbers andexpiration dates then that nosotros displace complete your transactions and contact y\'all asneeded.formore details please review our returns policy.section7 optional toolswemay supply y\'all alongside access to third-party tools over which nosotros neither monitornor accept any dominance nor input.youacknowledge and match that nosotros supply access to such tools equally is and asavailable without any warranties representations or weather of any kindand any endorsement nosotros shall accept no liability any arising from orrelating to your function of optional third-party tools.anyuse by y\'all of optional tools offered through the site is entirely at your ownrisk and discretion and y\'all should ensure that y\'all are familiar alongside andapprove of the price on which tools are provided by the relevant third-partyproviders.wemay besides in the future offer new services or features through the websiteincluding the free of new tools and resource such new features orservices shall besides exist discipline to these price of service.section8 third-party linkscertaincontent products and services available via our service may include materialfrom third-parties.third-partylinks on this site may directly y\'all to third-party websites that are notaffiliated alongside united states nosotros are not responsible for examining or evaluating thecontent or accuracy and nosotros make not warrant and volition not accept any liability orresponsibility for any third-party material or websites or for any othermaterials products or services of third-parties.weare not liable for any damage or damages related to the buy or function of goodsservices resource content or any other transactions made in connection withany third-party websites please review the third-partys policies andpractices carefully and brand certain y\'all sympathize them earlier y\'all engage in anytransaction complaints claims concerns or questions regarding third-partyproducts should exist directed to the third-party.section9 user comments feedback and other submissionsifat our request y\'all mail certain specific submissions for case contestentries or without h5n1 request from united states y\'all mail creative ideas suggestionsproposals plans or other material whether online by electronic mail by postal mailor otherwise collectively comments y\'all match that nosotros may at any timewithout restriction edit imitate publish distribute translate and otherwiseuse in any medium any comments that y\'all forward to united states nosotros are and shall beunder no obligation one to keep any comments in confidence two to paycompensation for any comments or three to answer to any comments.wemay merely accept no obligation to monitor edit or remove content that wedetermine in our sole discretion are unlawful offensive threateninglibelous defamatory pornographic obscene or otherwise objectionable orviolates any partys intellectual property or these price of service.youagree that your comments volition not violate any correct of any third-partyincluding copyright trademark privacy personality or other personal orproprietary correct y\'all further match that your comments volition not containlibelous or otherwise unlawful abusive or obscene fabric or incorporate anycomputer virus or other malware that could in any manner affect the operation ofthe service or any related website y\'all may not function h5n1 fake electronic mail addresspretend to exist person other than yourself or otherwise mislead united states orthird-parties equally to the beginning of any comments y\'all are entirely responsible forany comments y\'all brand and their accuracy nosotros accept no responsibility and assumeno liability for any comments posted by y\'all or any third-party.section10 personal informationourprivacy policy governs your submission of personal data through thestore to opinion our privacy policy.section11 errors inaccuracies and omissionsoccasionallythere may exist data on our site or in the service that containstypographical errors inaccuracies or omissions that may relate to productdescriptions pricing promotions offers product transportation charges transittimes and availability nosotros reserve the correct to correct any errorsinaccuracies or omissions and to change or update data or cancel ordersif any data in the service or on any related website is inaccurate atany fourth dimension without prior detect including subsequently y\'all accept submitted your order.weundertake no obligation to update better or clarify data in the serviceor on any related website including without limitation pricing informationexcept equally required by police no specified update or refresh date applied in theservice or on any relevant site should exist taken to bespeak that allinformation in the service or on any related website has been modified orupdated.section12 prohibited usesinaddition to other prohibitions equally fix forth in price of service y\'all areprohibited from using the site or its content h5n1 for any unlawful purposeb to solicit others to perform or participate in any unlawful acts c toviolate any international federal provincial or country regulations ruleslaws or local ordinances d to infringe upon or violate our intellectualproperty rights or the intellectual property rights of others east to harassabuse insult damage defame slander disparage intimidate or discriminatebased on sex sexual orientation religion ethnicity race historic period nationalorigin or disability f to submit fake or misleading data thousand toupload or transmit viruses or any other type of malicious code that volition or possibly used in any manner that volition affect the functionality or operation of theservice or of any related website other websites or the internet h tocollect or track the personal data of others one to spam phish pharmpretext spider crawl or scrape j for any obscene or immoral function ork to interfere alongside or circumvent the safety features of the service or anyrelated website other websites or the internet nosotros reserve the correct toterminate your function of the service or any related website for violating any ofthe prohibited uses.section13 disclaimer of warranties limitation of liabilitywedo not guarantee represent or warrant that your function of our service volition beuninterrupted timely secure or error-free.wedo not warrant that the results that may exist obtained from the function of theservice volition exist accurate or reliable.youagree that from fourth dimension to fourth dimension nosotros may remove the service for indefinite periodsof fourth dimension or cancel the service at any fourth dimension without detect to you.youexpressly match that your function of or inability to function the service is at yoursole gamble the service and all products and services delivered to y\'all throughthe service are except equally expressly stated by united states provided equally is and asavailable for your function without any representations warranties or conditionsof any kind either express or implied including all implied warranties orconditions of merchantability merchantable quality fitness for h5n1 particularpurpose durability championship and non-infringement.inno event shall store_site our directors officers employees affiliatesagents contractors interns suppliers service providers or licensors areliable for any injury loss claim or any directly indirect incidentalpunitive especial or consequential damages of any kind including withoutlimitation lost net income lost revenue lost savings loss of data replacementcosts or any like damages whether based in contract tort includingnegligence strict liability or otherwise arising from your function of any of theservice or any products procured using the service or for any other claimrelated in any manner to your function of the service or any product including butnot express to any errors or omissions in any content or any loss or damageof any kind incurred equally h5n1 consequence of the function of the service or any content orproduct posted transmitted or otherwise made available via the service evenif advised of their possibility because of approximately united states or jurisdictions make notallow the exclusion or the limitation of liability for consequential orincidental damages in such united states or jurisdictions our liability shall belimited to the maximum extent permitted by law.section14 indemnificationyouagree to indemnify defend and agree harmlessstore_siteand our parentsubsidiaries affiliates partners officers directors agents contractorslicensors service providers subcontractors suppliers interns and employeesharmless from any claim or necessitate including reasonable attorneys fees madeby any third-party due to or arising out of your breach of these price ofservice or the documents they incorporate by reference or your violation of anylaw or the rights of h5n1 third-party.section15 severabilityinthe consequence that any provision of these price of service is determined to beunlawful void or unenforceable such provision shall however beenforceable to the fullest extent permitted by applicable police and theunenforceable part shall exist deemed to exist severed from these price ofservice such decision shall not affect the validity and enforceability ofany other remaining provisions.section16 terminationtheobligations and liabilities of the parties incurred prior to the terminationdate shall survive the result of this agreement for all purposes.theseterms of service are effective unless and until terminated by either y\'all or us.you may displace these price of service at any fourth dimension by notifying united states that youno longer wish to function our services or when y\'all end using our site.ifin our sole judgment y\'all fail or nosotros suspect that y\'all accept failed to complywith any term or provision of these price of service nosotros besides may terminatethis agreement at any fourth dimension without detect and y\'all volition stay liable for allamounts due up to and including the date of result andor accordingly maydeny y\'all access to our services or any function thereof.section17 entire agreementthefailure of united states to practice or enforce any correct or provision of these price ofservice shall not establish h5n1 waiver of such correct or provision.theseterms of service and any policies or operating rules posted by united states on this siteor in respect to the service constitutes the entire agreement and understandingbetween y\'all and united states and govern your function of the service superseding any prior orcontemporaneous agreements communications and proposals whether oral orwritten between y\'all and united states including merely not express to any prior versionsof the price of service.anyambiguities in the interpretation of these price of service shall not beconstrued against the drafting party.section18 changes to price of serviceyoucan review the about stream version of the price of service at any fourth dimension at thispage.wereserve the correct at our sole discretion to update change or supplant anypart of these price of service by posting updates and changes to our website.it is your responsibility to match our website periodically for changes yourcontinued function of or access to our website or the service following the postingof any changes to these price of service constitutes acceptance of thosechanges.section19 contact informationquestions approximately the termsof service should exist sent to united states at store_email_address',1569487392,1569488443);
/*!40000 ALTER TABLE `osc_backend_index` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_backend_logs`
--

DROP TABLE IF EXISTS `osc_backend_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_backend_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `member_id` int(11) NOT NULL,
  `username` varchar(32) NOT NULL,
  `content` text NOT NULL,
  `log_data` text NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_backend_logs`
--

LOCK TABLES `osc_backend_logs` WRITE;
/*!40000 ALTER TABLE `osc_backend_logs` DISABLE KEYS */;
INSERT INTO `osc_backend_logs` VALUES (1,'27.79.223.200',1,'administrator','Tài khoản: Tạo tài khoản [#2] \"admin\"','[]',1569486385),(2,'27.79.223.200',1,'administrator','Add navigation [#1] \"Top bar\"','[]',1569486771),(3,'27.79.223.200',1,'administrator','Add navigation [#2] \"Main menu\"','[]',1569486803),(4,'27.79.223.200',1,'administrator','Add navigation [#3] \"Footer menu\"','[]',1569486844),(5,'27.79.223.200',1,'administrator','Catalog: Added collection [#1] \"Best Selling\"','[]',1569486912),(6,'27.79.223.200',1,'administrator','Catalog: Added collection [#2] \"New Arrivals\"','[]',1569486940),(7,'27.79.223.200',1,'administrator','Edit navigation #2','[]',1569486961),(8,'27.79.223.200',1,'administrator','Edit page #1','[]',1569486977),(9,'27.79.223.200',1,'administrator','Edit page #1','[]',1569486986),(10,'27.79.223.200',1,'administrator','Edit page #1','[]',1569486990),(11,'27.79.223.200',1,'administrator','Edit page #2','[]',1569487229),(12,'27.79.223.200',1,'administrator','Edit page #2','[]',1569487234),(13,'27.79.223.200',1,'administrator','Edit page #3','[]',1569487364),(14,'27.79.223.200',1,'administrator','Edit page #3','[]',1569487368),(15,'27.79.223.200',1,'administrator','Edit page #4','[]',1569487374),(16,'27.79.223.200',1,'administrator','Edit page #4','[]',1569487380),(17,'27.79.223.200',1,'administrator','Edit page #5','[]',1569487384),(18,'27.79.223.200',1,'administrator','Edit page #5','[]',1569487388),(19,'27.79.223.200',1,'administrator','Edit page #6','[]',1569487392),(20,'27.79.223.200',1,'administrator','Edit page #6','[]',1569487396),(21,'27.79.223.200',1,'administrator','Edit navigation #3','[]',1569487489),(22,'27.79.223.200',1,'administrator','Edit page #1','[]',1569487958),(23,'27.79.223.200',1,'administrator','Edit page #1','[]',1569488022),(24,'27.79.223.200',1,'administrator','Edit page #2','[]',1569488095),(25,'27.79.223.200',1,'administrator','Edit page #3','[]',1569488331),(26,'27.79.223.200',1,'administrator','Edit page #4','[]',1569488368),(27,'27.79.223.200',1,'administrator','Edit page #6','[]',1569488443);
/*!40000 ALTER TABLE `osc_backend_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_cart`
--

DROP TABLE IF EXISTS `osc_catalog_cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_cart` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(45) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `discount_codes` text DEFAULT NULL,
  `shipping_line` text NOT NULL,
  `taxes` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `client_info` text NOT NULL,
  `shipping_full_name` varchar(255) NOT NULL,
  `shipping_phone` varchar(30) NOT NULL,
  `shipping_company` varchar(255) NOT NULL,
  `shipping_address1` varchar(255) NOT NULL,
  `shipping_address2` varchar(255) NOT NULL,
  `shipping_city` varchar(255) NOT NULL,
  `shipping_province` varchar(255) DEFAULT NULL,
  `shipping_province_code` varchar(25) DEFAULT NULL,
  `shipping_country` varchar(255) NOT NULL,
  `shipping_country_code` varchar(2) NOT NULL,
  `shipping_zip` varchar(100) NOT NULL DEFAULT '',
  `billing_full_name` varchar(255) NOT NULL,
  `billing_phone` varchar(30) NOT NULL,
  `billing_company` varchar(255) NOT NULL,
  `billing_address1` varchar(255) NOT NULL,
  `billing_address2` varchar(255) NOT NULL,
  `billing_city` varchar(255) NOT NULL,
  `billing_province` varchar(255) DEFAULT NULL,
  `billing_province_code` varchar(25) DEFAULT NULL,
  `billing_country` varchar(255) NOT NULL,
  `billing_country_code` varchar(2) NOT NULL,
  `billing_zip` varchar(100) NOT NULL DEFAULT '',
  `abandoned_email_sents` int(11) NOT NULL DEFAULT 0,
  `added_timestamp` int(11) NOT NULL,
  `modified_timestamp` int(11) NOT NULL,
  PRIMARY KEY (`cart_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`),
  KEY `member_id` (`member_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_cart`
--

LOCK TABLES `osc_catalog_cart` WRITE;
/*!40000 ALTER TABLE `osc_catalog_cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_cart_item`
--

DROP TABLE IF EXISTS `osc_catalog_cart_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_cart_item` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(52) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `cost` int(11) NOT NULL DEFAULT 0,
  `compare_at_price` int(11) NOT NULL DEFAULT 0,
  `quantity` int(11) NOT NULL,
  `require_shipping` tinyint(1) NOT NULL DEFAULT 1,
  `require_packing` tinyint(1) NOT NULL DEFAULT 0,
  `weight` int(11) NOT NULL DEFAULT 0,
  `weight_unit` enum('kg','g','oz','lb') NOT NULL DEFAULT 'g',
  `keep_flat` varchar(45) NOT NULL DEFAULT '1',
  `dimension_width` int(11) NOT NULL DEFAULT 0,
  `dimension_height` varchar(45) NOT NULL DEFAULT '0',
  `dimension_length` varchar(45) NOT NULL DEFAULT '0',
  `custom_data` longtext DEFAULT NULL,
  `added_timestamp` int(11) NOT NULL,
  `modified_timestamp` int(11) NOT NULL,
  PRIMARY KEY (`item_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`),
  KEY `variant_id` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_cart_item`
--

LOCK TABLES `osc_catalog_cart_item` WRITE;
/*!40000 ALTER TABLE `osc_catalog_cart_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_cart_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_checkout_footprint`
--

DROP TABLE IF EXISTS `osc_catalog_checkout_footprint`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_checkout_footprint` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL,
  `log_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_info` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  KEY `cart_id` (`cart_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_checkout_footprint`
--

LOCK TABLES `osc_catalog_checkout_footprint` WRITE;
/*!40000 ALTER TABLE `osc_catalog_checkout_footprint` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_checkout_footprint` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_collection`
--

DROP TABLE IF EXISTS `osc_catalog_collection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_collection` (
  `collection_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `collect_method` enum('auto','manual') NOT NULL DEFAULT 'auto',
  `auto_conditions` text NOT NULL,
  `sort_option` varchar(255) NOT NULL,
  `meta_tags` text DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`collection_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_collection`
--

LOCK TABLES `osc_catalog_collection` WRITE;
/*!40000 ALTER TABLE `osc_catalog_collection` DISABLE KEYS */;
INSERT INTO `osc_catalog_collection` VALUES (1,'Best Selling','Best_Selling','','','auto','{\"matched_by\":\"all\",\"conditions\":[{\"field\":\"title\",\"operator\":\"not_equals\",\"value\":\"AaBbCcDd\"}]}','solds','[]',1569486912,1569486912),(2,'New Arrivals','New_Arrivals','','','auto','{\"matched_by\":\"all\",\"conditions\":[{\"field\":\"title\",\"operator\":\"not_equals\",\"value\":\"AaBbCcDd\"}]}','newest','[]',1569486940,1569486940);
/*!40000 ALTER TABLE `osc_catalog_collection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_customer`
--

DROP TABLE IF EXISTS `osc_catalog_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_customer` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL,
  `orders` int(11) NOT NULL DEFAULT 0,
  `spent` int(11) NOT NULL DEFAULT 0,
  `email` varchar(255) NOT NULL,
  `subscribe_newsletter` tinyint(1) NOT NULL DEFAULT 1,
  `phone` varchar(30) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `address1` varchar(255) NOT NULL,
  `address2` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `zip` varchar(100) NOT NULL DEFAULT '',
  `province` varchar(255) DEFAULT NULL,
  `province_code` varchar(25) DEFAULT NULL,
  `country` varchar(255) NOT NULL,
  `country_code` varchar(2) NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `member_id_UNIQUE` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_customer`
--

LOCK TABLES `osc_catalog_customer` WRITE;
/*!40000 ALTER TABLE `osc_catalog_customer` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_discount_code`
--

DROP TABLE IF EXISTS `osc_catalog_discount_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_discount_code` (
  `discount_code_id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_code` varchar(100) NOT NULL,
  `auto_generated` tinyint(1) NOT NULL DEFAULT 0,
  `discount_type` enum('percent','fixed_amount','free_shipping','bxgy') NOT NULL DEFAULT 'free_shipping',
  `discount_value` int(11) NOT NULL DEFAULT 0,
  `prerequisite_product_id` text NOT NULL,
  `prerequisite_variant_id` text NOT NULL,
  `prerequisite_collection_id` text NOT NULL,
  `prerequisite_customer_group` text NOT NULL,
  `prerequisite_customer_id` text NOT NULL,
  `prerequisite_country_code` text NOT NULL,
  `prerequisite_shipping_rate` int(11) NOT NULL DEFAULT 0,
  `prerequisite_subtotal` int(11) NOT NULL DEFAULT 0,
  `prerequisite_quantity` int(11) NOT NULL DEFAULT 0,
  `entitled_product_id` text NOT NULL,
  `entitled_variant_id` text NOT NULL,
  `entitled_collection_id` text NOT NULL,
  `bxgy_prerequisite_quantity` int(11) NOT NULL DEFAULT 0,
  `bxgy_entitled_quantity` int(11) NOT NULL DEFAULT 0,
  `bxgy_discount_rate` int(11) NOT NULL DEFAULT 100,
  `bxgy_allocation_limit` int(11) NOT NULL DEFAULT 0,
  `usage_limit` int(11) NOT NULL DEFAULT 0,
  `usage_counter` int(11) NOT NULL DEFAULT 0,
  `once_per_customer` tinyint(1) NOT NULL DEFAULT 0,
  `combine_flag` tinyint(1) NOT NULL DEFAULT 0,
  `auto_apply` tinyint(1) NOT NULL DEFAULT 0,
  `apply_across` tinyint(1) NOT NULL DEFAULT 1,
  `active_timestamp` int(10) NOT NULL DEFAULT 0,
  `deactive_timestamp` int(10) NOT NULL DEFAULT 0,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`discount_code_id`),
  UNIQUE KEY `discount_code_UNIQUE` (`discount_code`),
  KEY `auto_apply` (`auto_apply`,`usage_limit`,`usage_counter`,`active_timestamp`,`deactive_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_discount_code`
--

LOCK TABLES `osc_catalog_discount_code` WRITE;
/*!40000 ALTER TABLE `osc_catalog_discount_code` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_discount_code` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_discount_code_usage`
--

DROP TABLE IF EXISTS `osc_catalog_discount_code_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_discount_code_usage` (
  `usage_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `order_email` varchar(255) NOT NULL,
  `discount_code_id` int(11) NOT NULL,
  `discount_code` varchar(100) NOT NULL,
  `code_auto_generated` tinyint(1) NOT NULL DEFAULT 0,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`usage_id`),
  KEY `index` (`discount_code_id`,`order_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_discount_code_usage`
--

LOCK TABLES `osc_catalog_discount_code_usage` WRITE;
/*!40000 ALTER TABLE `osc_catalog_discount_code_usage` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_discount_code_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_item_customize`
--

DROP TABLE IF EXISTS `osc_catalog_item_customize`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_item_customize` (
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ukey` varchar(27) NOT NULL,
  `title` varchar(255) NOT NULL,
  `config` longtext NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`item_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_item_customize`
--

LOCK TABLES `osc_catalog_item_customize` WRITE;
/*!40000 ALTER TABLE `osc_catalog_item_customize` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_item_customize` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_item_customize_design`
--

DROP TABLE IF EXISTS `osc_catalog_item_customize_design`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_item_customize_design` (
  `record_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ukey` varchar(100) NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `product_title` varchar(255) NOT NULL,
  `product_image_url` varchar(255) NOT NULL,
  `customize_id` int(10) unsigned NOT NULL,
  `customize_title` varchar(255) NOT NULL,
  `customize_info` longtext NOT NULL,
  `customize_data` longtext NOT NULL,
  `design_image_url` text DEFAULT NULL,
  `printer_image_url` varchar(255) DEFAULT NULL,
  `state` tinyint(1) unsigned NOT NULL COMMENT '1: pending\\n2: processing\\n3: completed',
  `member_id` int(11) DEFAULT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  `modified_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`),
  KEY `member_id` (`member_id`),
  KEY `state` (`state`,`member_id`),
  KEY `ukey_index` (`ukey`,`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_item_customize_design`
--

LOCK TABLES `osc_catalog_item_customize_design` WRITE;
/*!40000 ALTER TABLE `osc_catalog_item_customize_design` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_item_customize_design` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_item_customize_order_map`
--

DROP TABLE IF EXISTS `osc_catalog_item_customize_order_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_item_customize_order_map` (
  `record_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `design_id` int(10) unsigned NOT NULL,
  `order_line_id` int(10) unsigned NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  `modified_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`),
  KEY `design_id` (`design_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_item_customize_order_map`
--

LOCK TABLES `osc_catalog_item_customize_order_map` WRITE;
/*!40000 ALTER TABLE `osc_catalog_item_customize_order_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_item_customize_order_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_order`
--

DROP TABLE IF EXISTS `osc_catalog_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(32) NOT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `cart_ukey` varchar(27) DEFAULT NULL,
  `order_status` enum('open','archived','cancelled') NOT NULL DEFAULT 'open',
  `payment_status` enum('pending','void','authorized','paid','partially_paid','partially_refunded','refunded') NOT NULL DEFAULT 'pending',
  `fulfillment_status` enum('fulfilled','unfulfilled','partially_fulfilled') NOT NULL DEFAULT 'unfulfilled',
  `process_status` enum('unprocess','process','partially_process','processed') NOT NULL DEFAULT 'unprocess',
  `member_id` int(11) NOT NULL DEFAULT 0,
  `customer_id` int(11) NOT NULL DEFAULT 0,
  `email` varchar(255) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `discount_codes` text DEFAULT NULL,
  `shipping_line` text NOT NULL,
  `taxes` text DEFAULT NULL,
  `payment_method` text NOT NULL,
  `payment_data` text DEFAULT NULL,
  `fraud_data` varchar(255) DEFAULT '[''score'':0,''detail'':'''']',
  `fraud_risk_level` varchar(45) DEFAULT 'unknown',
  `subtotal_price` int(11) NOT NULL,
  `shipping_price` int(11) NOT NULL,
  `tax_price` int(11) NOT NULL,
  `total_price` int(11) NOT NULL,
  `paid` int(11) NOT NULL DEFAULT 0,
  `refunded` int(11) NOT NULL DEFAULT 0,
  `shipping_price_refunded` int(11) NOT NULL DEFAULT 0,
  `client_info` text NOT NULL,
  `note` text DEFAULT NULL,
  `shipping_full_name` varchar(255) NOT NULL,
  `shipping_phone` varchar(30) NOT NULL,
  `shipping_company` varchar(255) NOT NULL,
  `shipping_address1` varchar(255) NOT NULL,
  `shipping_address2` varchar(255) NOT NULL,
  `shipping_city` varchar(255) NOT NULL,
  `shipping_province` varchar(255) DEFAULT NULL,
  `shipping_province_code` varchar(25) DEFAULT NULL,
  `shipping_country` varchar(255) NOT NULL,
  `shipping_country_code` varchar(2) NOT NULL,
  `shipping_zip` varchar(100) NOT NULL DEFAULT '',
  `billing_full_name` varchar(255) NOT NULL,
  `billing_phone` varchar(30) NOT NULL,
  `billing_company` varchar(255) NOT NULL,
  `billing_address1` varchar(255) NOT NULL,
  `billing_address2` varchar(255) NOT NULL,
  `billing_city` varchar(255) NOT NULL,
  `billing_province` varchar(255) DEFAULT NULL,
  `billing_province_code` varchar(25) DEFAULT NULL,
  `billing_country` varchar(255) NOT NULL,
  `billing_country_code` varchar(2) NOT NULL,
  `billing_zip` varchar(100) NOT NULL DEFAULT '',
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  `code` varchar(45) DEFAULT NULL,
  `master_lock_flag` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`),
  UNIQUE KEY `code_UNIQUE` (`code`),
  KEY `customer_id` (`customer_id`),
  KEY `member_id` (`member_id`),
  KEY `email` (`email`),
  KEY `order_status` (`order_status`),
  KEY `payment_status` (`payment_status`),
  KEY `fulfillment_status` (`fulfillment_status`),
  KEY `fraud_risk_level` (`fraud_risk_level`),
  KEY `cart_id` (`cart_id`),
  KEY `cart_ukey` (`cart_ukey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_order`
--

LOCK TABLES `osc_catalog_order` WRITE;
/*!40000 ALTER TABLE `osc_catalog_order` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_order_bulk_queue`
--

DROP TABLE IF EXISTS `osc_catalog_order_bulk_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_order_bulk_queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` int(11) NOT NULL,
  `secondary_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_flag` tinyint(1) NOT NULL DEFAULT 0,
  `error` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`queue_id`),
  UNIQUE KEY `order_id_UNIQUE` (`order_id`,`action`,`secondary_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_order_bulk_queue`
--

LOCK TABLES `osc_catalog_order_bulk_queue` WRITE;
/*!40000 ALTER TABLE `osc_catalog_order_bulk_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_order_bulk_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_order_fulfillment`
--

DROP TABLE IF EXISTS `osc_catalog_order_fulfillment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_order_fulfillment` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `tracking_number` varchar(255) NOT NULL,
  `shipping_carrier` varchar(255) NOT NULL,
  `tracking_url` varchar(255) NOT NULL,
  `line_items` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  KEY `order_id` (`order_id`),
  KEY `tracking_number` (`tracking_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_order_fulfillment`
--

LOCK TABLES `osc_catalog_order_fulfillment` WRITE;
/*!40000 ALTER TABLE `osc_catalog_order_fulfillment` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_order_fulfillment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_order_index`
--

DROP TABLE IF EXISTS `osc_catalog_order_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_order_index` (
  `record_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `keywords` text NOT NULL,
  `order_status` tinyint(3) unsigned NOT NULL,
  `payment_status` tinyint(3) unsigned NOT NULL,
  `fulfillment_status` tinyint(3) unsigned NOT NULL,
  `process_status` tinyint(4) unsigned NOT NULL DEFAULT 0,
  `fraud_risk_level` tinyint(3) unsigned NOT NULL,
  `order_added_timestamp` int(10) unsigned NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  `modified_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `order_id_UNIQUE` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_order_index`
--

LOCK TABLES `osc_catalog_order_index` WRITE;
/*!40000 ALTER TABLE `osc_catalog_order_index` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_order_index` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_order_item`
--

DROP TABLE IF EXISTS `osc_catalog_order_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_order_item` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(52) NOT NULL,
  `order_id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `options` text NOT NULL,
  `price` int(11) NOT NULL,
  `cost` int(11) NOT NULL DEFAULT 0,
  `quantity` int(11) NOT NULL,
  `refunded_quantity` int(11) NOT NULL DEFAULT 0,
  `process_quantity` int(11) NOT NULL DEFAULT 0,
  `fulfilled_quantity` int(11) NOT NULL DEFAULT 0,
  `require_shipping` tinyint(1) NOT NULL,
  `require_packing` tinyint(1) NOT NULL,
  `weight` int(11) NOT NULL DEFAULT 0,
  `weight_unit` enum('kg','g','oz','lb') NOT NULL DEFAULT 'g',
  `keep_flat` tinyint(1) NOT NULL DEFAULT 1,
  `dimension_width` int(11) NOT NULL DEFAULT 0,
  `dimension_height` int(11) NOT NULL DEFAULT 0,
  `dimension_length` int(11) NOT NULL DEFAULT 0,
  `custom_data` longtext DEFAULT NULL,
  `discount` text DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`item_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `variant_id` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_order_item`
--

LOCK TABLES `osc_catalog_order_item` WRITE;
/*!40000 ALTER TABLE `osc_catalog_order_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_order_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_order_log`
--

DROP TABLE IF EXISTS `osc_catalog_order_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_order_log` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `action_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`record_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_order_log`
--

LOCK TABLES `osc_catalog_order_log` WRITE;
/*!40000 ALTER TABLE `osc_catalog_order_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_order_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_order_pre_fulfillment`
--

DROP TABLE IF EXISTS `osc_catalog_order_pre_fulfillment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_order_pre_fulfillment` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `queue_flag` tinyint(1) NOT NULL DEFAULT 1,
  `requeue_counter` int(1) DEFAULT 0,
  `error_message` varchar(255) DEFAULT NULL,
  `tracking_number` varchar(255) DEFAULT NULL,
  `shipping_carrier` varchar(255) DEFAULT NULL,
  `tracking_url` varchar(255) DEFAULT NULL,
  `line_items` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `shipping_method` tinyint(1) NOT NULL DEFAULT 0,
  `email_receiver` varchar(255) NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `ukey_UNIQUE` (`order_id`),
  KEY `tracking_number` (`tracking_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_order_pre_fulfillment`
--

LOCK TABLES `osc_catalog_order_pre_fulfillment` WRITE;
/*!40000 ALTER TABLE `osc_catalog_order_pre_fulfillment` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_order_pre_fulfillment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_order_process`
--

DROP TABLE IF EXISTS `osc_catalog_order_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_order_process` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `line_items` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_order_process`
--

LOCK TABLES `osc_catalog_order_process` WRITE;
/*!40000 ALTER TABLE `osc_catalog_order_process` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_order_process` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_order_template_export`
--

DROP TABLE IF EXISTS `osc_catalog_order_template_export`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_order_template_export` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(45) NOT NULL,
  `list_key` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`list_key`)),
  `added_timestamp` int(11) NOT NULL,
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_order_template_export`
--

LOCK TABLES `osc_catalog_order_template_export` WRITE;
/*!40000 ALTER TABLE `osc_catalog_order_template_export` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_order_template_export` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_order_transaction`
--

DROP TABLE IF EXISTS `osc_catalog_order_transaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_order_transaction` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `transaction_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'authorize',
  `amount` int(11) NOT NULL,
  `transaction_data` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_order_transaction`
--

LOCK TABLES `osc_catalog_order_transaction` WRITE;
/*!40000 ALTER TABLE `osc_catalog_order_transaction` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_order_transaction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_product`
--

DROP TABLE IF EXISTS `osc_catalog_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_product` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `position_index` int(10) DEFAULT 0,
  `slug` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content` mediumtext NOT NULL,
  `product_type` varchar(255) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `compare_at_price` int(11) NOT NULL,
  `discarded` tinyint(1) NOT NULL DEFAULT 0,
  `listing` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `solds` int(11) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL DEFAULT 0,
  `tags` text DEFAULT NULL,
  `meta_tags` text DEFAULT NULL,
  `options` text NOT NULL,
  `collection_ids` varchar(255) DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  `master_lock_flag` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_product`
--

LOCK TABLES `osc_catalog_product` WRITE;
/*!40000 ALTER TABLE `osc_catalog_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_product_bulk_queue`
--

DROP TABLE IF EXISTS `osc_catalog_product_bulk_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_product_bulk_queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_id` int(11) NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_data` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_flag` tinyint(1) NOT NULL DEFAULT 0,
  `error` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`queue_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_product_bulk_queue`
--

LOCK TABLES `osc_catalog_product_bulk_queue` WRITE;
/*!40000 ALTER TABLE `osc_catalog_product_bulk_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_product_bulk_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_product_image`
--

DROP TABLE IF EXISTS `osc_catalog_product_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_product_image` (
  `image_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `extension` varchar(3) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `alt` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_product_image`
--

LOCK TABLES `osc_catalog_product_image` WRITE;
/*!40000 ALTER TABLE `osc_catalog_product_image` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_product_image` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_product_review`
--

DROP TABLE IF EXISTS `osc_catalog_product_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_product_review` (
  `record_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ukey` varchar(27) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vote_value` tinyint(1) NOT NULL DEFAULT 5,
  `photo_filename` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `photo_width` int(10) unsigned DEFAULT NULL,
  `photo_height` int(10) unsigned DEFAULT NULL,
  `photo_extension` varchar(4) CHARACTER SET latin1 DEFAULT NULL,
  `review` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  `state` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0:hidden|1:pending|2:approved',
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`),
  KEY `product_id` (`product_id`,`vote_value`),
  KEY `order_id` (`order_id`,`product_id`,`vote_value`),
  KEY `customer_id` (`customer_id`),
  KEY `vote_value` (`vote_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_product_review`
--

LOCK TABLES `osc_catalog_product_review` WRITE;
/*!40000 ALTER TABLE `osc_catalog_product_review` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_product_review` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_product_review_request`
--

DROP TABLE IF EXISTS `osc_catalog_product_review_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_product_review_request` (
  `request_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ukey` varchar(27) CHARACTER SET latin1 NOT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `added_timestamp` int(10) unsigned DEFAULT 0,
  PRIMARY KEY (`request_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`),
  UNIQUE KEY `order_id` (`order_id`,`product_id`),
  KEY `customer_id` (`customer_id`,`order_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_product_review_request`
--

LOCK TABLES `osc_catalog_product_review_request` WRITE;
/*!40000 ALTER TABLE `osc_catalog_product_review_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_product_review_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_product_tabs`
--

DROP TABLE IF EXISTS `osc_catalog_product_tabs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_product_tabs` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `priority` int(10) NOT NULL DEFAULT 0,
  `apply_all` int(1) NOT NULL DEFAULT 0,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_product_tabs`
--

LOCK TABLES `osc_catalog_product_tabs` WRITE;
/*!40000 ALTER TABLE `osc_catalog_product_tabs` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_product_tabs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_product_unique_visit`
--

DROP TABLE IF EXISTS `osc_catalog_product_unique_visit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_product_unique_visit` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `track_key` varchar(27) NOT NULL,
  `product_id` int(11) NOT NULL,
  `unique_timestamp` int(10) NOT NULL,
  `visit_timestamp` int(10) NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `unique` (`track_key`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_product_unique_visit`
--

LOCK TABLES `osc_catalog_product_unique_visit` WRITE;
/*!40000 ALTER TABLE `osc_catalog_product_unique_visit` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_product_unique_visit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_product_variant`
--

DROP TABLE IF EXISTS `osc_catalog_product_variant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_product_variant` (
  `variant_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_id` varchar(255) NOT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `option1` varchar(255) DEFAULT NULL,
  `option2` varchar(255) DEFAULT NULL,
  `option3` varchar(255) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `compare_at_price` int(11) NOT NULL DEFAULT 0,
  `cost` int(11) NOT NULL DEFAULT 0,
  `track_quantity` tinyint(1) NOT NULL DEFAULT 1,
  `overselling` tinyint(1) NOT NULL DEFAULT 0,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `require_shipping` tinyint(1) NOT NULL DEFAULT 0,
  `require_packing` tinyint(1) NOT NULL DEFAULT 0,
  `weight` int(11) NOT NULL DEFAULT 0,
  `weight_unit` enum('kg','g','oz','lb') NOT NULL DEFAULT 'g',
  `keep_flat` tinyint(1) NOT NULL DEFAULT 1,
  `dimension_width` int(11) NOT NULL DEFAULT 0,
  `dimension_height` int(11) NOT NULL DEFAULT 0,
  `dimension_length` int(11) NOT NULL DEFAULT 0,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`variant_id`),
  UNIQUE KEY `unique` (`product_id`,`option1`,`option2`,`option3`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_product_variant`
--

LOCK TABLES `osc_catalog_product_variant` WRITE;
/*!40000 ALTER TABLE `osc_catalog_product_variant` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_product_variant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_catalog_sizing_chart`
--

DROP TABLE IF EXISTS `osc_catalog_sizing_chart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_catalog_sizing_chart` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `product_types` text NOT NULL,
  `content` text NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_catalog_sizing_chart`
--

LOCK TABLES `osc_catalog_sizing_chart` WRITE;
/*!40000 ALTER TABLE `osc_catalog_sizing_chart` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_catalog_sizing_chart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_core_setting`
--

DROP TABLE IF EXISTS `osc_core_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_core_setting` (
  `record_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  `modified_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `setting_key_UNIQUE` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_core_setting`
--

LOCK TABLES `osc_core_setting` WRITE;
/*!40000 ALTER TABLE `osc_core_setting` DISABLE KEYS */;
INSERT INTO `osc_core_setting` VALUES (1,'shipping/table_rate/data','\"US\\/*\\/0\\/6.99\\/20\\nUS\\/*\\/1.99\\/10.99\\/20\\nUS\\/*\\/2.99\\/14.99\\/20\\nUS\\/*\\/3.99\\/18.99\\/20\\nUS\\/*\\/4.99\\/22.99\\/20\\nUS\\/*\\/5.99\\/26.99\\/20\\nUS\\/*\\/6.99\\/30.99\\/20\\nUS\\/*\\/7.99\\/34.99\\/20\\nUS\\/*\\/8.99\\/38.99\\/20\\nUS\\/*\\/9.99\\/42.99\\/20\\n*\\/0\\/7.99\\/20\\n*\\/1.99\\/11.99\\/20\\n*\\/2.99\\/15.99\\/20\\n*\\/3.99\\/19.99\\/20\\n*\\/4.99\\/23.99\\/20\\n*\\/5.99\\/27.99\\/20\\n*\\/6.99\\/31.99\\/20\\n*\\/7.99\\/35.99\\/20\\n*\\/8.99\\/39.99\\/20\\n*\\/8.99\\/43.99\\/20\"',1569486439,1569486439),(2,'shipping/table_rate/free_shipping','\"\"',1569486439,1569486439),(3,'shipping/table_rate','1',1569486439,1569486439),(12,'catalog/twilio/sid','\"\"',1569486528,1569486528),(13,'catalog/twilio/token','\"\"',1569486528,1569486528),(14,'catalog/twilio/service_id','\"\"',1569486528,1569486528),(15,'catalog/twilio/sender_number','\"\"',1569486528,1569486528),(16,'catalog/product_default/listing','1',1569486528,1569486528),(17,'catalog/store/legal_name','\"\"',1569486528,1569486528),(18,'catalog/store/address','{\"address1\":\"7192 Sunrise Drive\",\"address2\":\"\",\"city\":\"North Richland Hills\",\"country\":\"United States\",\"province\":\"American Samoa\",\"zip\":\"76180\",\"country_code\":\"US\",\"province_code\":\"AS\"}',1569486528,1569486528),(19,'catalog/order_code/prefix','\"AS\"',1569486528,1569486528),(20,'catalog/order_code/suffix','\"\"',1569486528,1569486528),(21,'catalog/facebook_feed/collection','\"Please select a collection\"',1569486528,1569486528),(22,'catalog/google_feed/collection','\"Please select a collection\"',1569486528,1569486528),(23,'catalog/auto_export_order/receiver','\"\"',1569486528,1569486528),(24,'catalog/auto_export_order/timezone','\"Please select a value\"',1569486528,1569486528),(25,'theme/site_name','\"Authyshop\"',1569486682,1569487689),(26,'theme/logo',NULL,1569486682,1569487689),(27,'theme/favicon',NULL,1569486682,1569487689),(30,'theme/metadata/title','\"\"',1569486682,1569487689),(31,'theme/metadata/image','\"\"',1569486682,1569487689),(32,'theme/metadata/keyword','\"Be unique\"',1569486682,1569487689),(33,'theme/metadata/description','\"\"',1569486682,1569487689),(34,'theme/contact/name','\"\"',1569486682,1569487689),(35,'theme/contact/address','\"6320 Sunrise Drive, North Richland Hills, TX, 76180 United States\"',1569486682,1569487689),(36,'theme/contact/email','\"support@authyshop.com\"',1569486682,1569487689),(38,'theme/contact/fax','\"\"',1569486682,1569487689),(41,'theme/about','\"\"',1569486682,1569487689),(42,'theme/header/top_menu','\"1\"',1569486682,1569487689),(43,'theme/header/main_menu','\"2\"',1569486682,1569487689),(44,'theme/footer/column1/title','\"AuthyShop\"',1569486682,1569487689),(45,'theme/footer/column1/content','\"<div>6320 Sunrise Drive<br>North Richland Hills, TX, 76180<br>United States<br>support@authyshop.com<br><\\/div>\"',1569486682,1569487689),(46,'theme/footer/column2/title','\"Main Menu\"',1569486682,1569487689),(47,'theme/footer/column2/content','\"2\"',1569486682,1569487689),(48,'theme/footer/column3/title','\"Our Policies\"',1569486682,1569487689),(49,'theme/footer/column3/content','\"3\"',1569486682,1569487689),(50,'theme/footer/copyright','\"Copyright \\u00a9 2019 AuthyShop. All Rights Reserved\"',1569486682,1569487689),(51,'theme/social/facebook','\"\"',1569486682,1569487689),(52,'theme/social/twitter','\"\"',1569486682,1569487689),(53,'theme/social/youtube','\"\"',1569486682,1569487689),(54,'theme/social/instagram','\"\"',1569486682,1569487689);
/*!40000 ALTER TABLE `osc_core_setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_cron_log`
--

DROP TABLE IF EXISTS `osc_cron_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_cron_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_id` int(11) NOT NULL,
  `queue_ukey` varchar(100) NOT NULL,
  `cron_name` varchar(255) NOT NULL,
  `queue_data` text NOT NULL,
  `queue_locked_key` varchar(30) NOT NULL,
  `queue_locked_timestamp` int(10) NOT NULL,
  `log_data` longtext NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_cron_log`
--

LOCK TABLES `osc_cron_log` WRITE;
/*!40000 ALTER TABLE `osc_cron_log` DISABLE KEYS */;
INSERT INTO `osc_cron_log` VALUES (1,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7619809191WID0H06990502',1569486361,'[]',1569486361),(2,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7655c60ad2U8C7T34951904',1569486421,'[]',1569486421),(3,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c76912b9e00F2AZK89627691',1569486481,'[]',1569486481),(4,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c76cd9d9d94KIKP097519789',1569486541,'[]',1569486541),(5,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c770a1cab383HUFX66099244',1569486602,'[]',1569486602),(6,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c774550ed29UHAQ465292129',1569486661,'[]',1569486661),(7,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7781814375TYCBC49411357',1569486721,'[]',1569486721),(8,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c77bdf30cb8PUZ3X85478855',1569486781,'[]',1569486782),(9,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c77f93b9817ZAX0G59992625',1569486841,'[]',1569486841),(10,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c78357f4512SN7KW76127491',1569486901,'[]',1569486901),(11,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7871af96871VWBO25671101',1569486961,'[]',1569486961),(12,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c78ae1be1083ES6P53818197',1569487022,'[]',1569487022),(13,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c78e93a84555W2ZV94919127',1569487081,'[]',1569487081),(14,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7925ef3939A79UV72980819',1569487141,'[]',1569487142),(15,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c796160ef92LU9E053425497',1569487201,'[]',1569487201),(16,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c799d8e02f030Y6D94679832',1569487261,'[]',1569487261),(17,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c79d9c9f365P7WMN49297739',1569487321,'[]',1569487321),(18,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7a1521a598B5QMX45364297',1569487381,'[]',1569487381),(19,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7a515bdaf7S6HSX84787898',1569487441,'[]',1569487441),(20,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7a8d81e2b7A0NOC57023211',1569487501,'[]',1569487501),(21,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7ac9b53a258LA5985998277',1569487561,'[]',1569487561),(22,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7b051fd722N9BFP98329906',1569487621,'[]',1569487621),(23,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7b415e0f12OF2GG37616099',1569487681,'[]',1569487681),(24,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7b7d66be35ZN92V50327975',1569487741,'[]',1569487741),(25,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7bb9adea54ZM6JW16652872',1569487801,'[]',1569487801),(26,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7bf54c37f9ZEMBH01163266',1569487861,'[]',1569487861),(27,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7c31893325J6PB097843711',1569487921,'[]',1569487921),(28,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7c6e236027BUABS57943078',1569487982,'[]',1569487982),(29,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7ca99491d9BTCAT26292282',1569488041,'[]',1569488041),(30,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7ce5aae8a14N4LA78871138',1569488101,'[]',1569488101),(31,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7d2154dd02JSPO239183395',1569488161,'[]',1569488161),(32,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7d5d8b3083VGMFB33794317',1569488221,'[]',1569488221),(33,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7d99d55496M930E03081398',1569488281,'[]',1569488281),(34,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7dd619b7194YRVT19245049',1569488342,'[]',1569488342),(35,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7e114c86a4G5YBE64230620',1569488401,'[]',1569488401),(36,3,'scheduler:catalog/marketing_email_checkout_abandoned:37a6259cc0c1dae299a7866489dff0bd','catalog/marketing_email_checkout_abandoned','null','5d8c7e115a8c59D4V0G81925934',1569488401,'[]',1569488401),(37,4,'scheduler:catalog/discountCode_cleanAutoGenerated:37a6259cc0c1dae299a7866489dff0bd','catalog/discountCode_cleanAutoGenerated','null','5d8c7e115d7230VWPCS46096647',1569488401,'[]',1569488401),(38,8,'scheduler:process_requeue:37a6259cc0c1dae299a7866489dff0bd','process_requeue','null','5d8c7e1160b402C23IM91967700',1569488401,'[\"REQUEUE TOTAL 0 queue(s)\"]',1569488401),(39,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7e4d8a0e66P42J396027603',1569488461,'[]',1569488461),(40,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7e89b5cdc56R8TJ60178691',1569488521,'[]',1569488521),(41,1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd','masterSync/sync','null','5d8c7ec5f3a675Y5T4N42846743',1569488581,'[]',1569488582);
/*!40000 ALTER TABLE `osc_cron_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_cron_queue`
--

DROP TABLE IF EXISTS `osc_cron_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_cron_queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(100) NOT NULL,
  `scheduler_flag` tinyint(1) NOT NULL DEFAULT 0,
  `scheduler_timer` varchar(255) NOT NULL,
  `cron_name` varchar(255) NOT NULL,
  `queue_data` longtext NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  `running_timestamp` int(10) NOT NULL,
  `locked_key` varchar(30) NOT NULL,
  `locked_timestamp` int(10) NOT NULL DEFAULT 0,
  `requeue_limit` int(11) NOT NULL DEFAULT 0,
  `requeue_counter` int(11) NOT NULL DEFAULT 0,
  `error_flag` tinyint(1) NOT NULL DEFAULT 0,
  `error_message` text NOT NULL,
  PRIMARY KEY (`queue_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`),
  KEY `processor` (`cron_name`),
  KEY `locked_key` (`locked_key`),
  KEY `locked_timestamp` (`locked_timestamp`),
  KEY `transport_timestamp` (`running_timestamp`),
  KEY `locked_timestamp_2` (`locked_timestamp`,`running_timestamp`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_cron_queue`
--

LOCK TABLES `osc_cron_queue` WRITE;
/*!40000 ALTER TABLE `osc_cron_queue` DISABLE KEYS */;
INSERT INTO `osc_cron_queue` VALUES (1,'scheduler:masterSync/sync:37a6259cc0c1dae299a7866489dff0bd',1,'* * * * *','masterSync/sync','null',1569486263,1569488640,'',0,0,0,0,''),(2,'scheduler:catalog/product_feed:d751713988987e9331980363e24189ce',1,'@daily','catalog/product_feed','[]',1569486263,1569542400,'',0,0,0,0,''),(3,'scheduler:catalog/marketing_email_checkout_abandoned:37a6259cc0c1dae299a7866489dff0bd',1,'@hourly','catalog/marketing_email_checkout_abandoned','null',1569486263,1569492000,'',0,0,0,0,''),(4,'scheduler:catalog/discountCode_cleanAutoGenerated:37a6259cc0c1dae299a7866489dff0bd',1,'@hourly','catalog/discountCode_cleanAutoGenerated','null',1569486263,1569492000,'',0,0,0,0,''),(5,'scheduler:catalog/order_autoExport:37a6259cc0c1dae299a7866489dff0bd',1,'0 8 * * *','catalog/order_autoExport','null',1569486263,1569571200,'',0,0,0,0,''),(6,'scheduler:log_make:37a6259cc0c1dae299a7866489dff0bd',1,'@daily','log_make','null',1569486263,1569542400,'',0,0,0,0,''),(7,'scheduler:log_clean:37a6259cc0c1dae299a7866489dff0bd',1,'@daily','log_clean','null',1569486263,1569542400,'',0,0,0,0,''),(8,'scheduler:process_requeue:37a6259cc0c1dae299a7866489dff0bd',1,'@hourly','process_requeue','null',1569486263,1569492000,'',0,0,0,0,''),(9,'scheduler:process_clean:37a6259cc0c1dae299a7866489dff0bd',1,'@daily','process_clean','null',1569486263,1569542400,'',0,0,0,0,''),(10,'scheduler:postOffice/email_queue_resend:37a6259cc0c1dae299a7866489dff0bd',1,'@daily','postOffice/email_queue_resend','null',1569486263,1569542400,'',0,0,0,0,'');
/*!40000 ALTER TABLE `osc_cron_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_dmca`
--

DROP TABLE IF EXISTS `osc_dmca`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_dmca` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ukey` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `form` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `added_timestamp` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_dmca`
--

LOCK TABLES `osc_dmca` WRITE;
/*!40000 ALTER TABLE `osc_dmca` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_dmca` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_frontend_index`
--

DROP TABLE IF EXISTS `osc_frontend_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_frontend_index` (
  `index_id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_key` varchar(2) NOT NULL,
  `ukey` varchar(255) NOT NULL,
  `module_key` varchar(100) NOT NULL,
  `item_group` varchar(50) NOT NULL DEFAULT 'default',
  `item_id` int(11) NOT NULL,
  `filter_data` varchar(255) NOT NULL,
  `item_data` text NOT NULL,
  `keywords` text NOT NULL,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`index_id`),
  UNIQUE KEY `ukey` (`ukey`),
  KEY `index` (`lang_key`,`module_key`,`item_group`,`item_id`),
  KEY `filter_data` (`filter_data`),
  FULLTEXT KEY `keywords` (`keywords`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_frontend_index`
--

LOCK TABLES `osc_frontend_index` WRITE;
/*!40000 ALTER TABLE `osc_frontend_index` DISABLE KEYS */;
INSERT INTO `osc_frontend_index` VALUES (1,'','catalog/collection-1','catalog','collection',1,';collect_method:auto;','[]','best selling',1569486912,1569486912),(2,'','catalog/collection-2','catalog','collection',2,';collect_method:auto;','[]','new arrivals',1569486940,1569486940),(3,'','page/page-1','page','page',1,'','[]','faqs howdo one change or cancel my orderinthe consequence y\'all wish to cancel your club please contact united states inside 12 hours uponconfirmation of club at store_email_address please exist advised that anycancellations subsequently 12 hours upon blessing of club volition no longer exist allowedand volition not exist entertain.pleasenote that any orders that accept already been packed or shipped cannot becanceled.what payment methods make y\'all acceptweaccept all major credit cards visa mastercard amex and paypal payments wedo not accept personal checks coin orders directly depository financial institution transfers debit cardpayments or cash on delivery.can one change my transportation address subsequently placing an orderif y\'all change your transportation address pleasecontact united states atstore_email_addresswithin 12 hours subsequently placing customerservice staffs volition match your club and confirm asap.pleasebe advised that your transportation address cannot exist revised subsequently the club hasbeen processed or shipped kindly update your transportation address to yourresidential address instead of your vocational address equally nosotros make not know howlong the destinations customs department volition accept the package on hold.when volition one become my tracking numberonce the club has been processed h5n1 trackingnumber normally takes 1-3 occupation days to exist generated please accept bill toallow one to three occupation days for your tracking data to exist updated if youhave not received your tracking issue inside three occupation days or if thetracking condition is not available inside one to two occupation days fromthe fourth dimension y\'all accept received your tracking issue kindly mail united states an electronic mail atstore_email_addresshow make one track my orderyoucan merely click on track your club on acme ofstore_namepage enter your trackingnumber to match your club status.how long does delivery taketheprocessing fourth dimension for orders is 1-3 occupation days once the shipment is alreadyloaded on the airplane estimated delivery is 5-8 days for the united united states and7-10 days for other countries.pleasetake bill that there are approximately unforeseen circumstances such equally customs delaysthat nosotros are unable to dominance on our end equally well equally approximately delays if there is anupcoming vacation season.how make one render an itemifyou are not happy alongside your buy and wish to render an detail please contactus inside thirty days from receiving your club please supply your club numberas well equally the argue for your render our customer service team volition reviewthe render request and volition mail further instructions if the render isapproved.fora list of concluding sale items please meet our returns policy all returns must bein master condition alongside packaging intact.will one exist charged alongside customs and taxestheprices displayed on our site are tax-free in united states dollars which means y\'all may beliable to pay for duties and taxes once y\'all receive your order.importtaxes duties and related customs fees may exist charged once your club arrivesat its concluding finish which is determined by your local customs office.paymentof these charges and taxes are your responsibility and volition not exist covered byus nosotros are not responsible for delays caused by the customs department in yourcountry for further details of the charges please contact your local customsoffice.i necessitate my club fast make y\'all guys supply expedited shippingifyou desire to receive your club early on than the casual delivery fourth dimension pleasecontact united states equally soon equally y\'all home nosotros volition change the transportation method for you.but y\'all volition pay extra for the transportation service.when volition one receive my refundallrefunds volition exist credited to your master class of payment if y\'all paid bycredit or debit menu refunds volition exist sent to the card-issuing depository financial institution inside 5-10business days of receipt of the returned detail or cancellation request pleasecontact the card-issuing depository financial institution alongside questions approximately when the credit volition beposted to your account.if y\'all havent received acredit for your render however heres what to make contact the bankcredit cardcompany information technology may accept approximately fourth dimension earlier the refund to y\'all',1569486977,1569488022),(4,'','page/page-2','page','page',2,'','[]','payment methods payment methodsmethods of payment available are based onthe nation where the club is from and the full sum on the bill.creditdebit cardyour creditdebit menu volition automaticallybe charged by our fiscal service provider your club volition get down processingafter all necessary verifications accept been completed.paypalthe transaction volition exist processed bypaypal an external fiscal service provider during the checkout processyou volition exist redirected to paypal and pay alongside your bill there your datawill not exist transferred to store_name subsequently paypal has completed verificationyour club volition then exist processed.currencywhen shopping on our website paymentswill exist processed in usd if your credit menu firm or depository financial institution uses h5n1 differentcurrency the concluding transaction price may differ due to currency exchangerates please contact your payment provider for further information.paymentcashon delivery buy club isnt possible at store_name',1569487229,1569488095),(5,'','page/page-3','page','page',3,'','[]','privacy policy privacy policysection1 what make nosotros make alongside your informationwhenyou buy something from store_name equally function of the buying and sellingprocess nosotros collect the personal data y\'all pass united states such equally your nameaddress and electronic mail address besides y\'all volition exist required to providestore_nameinformation regarding your credit menu or another payment instrument yourepresent and warrant to united states that such data is correct and that y\'all areauthorized to function the payment instrument.whenyou browse our shop nosotros besides automatically receive your computers internetprotocol ip address to supply united states alongside data that helps united states teach aboutyour browser and operating system.emailmarketing if applicable alongside your permission nosotros may mail y\'all emails aboutour shop new products and other updates.section2 consenthowdo y\'all become my consentwhenyou supply united states alongside personal data to complete h5n1 transaction verify yourcredit menu home an club arrange for h5n1 delivery or render h5n1 buy weimply that y\'all consent to our collecting information technology and using information technology for that specificreason only.ifwe necessitate for your personal data for h5n1 secondary argue like marketing wewill either necessitate y\'all immediately for your expressed consent or supply y\'all alongside anopportunity to order no.ourpolicy explains what data nosotros collect on the website how nosotros function or sharethis data and how nosotros keep such data by using this websiteyou signify your acceptance of this policy if y\'all make not match to the price ofthis policy in whole or function y\'all should not function this website please notethat this policy applies entirely concerning the data collected on thewebsite and not any data received or obtained through other methods orsources.howdo one remove my consentifafter y\'all opt-in y\'all change your heed y\'all may remove your consent for united states tocontact y\'all for the continued collection function or disclosure of yourinformation at any fourth dimension by contacting united states atstore_email_addresssection3 disclosurewemay unwrap your personal data if nosotros are required by police to make then or ifyou violate our price of service.section4 third-party servicesingeneral the third-party providers used by united states volition entirely collect function anddisclose your data to the extent necessary to let them to perform theservices they supply to us.howevercertain third-party service providers such equally payment gateways and otherpayment transaction processors accept their privacy policies concerning theinformation nosotros are required to supply to them for your purchase-relatedtransactions.forthese providers nosotros recommend that y\'all read their privacy policies then y\'all canunderstand the manner in which these providers volition manage your personalinformation.inparticular remember that certain providers may exist located in or havefacilities that are located in h5n1 unlike jurisdiction than either y\'all or us.so if y\'all elect to continue alongside h5n1 transaction that involves the services of athird-party service provider then your data may become discipline to thelaws of the jurisdictions in which that service provider or its facilitiesare located.asan case if y\'all are located in canada and your transaction is processed bya payment gateway located in the united united states then your personal informationused in completing that transaction may exist discipline to disclosure nether unitedstates legislation including the patriot act.onceyou exit our stores website or are redirected to h5n1 third-party site orapplication y\'all are no longer governed by this privacy policy or our websitesterms of service.linkswhenyou click on links on our shop they may directly y\'all away from our site nosotros arenot responsible for the privacy practices of other sites and encourage y\'all toread their privacy statements.section5 securitytoprotect your personal data nosotros accept reasonable precautions and followindustry best practices to brand certain information technology is not inappropriately lost misusedaccessed disclosed altered or destroyed.ifyou supply united states alongside your credit menu data the data is encryptedusing secure socket layer engineering ssl and stored alongside aes-256 encryption.although no method of transmission over the internet or electronic storage is100 condom nosotros follow all pci-dss requirements and implement additionalgenerally accepted industry standards.at store_name nosotros never share our customers data alongside the third party in any manner weuse the data that y\'all supply for such purposes equally responding to yourrequests customizing future shopping for y\'all improving our stores andcommunicating alongside y\'all nosotros always attempt to personalize and continually improveyourstore_nameshopping experience.section6 cookiesweuse cookies engineering to shop data on your calculator using thefunctionality of your browser h5n1 lot of websites make this because cookies allowthe website publisher to make useful things like finding out whether the computerand probably its user has visited the site earlier y\'all displace normally modifyyour browser to prevent cookie function merely if y\'all make this the service and thewebsite may not function correctly the data stored in the cookie is usedto identify y\'all this enables united states to function an efficient service and to trackthe patterns of demeanor of visitors to the website.alsoin the class of serving advertisements to this website if any third-partyadvertisers or ad servers may home or recognize h5n1 unique cookie on yourbrowser the function of cookies by such third party advertisers or ad servers isnot discipline to this policy merely is discipline to their respective privacy policies.please bill that function of the website is neither intended for nor directed tochildren nether the historic period of 18.section7 changes to this privacy policywereserve the correct to change this privacy policy at any fourth dimension then please reviewit frequently changes and clarifications volition accept consequence immediately upontheir posting on the website depending on the nature of the change nosotros mayannounce the change h5n1 on the homepage of the website or b by electronic mail if wehave your electronic mail address however in any consequence by continuing to function thewebsite following any changes y\'all volition exist deemed to accept agreed to suchchanges if y\'all make not match alongside the price of this policy equally information technology may beamended from fourth dimension to fourth dimension in whole or function y\'all must displace your function of thewebsite.questionsand contact informationifyou would like to access correct better or delete any personal data wehave approximately y\'all register h5n1 charge or merely desire more data contact ourprivacy compliance officeholder atstore_email_addressstore_nameservices proprietary rightsservicecontent software and trademarks y\'all are entirely authorized to function the store_name service to engage in occupation transactions alongside store_name y\'all may not function anyautomated engineering to scrape mine or assemble any data from store_name service or otherwise access the pages of store_name service for any unauthorizedpurpose if store_name service blocks y\'all from accessing store_name service includingby blocking your ip address y\'all match not to implement any measures tocircumvent such blocking e.g by masking your ip address or using h5n1 proxy ipaddress the engineering and software underlying the store_name service ordistributed in connection in summation to that are the property of store_name ouraffiliates and our partners the software y\'all match not to imitate modifycreate h5n1 derivative function of reverse engineer reverse assemble or otherwiseattempt to detect any beginning code sell assign sublicense or otherwisetransfer any correct in the software.store_name service may incorporate images artwork fonts and other content or featuresservice content that are protected by intellectual property rights andlaws except equally expressly authorized by store_name y\'all match not to change copyframe rent lease loan sell distribute or make derivative plant based onthe store_name service or the service content in whole or in function any function of the store_name service or the service content other than equally specifically authorized herein isstrictly prohibited store_name reserves any rights not expressly granted herein.the store_name mention and logos are trademarks and service marks of store_name collectivelythe store_name trademarks other firm product and service names and logosused and displayed via the store_name service may exist trademarks or service marks oftheir respective owners who may or may not endorse or exist affiliated alongside orconnected to store_name nothing in these price of service or the store_name serviceshould exist construed equally granting any license or correct to function any of store_name trademarks displayed on the store_name service without our prior writtenpermission in each case all goodwill generated from the function of store_name trademarks volition inure to store_names exclusive benefit.thirdparty fabric nether no circumstances volition store_name exist liable in any manner for anycontent or material of any third parties including users including merely notlimited to for any errors or omissions in any content or for any loss ordamage of any kind incurred equally h5n1 consequence of the function of any such content ormaterials to the maximum extent permitted nether applicable police the thirdparty providers of such content and material are express and intended thirdparty beneficiaries of these price of services alongside respect to their contentand materials.store_name may save content and may besides unwrap content if required to make then by lawor in the adept religion belief that such preservation or disclosure is reasonablynecessary to h5n1 comply alongside legal procedure applicable laws or governmentrequests b enforce these price of service c answer to claims that anycontent violates the rights of third parties or d protect the rightsproperty or personal condom of store_name its users or the populace',1569487364,1569488331),(6,'','page/page-4','page','page',4,'','[]','returns refund policy render policy1 not happy alongside your orderif y\'all are not happy alongside your purchase30 days from date of that y\'all received the product in like-new condition withno visible clothing and tear y\'all buyer volition exist the one who is responsible forpaying for the transportation costs for returning detail if not covered by our warrantyagainst manufacturer defects and central is not due to our error.2 damaged items or low-qualityif the product is defective or does notwork properly please kindly let united states know for the fastest resolution pleasecontact united states viastore_email_addressincluding h5n1 photo demonstrating the poorquality or the damaged area of the detail the about optimal pictures are on aflat surface alongside the tag and mistake clearly displayed well mail youreplacements equally soon equally nosotros confirmed the situation no necessitate to render thedefective ones nosotros volition function this data to aid y\'all alongside your club andeliminate errors in the future.cancellation1 canceling unshipped-out ordersif y\'all are canceling your club which has notyet to exist shipped out please kindly contact united states via store_email_address for thefastest resolution please include your club issue thanks2 cancelling shipped-out orderif y\'all are canceling orders when yourparcel has already been shipped out or on its manner to h5n1 finish pleasecontact united states and then kindly refuse to accept the package since nosotros are not able tocall information technology back at that fourth dimension nosotros volition refund your payment subsequently deducting shippingcosts refund volition exist issued equally soon equally package begins to return.warrantythis warranty entirely covers manufacturingdefects and does not coverdamage caused by accidentimproper carenormal clothing and tearbreak down of colors and material due tosun exposureaftermarket modificationsplease bill no returnsexchanges forproducts alongside water exposure volition exist accepted.refund policyif y\'all feel that the product youvepurchased does not meet the requirements y\'all accept in heed then y\'all make accept theoption to request h5n1 refund.below are the weather nether whichrefund volition exist granted.you displace become h5n1 full refund ifif the product y\'all purchased iscompletely non-functional.if y\'all did not receive your productwithin thirty occupation days subsequently the date y\'all placed your order.please bill the refund volition become back toyour bill in 5-10 occupation days.shipping costsyou volition exist responsible for paying foryour own transportation costs for returning detail transportation costs are non-refundable.if y\'all receive h5n1 refund the price of render transportation volition exist deducted from yourrefund.if y\'all are transportation an detail over 100 youshould see using h5n1 trackable transportation service or purchasing shippinginsurance thank youdamagedlow-quality itemfor the fastest resolution please includea photo demonstrating the hapless quality or the damaged area of the item.ideally the pictures should exist on h5n1 apartment surface alongside the tag and errorclearly displayed.we volition function this data to aid youwith your club and to prevent repeated errors in the future.if youhave other concerns and inquiries kindly mail h5n1 mail to store_email_address',1569487374,1569488368),(7,'','page/page-5','page','page',5,'','[]','transportation policy countries nosotros canship nosotros transport worldwide.deliverytime when placing your club nosotros see these factors when calculating the estimateddelivery dateorderprocessing the sum of timeit takes for united states to cook your club for transportation subsequently your payment isauthorized and verified this typically takes 1-3 occupation days.note processing fourth dimension for customizedpersonalized may accept longer information technology normally takes five to ten occupation days.transit fourth dimension the sum of fourth dimension information technology takes your club to exit ourwarehouse and arrive at the local delivery carrier information technology may accept from 5-10business days.estimated transportation shippingcharges are estimated due to place and weight the minimum transportation fee willbe 6.99.pleasenote that these are estimated delivery times only.pleaseensure all delivery data is correct if there is incorrect or missinginformation nosotros may exist required to contact y\'all for the update on the deliveryinformation which displace campaign delays in delivering your club delays may alsooccur equally h5n1 consequence of customs clearance.please fill in youraddress in all details otherwise the package nosotros mail to y\'all volition exist returnedto united states or nosotros volition merely ignore your request to salvage everyone the trouble',1569487384,1569487388),(8,'','page/page-6','page','page',6,'','[]','price of service overviewstore_name operates this website throughout thesite the price nosotros united states and our mention to store_name.store_nameoffersthis website including all data tools and services available fromthis site to y\'all the user conditioned upon your acceptance of all termsconditions policies and notices stated here.byvisiting our site or purchasing something from united states y\'all engage in our serviceand match to exist jump by the following price and weather price of serviceterms including those additional price and weather and policiesreferenced herein or available by hyperlink these price of service use toall users of the site including without limitation users who are browsersvendors customers merchants and or contributors of content.pleaseread these price of service carefully earlier accessing or using our website byaccessing or using any function of the site y\'all match to exist jump by these termsof service if y\'all make not match to all the price and weather of this agreementthen y\'all may not access the website or function any services if these price ofservice are considered an offer acceptance is expressly express to these termsof service.anynew features or tools which are added to the stream shop shall besides exist subjectto the price of service y\'all displace review the about stream version of the termsof service at any fourth dimension on this page nosotros reserve the correct to update change orreplace any function of these price of service by posting updates or changes to ourwebsite information technology is your responsibility to match this page periodically for changes.your continued function of or access to the site following the posting of anychanges constitutes acceptance of those changes.ourstore is hosted on shopify inc they supply united states alongside an online e-commerceplatform that allows united states to sell our products and services to you.section1 online shop termsbyagreeing to these price of service y\'all represent that y\'all are at least the ageof majority in your country or state of residence or that y\'all are the historic period ofmajority in your country or state of residence and y\'all accept given united states yourconsent to let any of your child dependents to function this site.youmay not function our products for any illegal or unauthorized function nor may youin the function of the service violate any laws in your jurisdiction including butnot express to copyright laws.youmust not transmit any worms or viruses or any code of h5n1 destructive nature.abreach or violation of any of the price volition consequence in immediate result ofyour services.section2 general conditionswereserve the correct to refuse service to anyone for any argue at any time.youunderstand that your content not including credit menu data may betransferred unencrypted and necessitate h5n1 transmissions over diverse networksand b changes to arrange and arrange to technical requirements of connectingsystems or devices credit menu data is always encrypted during transferover networks.youagree not to reproduce duplicate imitate sell resell or exploit any part ofthe service function of the service or access to the service or any contact on thewebsite through which the service is provided without express wrote permissionby us.theheadings used in this agreement are included for convenience entirely and volition notlimit or otherwise affect these terms.section3 accuracy completeness and timeliness of informationweare not responsible if data made available on this site is not accuratecomplete or stream the fabric on this site is provided for generalinformation entirely and should not exist relied upon or used equally the sole footing formaking decisions without consulting chief more accurate more complete ormore timely sources of data any reliance on the fabric on this siteis at your own risk.thissite may incorporate specific historical data historical informationnecessarily is not stream and is provided for your reference entirely nosotros reservethe correct to change the contents of this site at any fourth dimension merely nosotros accept noobligation to update any data on our website y\'all match that information technology is yourresponsibility to monitor changes to our site.section4 modifications to the service and pricespricesfor our products are discipline to change without notice.wereserve the correct at any fourth dimension to change or discontinue the service or any partor content thereof without detect at any time.weshall not exist liable to y\'all or any third-party for any change pricechange pause or discontinuance of the service.section5 products or services if applicablecertainproducts or services may exist available entirely online through the website.these products or services may accept express quantities and are discipline to returnor central entirely according to our render policy.wehave made every attempt to display equally accurately equally possible the colors andimages of our products that look at the shop nosotros cannot guarantee that yourcomputer monitors display of any coloring material volition exist accurate.wereserve the correct merely are not obligated to restrict the sales of our products orservices to any person geographic region or jurisdiction nosotros may practice thisright on h5n1 case-by-case footing nosotros reserve the correct to restrict the quantities ofany products or services that nosotros offer all descriptions of products or productpricing are discipline to change at any fourth dimension without detect at the solediscretion of united states nosotros reserve the correct to discontinue any product at any time.any offer for any product or service made on this site is void whereprohibited.wedo not warrant that the quality of any products services data orother fabric purchased or obtained by y\'all volition meet your expectations orthat any errors in the service volition exist corrected.section6 accuracy of billing and bill informationwereserve the correct to refuse any club y\'all home alongside united states nosotros may in our solediscretion restrict or cancel quantities purchased per person per family oreach club these restrictions may include orders placed by or nether the samecustomer bill the same credit menu or orders that function the equal billing orshipping address if nosotros brand h5n1 change to or cancel an club nosotros may try tonotify y\'all by contacting the electronic mail or billing addressphone issue provided atthe fourth dimension the club was made nosotros reserve the correct to restrict or prohibit ordersthat in our sole judgment look to exist placed by dealers resellers ordistributors.youagree to supply stream complete and accurate buy and bill informationfor all purchases made at our shop y\'all match to promptly update your accountand other data including your electronic mail address and credit menu numbers andexpiration dates then that nosotros displace complete your transactions and contact y\'all asneeded.formore details please review our returns policy.section7 optional toolswemay supply y\'all alongside access to third-party tools over which nosotros neither monitornor accept any dominance nor input.youacknowledge and match that nosotros supply access to such tools equally is and asavailable without any warranties representations or weather of any kindand any endorsement nosotros shall accept no liability any arising from orrelating to your function of optional third-party tools.anyuse by y\'all of optional tools offered through the site is entirely at your ownrisk and discretion and y\'all should ensure that y\'all are familiar alongside andapprove of the price on which tools are provided by the relevant third-partyproviders.wemay besides in the future offer new services or features through the websiteincluding the free of new tools and resource such new features orservices shall besides exist discipline to these price of service.section8 third-party linkscertaincontent products and services available via our service may include materialfrom third-parties.third-partylinks on this site may directly y\'all to third-party websites that are notaffiliated alongside united states nosotros are not responsible for examining or evaluating thecontent or accuracy and nosotros make not warrant and volition not accept any liability orresponsibility for any third-party material or websites or for any othermaterials products or services of third-parties.weare not liable for any damage or damages related to the buy or function of goodsservices resource content or any other transactions made in connection withany third-party websites please review the third-partys policies andpractices carefully and brand certain y\'all sympathize them earlier y\'all engage in anytransaction complaints claims concerns or questions regarding third-partyproducts should exist directed to the third-party.section9 user comments feedback and other submissionsifat our request y\'all mail certain specific submissions for case contestentries or without h5n1 request from united states y\'all mail creative ideas suggestionsproposals plans or other material whether online by electronic mail by postal mailor otherwise collectively comments y\'all match that nosotros may at any timewithout restriction edit imitate publish distribute translate and otherwiseuse in any medium any comments that y\'all forward to united states nosotros are and shall beunder no obligation one to keep any comments in confidence two to paycompensation for any comments or three to answer to any comments.wemay merely accept no obligation to monitor edit or remove content that wedetermine in our sole discretion are unlawful offensive threateninglibelous defamatory pornographic obscene or otherwise objectionable orviolates any partys intellectual property or these price of service.youagree that your comments volition not violate any correct of any third-partyincluding copyright trademark privacy personality or other personal orproprietary correct y\'all further match that your comments volition not containlibelous or otherwise unlawful abusive or obscene fabric or incorporate anycomputer virus or other malware that could in any manner affect the operation ofthe service or any related website y\'all may not function h5n1 fake electronic mail addresspretend to exist person other than yourself or otherwise mislead united states orthird-parties equally to the beginning of any comments y\'all are entirely responsible forany comments y\'all brand and their accuracy nosotros accept no responsibility and assumeno liability for any comments posted by y\'all or any third-party.section10 personal informationourprivacy policy governs your submission of personal data through thestore to opinion our privacy policy.section11 errors inaccuracies and omissionsoccasionallythere may exist data on our site or in the service that containstypographical errors inaccuracies or omissions that may relate to productdescriptions pricing promotions offers product transportation charges transittimes and availability nosotros reserve the correct to correct any errorsinaccuracies or omissions and to change or update data or cancel ordersif any data in the service or on any related website is inaccurate atany fourth dimension without prior detect including subsequently y\'all accept submitted your order.weundertake no obligation to update better or clarify data in the serviceor on any related website including without limitation pricing informationexcept equally required by police no specified update or refresh date applied in theservice or on any relevant site should exist taken to bespeak that allinformation in the service or on any related website has been modified orupdated.section12 prohibited usesinaddition to other prohibitions equally fix forth in price of service y\'all areprohibited from using the site or its content h5n1 for any unlawful purposeb to solicit others to perform or participate in any unlawful acts c toviolate any international federal provincial or country regulations ruleslaws or local ordinances d to infringe upon or violate our intellectualproperty rights or the intellectual property rights of others east to harassabuse insult damage defame slander disparage intimidate or discriminatebased on sex sexual orientation religion ethnicity race historic period nationalorigin or disability f to submit fake or misleading data thousand toupload or transmit viruses or any other type of malicious code that volition or possibly used in any manner that volition affect the functionality or operation of theservice or of any related website other websites or the internet h tocollect or track the personal data of others one to spam phish pharmpretext spider crawl or scrape j for any obscene or immoral function ork to interfere alongside or circumvent the safety features of the service or anyrelated website other websites or the internet nosotros reserve the correct toterminate your function of the service or any related website for violating any ofthe prohibited uses.section13 disclaimer of warranties limitation of liabilitywedo not guarantee represent or warrant that your function of our service volition beuninterrupted timely secure or error-free.wedo not warrant that the results that may exist obtained from the function of theservice volition exist accurate or reliable.youagree that from fourth dimension to fourth dimension nosotros may remove the service for indefinite periodsof fourth dimension or cancel the service at any fourth dimension without detect to you.youexpressly match that your function of or inability to function the service is at yoursole gamble the service and all products and services delivered to y\'all throughthe service are except equally expressly stated by united states provided equally is and asavailable for your function without any representations warranties or conditionsof any kind either express or implied including all implied warranties orconditions of merchantability merchantable quality fitness for h5n1 particularpurpose durability championship and non-infringement.inno event shall store_site our directors officers employees affiliatesagents contractors interns suppliers service providers or licensors areliable for any injury loss claim or any directly indirect incidentalpunitive especial or consequential damages of any kind including withoutlimitation lost net income lost revenue lost savings loss of data replacementcosts or any like damages whether based in contract tort includingnegligence strict liability or otherwise arising from your function of any of theservice or any products procured using the service or for any other claimrelated in any manner to your function of the service or any product including butnot express to any errors or omissions in any content or any loss or damageof any kind incurred equally h5n1 consequence of the function of the service or any content orproduct posted transmitted or otherwise made available via the service evenif advised of their possibility because of approximately united states or jurisdictions make notallow the exclusion or the limitation of liability for consequential orincidental damages in such united states or jurisdictions our liability shall belimited to the maximum extent permitted by law.section14 indemnificationyouagree to indemnify defend and agree harmlessstore_siteand our parentsubsidiaries affiliates partners officers directors agents contractorslicensors service providers subcontractors suppliers interns and employeesharmless from any claim or necessitate including reasonable attorneys fees madeby any third-party due to or arising out of your breach of these price ofservice or the documents they incorporate by reference or your violation of anylaw or the rights of h5n1 third-party.section15 severabilityinthe consequence that any provision of these price of service is determined to beunlawful void or unenforceable such provision shall however beenforceable to the fullest extent permitted by applicable police and theunenforceable part shall exist deemed to exist severed from these price ofservice such decision shall not affect the validity and enforceability ofany other remaining provisions.section16 terminationtheobligations and liabilities of the parties incurred prior to the terminationdate shall survive the result of this agreement for all purposes.theseterms of service are effective unless and until terminated by either y\'all or us.you may displace these price of service at any fourth dimension by notifying united states that youno longer wish to function our services or when y\'all end using our site.ifin our sole judgment y\'all fail or nosotros suspect that y\'all accept failed to complywith any term or provision of these price of service nosotros besides may terminatethis agreement at any fourth dimension without detect and y\'all volition stay liable for allamounts due up to and including the date of result andor accordingly maydeny y\'all access to our services or any function thereof.section17 entire agreementthefailure of united states to practice or enforce any correct or provision of these price ofservice shall not establish h5n1 waiver of such correct or provision.theseterms of service and any policies or operating rules posted by united states on this siteor in respect to the service constitutes the entire agreement and understandingbetween y\'all and united states and govern your function of the service superseding any prior orcontemporaneous agreements communications and proposals whether oral orwritten between y\'all and united states including merely not express to any prior versionsof the price of service.anyambiguities in the interpretation of these price of service shall not beconstrued against the drafting party.section18 changes to price of serviceyoucan review the about stream version of the price of service at any fourth dimension at thispage.wereserve the correct at our sole discretion to update change or supplant anypart of these price of service by posting updates and changes to our website.it is your responsibility to match our website periodically for changes yourcontinued function of or access to our website or the service following the postingof any changes to these price of service constitutes acceptance of thosechanges.section19 contact informationquestions approximately the termsof service should exist sent to united states at store_email_address',1569487392,1569488443);
/*!40000 ALTER TABLE `osc_frontend_index` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_groups`
--

DROP TABLE IF EXISTS `osc_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `lock_flag` tinyint(1) NOT NULL DEFAULT 0,
  `title` varchar(32) NOT NULL DEFAULT '',
  `perm_mask_ids` varchar(255) NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_groups`
--

LOCK TABLES `osc_groups` WRITE;
/*!40000 ALTER TABLE `osc_groups` DISABLE KEYS */;
INSERT INTO `osc_groups` VALUES (1,1,'Guest','',0,0),(2,1,'Thành viên','',0,0),(3,1,'Root Admin','',0,1317292132);
/*!40000 ALTER TABLE `osc_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_homepage`
--

DROP TABLE IF EXISTS `osc_homepage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_homepage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `ukey` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `description` varchar(255) NOT NULL,
  `position` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_homepage`
--

LOCK TABLES `osc_homepage` WRITE;
/*!40000 ALTER TABLE `osc_homepage` DISABLE KEYS */;
INSERT INTO `osc_homepage` VALUES (1,'Banner slider','QC2195d8c7a9e023ca','banner_slider','section/bannerSlider','[{\"banner_name\":{\"label\":\"T\\u00ean banner\",\"type\":\"text\",\"name\":\"banner_name\",\"value\":\"\"},\"banner_link\":{\"label\":\"Banner link\",\"type\":\"text\",\"name\":\"banner_link\",\"value\":\"#\",\"description\":\"Nh\\u1eadp link banner\"},\"banner_image\":{\"label\":\"Banner image\",\"type\":\"file\",\"name\":\"banner_image\",\"value\":\"\",\"description\":\"Upload banner\"}}]','',1),(2,'New Arrivals','0HPXU5d8c7aa3954a2','products','section/products','{\"collection_id\":{\"label\":\"Select collection\",\"type\":\"select\",\"name\":\"collection_id\",\"options\":{\"1\":\"Best Selling\",\"2\":\"New Arrivals\"},\"value\":\"2\"},\"filter_by\":{\"label\":\"Get product by\",\"type\":\"select\",\"name\":\"filter_by\",\"options\":{\"solds\":\"Best selling\",\"title_az\":\"Product title A-Z\",\"title_za\":\"Product title Z-A\",\"highest_price\":\"Highest price\",\"lowest_price\":\"Lowest price\",\"newest\":\"Newest\",\"oldest\":\"Oldest\",\"manual\":\"Manual\"},\"value\":\"newest\",\"description\":\"This option apply for select All product(Not select collection above)\"},\"product_to_show\":{\"label\":\"Products to show\",\"type\":\"text\",\"name\":\"product_to_show\",\"value\":\"8\",\"description\":\"Enter number of products to show in this section\"},\"grid_col\":{\"label\":\"Grid column\",\"type\":\"text\",\"name\":\"grid_col\",\"value\":\"4\",\"description\":\"Number of column\"},\"collection_display_format\":{\"label\":\"Collection Display Format\",\"type\":\"select\",\"name\":\"collection_display_format\",\"options\":{\"1\":\"style1\",\"2\":\"style2\",\"3\":\"style3\",\"4\":\"style4\",\"5\":\"style5\",\"6\":\"style6\"},\"value\":\"1\",\"description\":\"Select a style for collection display format\"},\"collection_banner\":{\"label\":\"Collection Banner\",\"type\":\"file\",\"name\":\"collection_banner\",\"value\":\"\",\"description\":\"Upload banner\"}}','',2),(3,'Best Selling','WO1OI5d8c7aa985f4e','products','section/products','{\"collection_id\":{\"label\":\"Select collection\",\"type\":\"select\",\"name\":\"collection_id\",\"options\":{\"1\":\"Best Selling\",\"2\":\"New Arrivals\"},\"value\":\"1\"},\"filter_by\":{\"label\":\"Get product by\",\"type\":\"select\",\"name\":\"filter_by\",\"options\":{\"solds\":\"Best selling\",\"title_az\":\"Product title A-Z\",\"title_za\":\"Product title Z-A\",\"highest_price\":\"Highest price\",\"lowest_price\":\"Lowest price\",\"newest\":\"Newest\",\"oldest\":\"Oldest\",\"manual\":\"Manual\"},\"value\":\"solds\",\"description\":\"This option apply for select All product(Not select collection above)\"},\"product_to_show\":{\"label\":\"Products to show\",\"type\":\"text\",\"name\":\"product_to_show\",\"value\":\"8\",\"description\":\"Enter number of products to show in this section\"},\"grid_col\":{\"label\":\"Grid column\",\"type\":\"text\",\"name\":\"grid_col\",\"value\":\"4\",\"description\":\"Number of column\"},\"collection_display_format\":{\"label\":\"Collection Display Format\",\"type\":\"select\",\"name\":\"collection_display_format\",\"options\":{\"1\":\"style1\",\"2\":\"style2\",\"3\":\"style3\",\"4\":\"style4\",\"5\":\"style5\",\"6\":\"style6\"},\"value\":\"1\",\"description\":\"Select a style for collection display format\"},\"collection_banner\":{\"label\":\"Collection Banner\",\"type\":\"file\",\"name\":\"collection_banner\",\"value\":\"\",\"description\":\"Upload banner\"}}','',3);
/*!40000 ALTER TABLE `osc_homepage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_mastersync_queue`
--

DROP TABLE IF EXISTS `osc_mastersync_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_mastersync_queue` (
  `queue_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ukey` varchar(255) DEFAULT NULL,
  `sync_key` varchar(255) NOT NULL,
  `sync_data` longtext DEFAULT NULL,
  `syncing_flag` tinyint(1) NOT NULL DEFAULT 0,
  `error_message` longtext DEFAULT NULL,
  `running_timestamp` int(10) NOT NULL DEFAULT 0,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`queue_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_mastersync_queue`
--

LOCK TABLES `osc_mastersync_queue` WRITE;
/*!40000 ALTER TABLE `osc_mastersync_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_mastersync_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_members`
--

DROP TABLE IF EXISTS `osc_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_members` (
  `member_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `username` varchar(32) NOT NULL,
  `password_hash` varchar(32) NOT NULL,
  `auth_secret_key` varchar(16) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL,
  `avatar_extension` varchar(4) NOT NULL,
  `perm_mask_ids` varchar(255) NOT NULL,
  `timezone` varchar(5) NOT NULL DEFAULT '7',
  `activated` tinyint(1) NOT NULL DEFAULT 1,
  `suspended` tinyint(1) NOT NULL DEFAULT 0,
  `suspend_expire_timestamp` int(10) NOT NULL DEFAULT 0,
  `suspend_reason` varchar(255) DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  `last_visited_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`member_id`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_members`
--

LOCK TABLES `osc_members` WRITE;
/*!40000 ALTER TABLE `osc_members` DISABLE KEYS */;
INSERT INTO `osc_members` VALUES (1,3,'administrator','afa6f97ab28c5fb2a2e8f747554c097e','','webmaster@osecore.com','jpg','','7',1,0,0,NULL,0,1564719397,1345648291),(2,3,'admin','05487ee141be6528016606898bb08a0e','','admin@authyshop.com','','','7',1,0,0,NULL,1569486385,1569486385,0);
/*!40000 ALTER TABLE `osc_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_migrate_gearlaunch`
--

DROP TABLE IF EXISTS `osc_migrate_gearlaunch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_migrate_gearlaunch` (
  `queue_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `queue_key` int(10) unsigned NOT NULL,
  `queue_flag` tinyint(1) NOT NULL DEFAULT 1,
  `error_flag` tinyint(1) NOT NULL DEFAULT 0,
  `error_message` varchar(255) DEFAULT NULL,
  `action_key` varchar(100) NOT NULL,
  `action_data` text NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`queue_id`),
  KEY `queue_key` (`queue_key`,`queue_flag`,`added_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_migrate_gearlaunch`
--

LOCK TABLES `osc_migrate_gearlaunch` WRITE;
/*!40000 ALTER TABLE `osc_migrate_gearlaunch` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_migrate_gearlaunch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_navigation`
--

DROP TABLE IF EXISTS `osc_navigation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_navigation` (
  `navigation_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `items` text NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`navigation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_navigation`
--

LOCK TABLES `osc_navigation` WRITE;
/*!40000 ALTER TABLE `osc_navigation` DISABLE KEYS */;
INSERT INTO `osc_navigation` VALUES (1,'Top bar','[{\"title\":\"Track order\",\"url\":\"https:\\/\\/authyshop.com\\/catalog\\/frontend\\/orderDetail\",\"source_icon\":\"tags\",\"source_title\":\"Track order\",\"parent_id\":\"root\"},{\"title\":\"Contact\",\"url\":\"https:\\/\\/authyshop.com\\/contact\",\"source_icon\":\"email\",\"source_title\":\"Contact\",\"parent_id\":\"root\"}]',1569486771,1569486771),(2,'Main menu','[{\"title\":\"All products\",\"url\":\"https:\\/\\/authyshop.com\\/catalog\\/collections\",\"source_icon\":\"tags\",\"source_title\":\"All Collections\",\"parent_id\":\"root\"},{\"title\":\"Best Selling\",\"url\":\"https:\\/\\/authyshop.com\\/catalog\\/collection\\/1\\/Best_Selling\",\"source_icon\":\"tags\",\"source_title\":\"Best Selling\",\"parent_id\":\"root\"},{\"title\":\"New Arrivals\",\"url\":\"https:\\/\\/authyshop.com\\/catalog\\/collection\\/2\\/New_Arrivals\",\"source_icon\":\"tags\",\"source_title\":\"New Arrivals\",\"parent_id\":\"root\"}]',1569486803,1569486803),(3,'Footer menu','[{\"title\":\"FAQs\",\"url\":\"https:\\/\\/authyshop.com\\/page\\/1\\/FAQs\",\"source_icon\":\"file-regular\",\"source_title\":\"FAQs\",\"parent_id\":\"root\"},{\"title\":\"Payment Methods\",\"url\":\"https:\\/\\/authyshop.com\\/page\\/2\\/Payment_Methods\",\"source_icon\":\"file-regular\",\"source_title\":\"Payment Methods\",\"parent_id\":\"root\"},{\"title\":\"Privacy Policy\",\"url\":\"https:\\/\\/authyshop.com\\/page\\/3\\/Privacy_Policy\",\"source_icon\":\"file-regular\",\"source_title\":\"Privacy Policy\",\"parent_id\":\"root\"},{\"title\":\"Returns & Refund Policy\",\"url\":\"https:\\/\\/authyshop.com\\/page\\/4\\/Returns_Refund_Policy\",\"source_icon\":\"file-regular\",\"source_title\":\"Returns & Refund Policy\",\"parent_id\":\"root\"},{\"title\":\"Terms of Service\",\"url\":\"https:\\/\\/authyshop.com\\/page\\/6\\/Terms_of_Service\",\"source_icon\":\"file-regular\",\"source_title\":\"Terms of Service\",\"parent_id\":\"root\"},{\"title\":\"Track order\",\"url\":\"https:\\/\\/authyshop.com\\/catalog\\/frontend\\/orderDetail\",\"source_icon\":\"tags\",\"source_title\":\"Track order\",\"parent_id\":\"root\"}]',1569486844,1569486844);
/*!40000 ALTER TABLE `osc_navigation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_page`
--

DROP TABLE IF EXISTS `osc_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_page` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_key` varchar(45) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `meta_tags` text NOT NULL,
  `published_flag` tinyint(1) NOT NULL DEFAULT 1,
  `publish_start_timestamp` int(10) NOT NULL,
  `publish_to_timestamp` int(10) NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `system_key_UNIQUE` (`page_key`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_page`
--

LOCK TABLES `osc_page` WRITE;
/*!40000 ALTER TABLE `osc_page` DISABLE KEYS */;
INSERT INTO `osc_page` VALUES (1,NULL,'FAQs','FAQs','<p><b>How\ndo I change or cancel my order?</b></p><p>In\nthe event you wish to cancel your order, please contact us within 12 hours upon\nconfirmation of order at {{store_email_address}}. Please be advised that any\ncancellations after 12 hours upon approval of order will no longer be allowed\nand will not be entertain.</p><p><i>*Please\nnote that any orders that have already been packed or shipped cannot be\ncanceled.</i></p><p><b>What payment methods do you accept?</b></p><p>We\naccept all major credit cards (VISA, Mastercard, AMEX) and PayPal payments. We\ndo not accept personal checks, money orders, direct bank transfers, debit card\npayments, or cash on delivery.</p><p><b>Can I change my shipping address after placing an order?</b></p><div>If you change your shipping address, please\ncontact us at {{store_email_address}} within 12 hours after placing. Customer\nservice staffs will check your order and confirm asap.</div><p>Please\nbe advised that your shipping address cannot be revised after the order has\nbeen processed or shipped. Kindly update your shipping address to your\nresidential address instead of your vocational address as we do not know how\nlong the destination\'s customs\' department will have the package on hold.</p><p><b>When will I get my tracking number?</b></p><div>Once the order has been processed, a tracking\nnumber usually takes 1-3 business days to be generated. Please take note to\nallow 1 to 3 business days for your tracking information to be updated. If you\nhave not received your tracking number within three business days or if the\ntracking status is \"not available\" within 1 to 2 business days from\nthe time you have received your tracking number, kindly send us an email at {{store_email_address}}</div><p><b>How do I track my order?</b></p><div>You\ncan just click on Track Your Order on top of {{store_name}} page, enter your tracking\nnumber to check your order status.</div><p><b>How long does delivery take?</b></p><p>The\nprocessing time for orders is 1-3 business days. Once the shipment is already\nloaded on the plane, estimated delivery is 5-8 days for the United States, and\n7-10 days for other countries.</p><p>Please\ntake note that there are some unforeseen circumstances such as customs delays\nthat we are unable to control on our end as well as, some delays if there is an\nupcoming holiday season.</p><p><b>How do I return an item?</b></p><p>If\nyou are not happy with your purchase and wish to return an item, please contact\nus within 30 days from receiving your order. Please provide your order number\nas well as the reason for your return. Our customer service team will review\nthe return request and will send further instructions if the return is\napproved.</p><p>For\na list of final sale items, please see our Returns Policy. All returns must be\nin original condition with packaging intact.</p><p><b>Will I be charged with customs and taxes?</b></p><p>The\nprices displayed on our site are tax-free in US Dollars, which means you may be\nliable to pay for duties and taxes once you receive your order.</p><p>Import\ntaxes, duties, and related customs fees may be charged once your order arrives\nat its final destination, which is determined by your local customs office.</p><p>Payment\nof these charges and taxes are your responsibility and will not be covered by\nus. We are not responsible for delays caused by the customs department in your\ncountry. For further details of the charges, please contact your local customs\noffice.</p><p><b>I need my order fast, do you guys provide expedited shipping?</b></p><p>If\nyou want to receive your order early than the casual delivery time, please\ncontact us as soon as you place. We will change the shipping method for you.\nBut you will pay extra for the shipping service.</p><p><b>When will I receive my refund?</b></p><p>All\nrefunds will be credited to your original form of payment. If you paid by\ncredit or debit card, refunds will be sent to the card-issuing bank within 5-10\nbusiness days of receipt of the returned item or cancellation request. Please\ncontact the card-issuing bank with questions about when the credit will be\nposted to your account.</p><p>If you haven\'t received a\ncredit for your return yet, here\'s what to do: Contact the bank/credit card\ncompany. It may take some time before the refund to you.﻿﻿﻿﻿﻿﻿﻿﻿﻿﻿﻿</p>','[]',0,0,0,1563185661,1563185661),(2,NULL,'Payment Methods','Payment_Methods','<p><b>Payment Methods</b></p><p>Methods of payment available are based on\nthe country where the order is from and the total amount on the bill.</p><p><b>Credit/Debit card:</b></p><p>Your credit/debit card will automatically\nbe charged by our financial service provider. Your order will begin processing\nafter all necessary verifications have been completed.</p><p><b>PayPal:</b></p><div>The transaction will be processed by\nPayPal, an external financial service provider. During the checkout process,\nyou will be redirected to PayPal and pay with your account there. Your data\nwill not be transferred to {{store_name}}. After PayPal has completed verification,\nyour order will then be processed.</div><p><b>Currency:</b></p><p>When shopping on our website, payments\nwill be processed in USD. If your credit card company or bank uses a different\ncurrency, the final transaction price may differ due to currency exchange\nrates. Please contact your payment provider for further information.</p><div>*Payment/cash\non delivery (purchase order) isn’t possible at {{store_name}}.*﻿</div>','[]',0,0,0,1563185720,1563185720),(3,NULL,'Privacy Policy','Privacy_Policy','<p><b>Privacy Policy</b></p><p>SECTION\n1 - WHAT DO WE DO WITH YOUR INFORMATION?</p><div>When\nyou purchase something from {{store_name}}, as part of the buying and selling\nprocess, we collect the personal information you give us such as your name,\naddress and email address. Also, you will be required to provide {{store_name}} information regarding your credit card or another payment instrument. You\nrepresent and warrant to us that such information is correct and that you are\nauthorized to use the payment instrument.</div><p>When\nyou browse our store, we also automatically receive your computer’s internet\nprotocol (IP) address to provide us with information that helps us learn about\nyour browser and operating system.</p><p>Email\nmarketing (if applicable): With your permission, we may send you emails about\nour store, new products, and other updates.</p><p>SECTION\n2 - CONSENT</p><p>How\ndo you get my consent?</p><p>When\nyou provide us with personal information to complete a transaction, verify your\ncredit card, place an order, arrange for a delivery or return a purchase, we\nimply that you consent to our collecting it and using it for that specific\nreason only.</p><p>If\nwe ask for your personal information for a secondary reason, like marketing, we\nwill either ask you directly for your expressed consent or provide you with an\nopportunity to say no.</p><p>Our\nPolicy explains what information we collect on the Website, how we use or share\nthis information, and how we maintain such information. By using this Website,\nyou signify your acceptance of this Policy. If you do not agree to the terms of\nthis Policy, in whole or part, you should not use this Website. Please note\nthat this Policy applies only concerning the information collected on the\nWebsite and not any information received or obtained through other methods or\nsources.</p><p>How\ndo I withdraw my consent?</p><div>If\nafter you opt-in, you change your mind, you may withdraw your consent for us to\ncontact you, for the continued collection, use or disclosure of your\ninformation, at any time, by contacting us at {{store_email_address}}</div><p>SECTION\n3 - DISCLOSURE</p><p>We\nmay disclose your personal information if we are required by law to do so or if\nyou violate our Terms of Service.</p><p>SECTION\n4 - THIRD-PARTY SERVICES</p><p>In\ngeneral, the third-party providers used by us will only collect, use and\ndisclose your information to the extent necessary to allow them to perform the\nservices they provide to us.</p><p>However,\ncertain third-party service providers, such as payment gateways and other\npayment transaction processors, have their privacy policies concerning the\ninformation we are required to provide to them for your purchase-related\ntransactions.</p><p>For\nthese providers, we recommend that you read their privacy policies so you can\nunderstand the manner in which these providers will handle your personal\ninformation.</p><p>In\nparticular, remember that certain providers may be located in or have\nfacilities that are located in a different jurisdiction than either you or us.\nSo if you elect to proceed with a transaction that involves the services of a\nthird-party service provider, then your information may become subject to the\nlaws of the jurisdiction(s) in which that service provider or its facilities\nare located.</p><p>As\nan example, if you are located in Canada, and your transaction is processed by\na payment gateway located in the United States, then your personal information\nused in completing that transaction may be subject to disclosure under United\nStates legislation, including the Patriot Act.</p><p>Once\nyou leave our store’s website or are redirected to a third-party site or\napplication, you are no longer governed by this Privacy Policy or our website’s\nTerms of Service.</p><p>Links</p><p>When\nyou click on links on our store, they may direct you away from our site. We are\nnot responsible for the privacy practices of other sites and encourage you to\nread their privacy statements.</p><p>SECTION\n5- SECURITY</p><p>To\nprotect your personal information, we take reasonable precautions and follow\nindustry best practices to make sure it is not inappropriately lost, misused,\naccessed, disclosed, altered or destroyed.</p><p>If\nyou provide us with your credit card information, the information is encrypted\nusing secure socket layer technology (SSL) and stored with AES-256 encryption.\nAlthough no method of transmission over the Internet or electronic storage is\n100% safe, we follow all PCI-DSS requirements and implement additional\ngenerally accepted industry standards.</p><div>At {{store_name}}, we never share our customer’s data with the third party in any way. We\nuse the information that you provide for such purposes as responding to your\nrequests, customizing future shopping for you, improving our stores, and\ncommunicating with you. We always try to personalize and continually improve\nyour {{store_name}} shopping experience.</div><p>SECTION\n6 - COOKIES</p><p>We\nuse \"cookies\" technology to store data on your computer using the\nfunctionality of your browser. A lot of websites do this because cookies allow\nthe website publisher to do useful things like finding out whether the computer\n(and probably its user) has visited the site before. You can usually modify\nyour browser to prevent cookie use - but if you do this, the Service (and the\nWebsite) may not work correctly. The information stored in the cookie is used\nto identify you. This enables us to operate an efficient service and to track\nthe patterns of behavior of visitors to the website.</p><p>Also,\nin the course of serving advertisements to this Website (if any), third-party\nadvertisers or ad servers may place or recognize a unique cookie on your\nbrowser. The use of cookies by such third party advertisers or ad servers is\nnot subject to this Policy but is subject to their respective privacy policies.\n(Please note that use of the Website, is neither intended for nor directed to,\nchildren under the age of 18.)</p><p>SECTION\n7 - CHANGES TO THIS PRIVACY POLICY</p><p>We\nreserve the right to modify this privacy policy at any time, so please review\nit frequently. Changes and clarifications will take effect immediately upon\ntheir posting on the website. Depending on the nature of the change, we may\nannounce the change: (a) on the homepage of the Website, or (b) by email, if we\nhave your email address. However, in any event, by continuing to use the\nWebsite following any changes, you will be deemed to have agreed to such\nchanges. If you do not agree with the terms of this Policy, as it may be\namended from time to time, in whole or part, you must terminate your use of the\nWebsite.</p><p>QUESTIONS\nAND CONTACT INFORMATION</p><div>If\nyou would like to: access, correct, amend or delete any personal information we\nhave about you, register a complaint, or just want more information contact our\nPrivacy Compliance Officer at {{store_email_address}}</div><div><br></div><div>{{store_name}} Service\'s Proprietary Rights</div><div>Service\nContent, Software, and Trademarks: You are only authorized to use the {{store_name}} Service to engage in business transactions with {{store_name}}. You may not use any\nautomated technology to scrape, mine or gather any information from {{store_name}} Service or otherwise access the pages of {{store_name}} Service for any unauthorized\npurpose. If {{store_name}} Service blocks you from accessing {{store_name}} Service (including\nby blocking your IP address), you agree not to implement any measures to\ncircumvent such blocking (e.g., by masking your IP address or using a proxy IP\naddress). The technology and software underlying the {{store_name}} Service or\ndistributed in connection in addition to that are the property of {{store_name}}, our\naffiliates and our partners (the “Software”). You agree not to copy, modify,\ncreate a derivative work of, reverse engineer, reverse assemble or otherwise\nattempt to discover any source code, sell, assign, sublicense, or otherwise\ntransfer any right in the Software.</div><div><br></div><div>{{store_name}} Service may contain images, artwork, fonts and other content or features\n(“Service Content”) that are protected by intellectual property rights and\nlaws. Except as expressly authorized by {{store_name}}, you agree not to modify, copy,\nframe, rent, lease, loan, sell, distribute or create derivative works based on\nthe {{store_name}} Service or the Service Content, in whole or in part. Any use of the {{store_name}} Service or the Service Content other than as specifically authorized herein is\nstrictly prohibited. {{store_name}} reserves any rights not expressly granted herein.</div><div><br></div><div>The {{store_name}} name and logos are trademarks and service marks of {{store_name}} (collectively\nthe “{{store_name}} Trademarks”). Other company, product and service names, and logos\nused and displayed via the {{store_name}} Service may be trademarks or service marks of\ntheir respective owners who may or may not endorse or be affiliated with or\nconnected to {{store_name}}. </div><div><br></div><div>Nothing in these Terms of Service or the {{store_name}} Service\nshould be construed as granting any license or right to use any of {{store_name}} Trademarks displayed on the {{store_name}} Service, without our prior written\npermission in each instance. All goodwill generated from the use of {{store_name}} Trademarks will inure to {{store_name}}’s exclusive benefit.</div><div><br></div><div>Third\nParty Material: Under no circumstances will {{store_name}} be liable in any way for any\ncontent or materials of any third parties (including users), including, but not\nlimited to, for any errors or omissions in any content, or for any loss or\ndamage of any kind incurred as a result of the use of any such content or\nmaterials. To the maximum extent permitted under applicable law, the third\nparty providers of such content and materials are express and intended third\nparty beneficiaries of these Terms of Services with respect to their content\nand materials.</div><div><br></div><div>{{store_name}} may preserve content and may also disclose content if required to do so by law\nor in the good faith belief that such preservation or disclosure is reasonably\nnecessary to (a) comply with legal process, applicable laws or government\nrequests; (b) enforce these Terms of Service; (c) respond to claims that any\ncontent violates the rights of third parties; or (d) protect the rights,\nproperty, or personal safety of {{store_name}}, its users or the public.﻿﻿﻿﻿﻿﻿﻿﻿</div>','[]',0,0,0,1563185777,1563185777),(4,NULL,'Returns & Refund Policy','Returns_Refund_Policy','<p><b>RETURN POLICY</b></p><p>1) Not Happy with Your Order</p><p>If you are not happy with your purchase,\n30 days from date of that you received the product in like-new condition with\nno visible wear and tear, you (buyer) will be the one who is responsible for\npaying for the shipping costs for returning item if not covered by our warranty\nagainst manufacturer defects and exchange is not due to our error.</p><p>2) Damaged Items or Low-Quality</p><div>If the product is defective or does not\nwork properly, please kindly let us know. For the fastest resolution, please\ncontact us via {{store_email_address}} including a photograph demonstrating the poor\nquality or the damaged area of the item. The most optimal pictures are on a\nflat surface, with the tag and error clearly displayed. We’ll send you\nreplacements as soon as we confirmed the situation, no need to return the\ndefective ones. We will use this information to help you with your order, and\neliminate errors in the future.</div><p><b>CANCELLATION</b></p><p>1) Canceling Unshipped-out Orders</p><div>If you are canceling your order which has not\nyet to be shipped out, please kindly contact us via {{store_email_address}}. For the\nfastest resolution, please include your order number. Thanks!</div><p>2) Cancelling Shipped-out Order</p><p>If you are canceling orders when your\nparcel has already been shipped out or on its way to a destination, please\ncontact us and then kindly refuse to accept the parcel since we are not able to\ncall it back at that time. we will refund your payment after deducting shipping\ncosts. Refund will be issued as soon as parcel begins to return.</p><p><b>WARRANTY</b></p><p>This warranty only covers manufacturing\ndefects and does not cover:</p><p>·Damage caused by accident</p><p>·Improper care</p><p>·Normal wear and tear</p><p>·Break down of colors and materials due to\nsun exposure</p><p>·Aftermarket modifications</p><p>*Please Note: No returns/exchanges for\nproducts with water exposure will be accepted.</p><p><b>REFUND POLICY</b></p><p>If you feel that the product you’ve\npurchased does not meet the requirements you have in mind, then you do have the\noption to request a refund.</p><p>Below are the conditions under which\nrefund will be granted.</p><p>You can get a full refund if:</p><p>·If the product you purchased is\ncompletely non-functional.</p><p>·If you did not receive your product\nwithin 30 business days after the date you placed your order.</p><p>*Please Note: The refund will go back to\nyour account in 5-10 business days.</p><p><b>SHIPPING COSTS</b></p><p>You will be responsible for paying for\nyour own shipping costs for returning item. Shipping costs are non-refundable.\nIf you receive a refund, the cost of return shipping will be deducted from your\nrefund.</p><p>If you are shipping an item over $100, you\nshould consider using a trackable shipping service or purchasing shipping\ninsurance. Thank you!</p><p><b>DAMAGED/LOW-QUALITY ITEM</b></p><p>For the fastest resolution, please include\na photograph demonstrating the poor quality or the damaged area of the item.\nIdeally, the pictures should be on a flat surface, with the tag and error\nclearly displayed.</p><p>We will use this information to help you\nwith your order and to prevent repeated errors in the future.</p><div>If you\nhave other concerns and inquiries, kindly send a mail to {{store_email_address}}.﻿﻿</div>','[]',0,0,0,1563185836,1563185836),(5,NULL,'Shipping Policy','Shipping_Policy','<p><b>Countries We can\nShip: </b>We ship worldwide.</p><p>Delivery\nTime: When placing your order, we consider these factors when calculating the Estimated\nDelivery Date:</p><p>·<i>Order\nProcessing:</i> The amount of time\nit takes for us to prepare your order for shipping after your payment is\nauthorized and verified. This typically takes 1-3 business days.</p><p><i>*Note: Processing time for customized/personalized may take longer; it usually takes 5 to 10 business days.</i></p><p>·<i>Transit Time:</i> The amount of time it takes your order to leave our\nwarehouse and arrive at the local delivery carrier. It may take from 5-10\nbusiness days.</p><p>Estimated Shipping<b>:</b> Shipping\ncharges are estimated due to location and weight. The minimum shipping fee will\nbe $6.99.</p><p><i>*Please\nnote that these are estimated delivery times only.</i></p><p>Please\nensure all delivery information is correct. If there is incorrect or missing\ninformation, we may be required to contact you for the update on the delivery\ninformation, which can cause delays in delivering your order. Delays may also\noccur as a result of customs clearance.</p><p>Please fill in your\naddress in all details, otherwise, the package we mail to you will be returned\nto us, or we will just ignore your request to save everyone the trouble. ﻿﻿﻿</p>','[]',0,0,0,1563186036,1563186036),(6,NULL,'Terms of Service','Terms_of_Service','<p><b>OVERVIEW</b></p><div>{{store_name}} operates this website. Throughout the\nsite, the terms “we”, “us” and “our” refer to {{store_name}}. {{store_name}} offers\nthis website, including all information, tools, and services available from\nthis site to you, the user, conditioned upon your acceptance of all terms,\nconditions, policies, and notices stated here.</div><p>By\nvisiting our site or purchasing something from us, you engage in our “Service”\nand agree to be bound by the following terms and conditions (“Terms of Service”\n“Terms”), including those additional terms and conditions and policies\nreferenced herein or available by hyperlink. These Terms of Service apply to\nall users of the site, including without limitation users who are browsers,\nvendors, customers, merchants, and/ or contributors of content.</p><p>Please\nread these Terms of Service carefully before accessing or using our website. By\naccessing or using any part of the site, you agree to be bound by these Terms\nof Service. If you do not agree to all the terms and conditions of this agreement,\nthen you may not access the website or use any services. If these Terms of\nService are considered an offer, acceptance is expressly limited to these Terms\nof Service.</p><p>Any\nnew features or tools which are added to the current store shall also be subject\nto the Terms of Service. You can review the most current version of the Terms\nof Service at any time on this page. We reserve the right to update, change or\nreplace any part of these Terms of Service by posting updates or changes to our\nwebsite. It is your responsibility to check this page periodically for changes.\nYour continued use of or access to the site following the posting of any\nchanges constitutes acceptance of those changes.</p><p>Our\nstore is hosted on Shopify Inc. They provide us with an online e-commerce\nplatform that allows us to sell our products and services to you.</p><p><b>SECTION\n1 - ONLINE STORE TERMS</b></p><p>By\nagreeing to these Terms of Service, you represent that you are at least the age\nof majority in your state or province of residence, or that you are the age of\nmajority in your state or province of residence, and you have given us your\nconsent to allow any of your minor dependents to use this site.</p><p>You\nmay not use our products for any illegal or unauthorized purpose nor may you,\nin the use of the Service, violate any laws in your jurisdiction (including but\nnot limited to copyright laws).</p><p>You\nmust not transmit any worms or viruses or any code of a destructive nature.</p><p>A\nbreach or violation of any of the Terms will result in immediate termination of\nyour Services.</p><p><b>SECTION\n2 - GENERAL CONDITIONS</b></p><p>We\nreserve the right to refuse service to anyone for any reason at any time.</p><p>You\nunderstand that your content (not including credit card information), may be\ntransferred unencrypted and involve (a) transmissions over various networks;\nand (b) changes to conform and adapt to technical requirements of connecting\nsystems or devices. Credit card information is always encrypted during transfer\nover networks.</p><p>You\nagree not to reproduce, duplicate, copy, sell, resell or exploit any portion of\nthe Service, use of the Service, or access to the Service or any contact on the\nwebsite through which the service is provided, without express wrote permission\nby us.</p><p>The\nheadings used in this Agreement are included for convenience only and will not\nlimit or otherwise affect these Terms.</p><p><b>SECTION\n3 - ACCURACY, COMPLETENESS, AND TIMELINESS OF INFORMATION</b></p><p>We\nare not responsible if information made available on this site is not accurate,\ncomplete or current. The material on this site is provided for general\ninformation only and should not be relied upon or used as the sole basis for\nmaking decisions without consulting primary, more accurate, more complete or\nmore timely sources of information. Any reliance on the material on this site\nis at your own risk.</p><p>This\nsite may contain specific historical information. Historical information,\nnecessarily, is not current and is provided for your reference only. We reserve\nthe right to modify the contents of this site at any time, but we have no\nobligation to update any information on our website. You agree that it is your\nresponsibility to monitor changes to our site.</p><p><b>SECTION\n4 - MODIFICATIONS TO THE SERVICE AND PRICES</b></p><p>Prices\nfor our products are subject to change without notice.</p><p>We\nreserve the right at any time to modify or discontinue the Service (or any part\nor content thereof) without notice at any time.</p><p>We\nshall not be liable to you or any third-party for any modification, price\nchange, suspension or discontinuance of the Service.</p><p>SECTION\n5 - PRODUCTS OR SERVICES (if applicable)</p><p>Certain\nproducts or services may be available exclusively online through the website.\nThese products or services may have limited quantities and are subject to return\nor exchange only according to our Return Policy.</p><p>We\nhave made every effort to display as accurately as possible the colors and\nimages of our products that appear at the store. We cannot guarantee that your\ncomputer monitor\'s display of any color will be accurate.</p><p>We\nreserve the right but are not obligated, to limit the sales of our products or\nServices to any person, geographic region or jurisdiction. We may exercise this\nright on a case-by-case basis. We reserve the right to limit the quantities of\nany products or services that we offer. All descriptions of products or product\npricing are subject to change at any time without notice, at the sole\ndiscretion of us. We reserve the right to discontinue any product at any time.\nAny offer for any product or service made on this site is void where\nprohibited.</p><p>We\ndo not warrant that the quality of any products, services, information, or\nother material purchased or obtained by you will meet your expectations, or\nthat any errors in the Service will be corrected.</p><p><b>SECTION\n6 - ACCURACY OF BILLING AND ACCOUNT INFORMATION</b></p><p>We\nreserve the right to refuse any order you place with us. We may, in our sole\ndiscretion, limit or cancel quantities purchased per person, per household or\neach order. These restrictions may include orders placed by or under the same\ncustomer account, the same credit card, or orders that use the equal billing or\nshipping address. If we make a change to or cancel an order, we may attempt to\nnotify you by contacting the e-mail or billing address/phone number provided at\nthe time the order was made. We reserve the right to limit or prohibit orders\nthat, in our sole judgment, appear to be placed by dealers, resellers or\ndistributors.</p><p>You\nagree to provide current, complete and accurate purchase and account information\nfor all purchases made at our store. You agree to promptly update your account\nand other information, including your email address and credit card numbers and\nexpiration dates so that we can complete your transactions and contact you as\nneeded.</p><p>For\nmore details, please review our Returns Policy.</p><p><b>SECTION\n7 - OPTIONAL TOOLS</b></p><p>We\nmay provide you with access to third-party tools over which we neither monitor\nnor have any control nor input.</p><p>You\nacknowledge and agree that we provide access to such tools ”as is” and “as\navailable” without any warranties, representations or conditions of any kind\nand any endorsement. We shall have no liability whatsoever arising from or\nrelating to your use of optional third-party tools.</p><p>Any\nuse by you of optional tools offered through the site is entirely at your own\nrisk and discretion, and you should ensure that you are familiar with and\napprove of the terms on which tools are provided by the relevant third-party\nprovider(s).</p><p>We\nmay also, in the future, offer new services or features through the website\n(including, the release of new tools and resources). Such new features or\nservices shall also be subject to these Terms of Service.</p><p><b>SECTION\n8 - THIRD-PARTY LINKS</b></p><p>Certain\ncontent, products, and services available via our Service may include material\nfrom third-parties.</p><p>Third-party\nlinks on this site may direct you to third-party websites that are not\naffiliated with us. We are not responsible for examining or evaluating the\ncontent or accuracy, and we do not warrant and will not have any liability or\nresponsibility for any third-party materials or websites, or for any other\nmaterials, products, or services of third-parties.</p><p>We\nare not liable for any harm or damages related to the purchase or use of goods,\nservices, resources, content, or any other transactions made in connection with\nany third-party websites. Please review the third-party\'s policies and\npractices carefully and make sure you understand them before you engage in any\ntransaction. Complaints, claims, concerns, or questions regarding third-party\nproducts should be directed to the third-party.</p><p><b>SECTION\n9 - USER COMMENTS, FEEDBACK, AND OTHER SUBMISSIONS</b></p><p>If,\nat our request, you send certain specific submissions (for example contest\nentries) or without a request from us you send creative ideas, suggestions,\nproposals, plans, or other materials, whether online, by email, by postal mail,\nor otherwise (collectively, \'comments\'), you agree that we may, at any time,\nwithout restriction, edit, copy, publish, distribute, translate and otherwise\nuse in any medium any comments that you forward to us. We are and shall be\nunder no obligation (1) to maintain any comments in confidence; (2) to pay\ncompensation for any comments; or (3) to respond to any comments.</p><p>We\nmay, but have no obligation to, monitor, edit or remove content that we\ndetermine in our sole discretion are unlawful, offensive, threatening,\nlibelous, defamatory, pornographic, obscene or otherwise objectionable or\nviolates any party’s intellectual property or these Terms of Service.</p><p>You\nagree that your comments will not violate any right of any third-party,\nincluding copyright, trademark, privacy, personality or other personal or\nproprietary right. You further agree that your comments will not contain\nlibelous or otherwise unlawful, abusive or obscene material, or contain any\ncomputer virus or other malware that could in any way affect the operation of\nthe Service or any related website. You may not use a false e-mail address,\npretend to be someone other than yourself, or otherwise mislead us or\nthird-parties as to the origin of any comments. You are solely responsible for\nany comments you make and their accuracy. We take no responsibility and assume\nno liability for any comments posted by you or any third-party.</p><p><b>SECTION\n10 - PERSONAL INFORMATION</b></p><p>Our\nPrivacy Policy governs your submission of personal information through the\nstore. To view our Privacy Policy.</p><p><b>SECTION\n11 - ERRORS, INACCURACIES, AND OMISSIONS</b></p><p>Occasionally\nthere may be information on our site or in the Service that contains\ntypographical errors, inaccuracies or omissions that may relate to product\ndescriptions, pricing, promotions, offers, product shipping charges, transit\ntimes and availability. We reserve the right to correct any errors,\ninaccuracies or omissions, and to change or update information or cancel orders\nif any information in the Service or on any related website is inaccurate at\nany time without prior notice (including after you have submitted your order).</p><p>We\nundertake no obligation to update, amend or clarify information in the Service\nor on any related website, including without limitation, pricing information,\nexcept as required by law. No specified update or refresh date applied in the\nService or on any relevant site should be taken to indicate that all\ninformation in the Service or on any related website has been modified or\nupdated.</p><p><b>SECTION\n12 - PROHIBITED USES</b></p><p>In\naddition to other prohibitions as set forth in terms of Service, you are\nprohibited from using the site or its content: (a) for any unlawful purpose;\n(b) to solicit others to perform or participate in any unlawful acts; (c) to\nviolate any international, federal, provincial or state regulations, rules,\nlaws, or local ordinances; (d) to infringe upon or violate our intellectual\nproperty rights or the intellectual property rights of others; (e) to harass,\nabuse, insult, harm, defame, slander, disparage, intimidate, or discriminate\nbased on gender, sexual orientation, religion, ethnicity, race, age, national\norigin, or disability; (f) to submit false or misleading information; (g) to\nupload or transmit viruses or any other type of malicious code that will or may\nbe used in any way that will affect the functionality or operation of the\nService or of any related website, other websites, or the Internet; (h) to\ncollect or track the personal information of others; (i) to spam, phish, pharm,\npretext, spider, crawl, or scrape; (j) for any obscene or immoral purpose; or\n(k) to interfere with or circumvent the security features of the Service or any\nrelated website, other websites, or the Internet. We reserve the right to\nterminate your use of the Service or any related website for violating any of\nthe prohibited uses.</p><p><b>SECTION\n13 - DISCLAIMER OF WARRANTIES; LIMITATION OF LIABILITY</b></p><p>We\ndo not guarantee, represent or warrant that your use of our service will be\nuninterrupted, timely, secure or error-free.</p><p>We\ndo not warrant that the results that may be obtained from the use of the\nservice will be accurate or reliable.</p><p>You\nagree that from time to time we may remove the service for indefinite periods\nof time or cancel the service at any time, without notice to you.</p><p>You\nexpressly agree that your use of, or inability to use, the service is at your\nsole risk. The service and all products and services delivered to you through\nthe service are (except as expressly stated by us) provided \'as is\' and \'as\navailable\' for your use, without any representations, warranties or conditions\nof any kind, either express or implied, including all implied warranties or\nconditions of merchantability, merchantable quality, fitness for a particular\npurpose, durability, title, and non-infringement.</p><div>In\nno case shall {{store_site}}, our directors, officers, employees, affiliates,\nagents, contractors, interns, suppliers, service providers or licensors are\nliable for any injury, loss, claim, or any direct, indirect, incidental,\npunitive, special, or consequential damages of any kind, including, without\nlimitation lost profits, lost revenue, lost savings, loss of data, replacement\ncosts, or any similar damages, whether based in contract, tort (including\nnegligence), strict liability or otherwise, arising from your use of any of the\nservice or any products procured using the service, or for any other claim\nrelated in any way to your use of the service or any product, including, but\nnot limited to, any errors or omissions in any content, or any loss or damage\nof any kind incurred as a result of the use of the service or any content (or\nproduct) posted, transmitted, or otherwise made available via the service, even\nif advised of their possibility. Because of some states or jurisdictions do not\nallow the exclusion or the limitation of liability for consequential or\nincidental damages, in such states or jurisdictions, our liability shall be\nlimited to the maximum extent permitted by law.</div><p><b>SECTION\n14 - INDEMNIFICATION</b></p><div>You\nagree to indemnify, defend and hold harmless {{store_site}} and our parent,\nsubsidiaries, affiliates, partners, officers, directors, agents, contractors,\nlicensors, service providers, subcontractors, suppliers, interns and employees,\nharmless from any claim or demand, including reasonable attorneys’ fees, made\nby any third-party due to or arising out of your breach of these Terms of\nService or the documents they incorporate by reference or your violation of any\nlaw or the rights of a third-party.</div><p><b>SECTION\n15 - SEVERABILITY</b></p><p>In\nthe event that any provision of these Terms of Service is determined to be\nunlawful, void or unenforceable, such provision shall nonetheless be\nenforceable to the fullest extent permitted by applicable law, and the\nunenforceable portion shall be deemed to be severed from these Terms of\nService, such determination shall not affect the validity and enforceability of\nany other remaining provisions.</p><p><b>SECTION\n16 - TERMINATION</b></p><p>The\nobligations and liabilities of the parties incurred prior to the termination\ndate shall survive the termination of this agreement for all purposes.</p><p>These\nTerms of Service are effective unless and until terminated by either you or us.\nYou may terminate these Terms of Service at any time by notifying us that you\nno longer wish to use our Services, or when you cease using our site.</p><p>If\nin our sole judgment you fail, or we suspect that you have failed, to comply\nwith any term or provision of these Terms of Service, we also may terminate\nthis agreement at any time without notice and you will remain liable for all\namounts due up to and including the date of termination; and/or accordingly may\ndeny you access to our Services (or any part thereof).</p><p><b>SECTION\n17 - ENTIRE AGREEMENT</b></p><p>The\nfailure of us to exercise or enforce any right or provision of these Terms of\nService shall not constitute a waiver of such right or provision.</p><p>These\nTerms of Service and any policies or operating rules posted by us on this site\nor in respect to The Service constitutes the entire agreement and understanding\nbetween you and us and govern your use of the Service, superseding any prior or\ncontemporaneous agreements, communications and proposals, whether oral or\nwritten, between you and us (including, but not limited to, any prior versions\nof the Terms of Service).</p><p>Any\nambiguities in the interpretation of these Terms of Service shall not be\nconstrued against the drafting party.</p><p><b>SECTION\n18 - CHANGES TO TERMS OF SERVICE</b></p><p>You\ncan review the most current version of the Terms of Service at any time at this\npage.</p><p>We\nreserve the right, at our sole discretion, to update, change or replace any\npart of these Terms of Service by posting updates and changes to our website.\nIt is your responsibility to check our website periodically for changes. Your\ncontinued use of or access to our website or the Service following the posting\nof any changes to these Terms of Service constitutes acceptance of those\nchanges.</p><p><b>SECTION\n19 - CONTACT INFORMATION</b></p><div>Questions about the Terms\nof Service should be sent to us at {{store_email_address}}.﻿﻿</div>','[]',0,0,0,1563186075,1563186075);
/*!40000 ALTER TABLE `osc_page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_permission_masks`
--

DROP TABLE IF EXISTS `osc_permission_masks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_permission_masks` (
  `perm_mask_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `permission_data` text NOT NULL,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`perm_mask_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_permission_masks`
--

LOCK TABLES `osc_permission_masks` WRITE;
/*!40000 ALTER TABLE `osc_permission_masks` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_permission_masks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_personalized_design`
--

DROP TABLE IF EXISTS `osc_personalized_design`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_personalized_design` (
  `design_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ukey` varchar(27) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `design_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`design_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_personalized_design`
--

LOCK TABLES `osc_personalized_design` WRITE;
/*!40000 ALTER TABLE `osc_personalized_design` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_personalized_design` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_post_office_email`
--

DROP TABLE IF EXISTS `osc_post_office_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_post_office_email` (
  `email_id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(27) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receiver_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receiver_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `html_content` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_content` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opens` int(11) NOT NULL DEFAULT 0,
  `clicks` int(11) NOT NULL DEFAULT 0,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`email_id`),
  UNIQUE KEY `token_UNIQUE` (`token`),
  UNIQUE KEY `email_key_UNIQUE` (`email_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_post_office_email`
--

LOCK TABLES `osc_post_office_email` WRITE;
/*!40000 ALTER TABLE `osc_post_office_email` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_post_office_email` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_post_office_email_queue`
--

DROP TABLE IF EXISTS `osc_post_office_email_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_post_office_email_queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(27) NOT NULL,
  `email_key` varchar(100) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT 0,
  `state` enum('queue','sending','sent','error') NOT NULL DEFAULT 'queue',
  `error_message` varchar(255) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `sender_name` varchar(255) NOT NULL,
  `sender_email` varchar(255) NOT NULL,
  `receiver_name` varchar(255) NOT NULL,
  `receiver_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `html_content` mediumtext DEFAULT NULL,
  `text_content` mediumtext DEFAULT NULL,
  `attachments` text DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  `running_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`queue_id`),
  UNIQUE KEY `token_UNIQUE` (`token`),
  UNIQUE KEY `email_key_UNIQUE` (`email_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_post_office_email_queue`
--

LOCK TABLES `osc_post_office_email_queue` WRITE;
/*!40000 ALTER TABLE `osc_post_office_email_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_post_office_email_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_post_office_email_tracking`
--

DROP TABLE IF EXISTS `osc_post_office_email_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_post_office_email_tracking` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `email_id` int(11) NOT NULL,
  `event` enum('open','click') NOT NULL,
  `referer` varchar(255) DEFAULT NULL,
  `event_data` varchar(255) DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  KEY `email_id` (`email_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_post_office_email_tracking`
--

LOCK TABLES `osc_post_office_email_tracking` WRITE;
/*!40000 ALTER TABLE `osc_post_office_email_tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_post_office_email_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_post_office_subscriber`
--

DROP TABLE IF EXISTS `osc_post_office_subscriber`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_post_office_subscriber` (
  `subscriber_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(27) COLLATE utf8mb4_unicode_ci NOT NULL,
  `newsletter` tinyint(1) NOT NULL DEFAULT 1,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`subscriber_id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `token_UNIQUE` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_post_office_subscriber`
--

LOCK TABLES `osc_post_office_subscriber` WRITE;
/*!40000 ALTER TABLE `osc_post_office_subscriber` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_post_office_subscriber` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_report_record`
--

DROP TABLE IF EXISTS `osc_report_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_report_record` (
  `record_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `report_key` varchar(255) NOT NULL,
  `extra_key_1` varchar(255) DEFAULT NULL,
  `extra_key_2` varchar(255) DEFAULT NULL,
  `extra_key_3` varchar(255) DEFAULT NULL,
  `report_value` int(11) NOT NULL,
  `ab_test` text DEFAULT NULL,
  `client_ip` varchar(15) DEFAULT NULL,
  `country_code` varchar(2) DEFAULT NULL,
  `referer_url` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referer_host` varchar(255) DEFAULT NULL,
  `device_type` varchar(100) DEFAULT NULL,
  `device_identifier` varchar(255) DEFAULT NULL,
  `browser_name` varchar(255) DEFAULT NULL,
  `browser_version` varchar(255) DEFAULT NULL,
  `os_name` varchar(100) DEFAULT NULL,
  `os_version` varchar(100) DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  KEY `report_key` (`report_key`,`added_timestamp`),
  KEY `country_code` (`report_key`,`country_code`,`added_timestamp`),
  KEY `referer_host` (`report_key`,`referer_host`,`added_timestamp`),
  KEY `device_type` (`report_key`,`device_type`,`added_timestamp`),
  KEY `device_identifier` (`report_key`,`device_identifier`,`added_timestamp`),
  KEY `browser_name` (`report_key`,`browser_name`,`added_timestamp`),
  KEY `browser_version` (`report_key`,`browser_name`,`browser_version`,`added_timestamp`),
  KEY `os_name` (`report_key`,`os_name`,`added_timestamp`),
  KEY `os_version` (`report_key`,`os_name`,`os_version`,`added_timestamp`),
  KEY `extra_key_1` (`report_key`,`extra_key_1`),
  KEY `extra_key_2` (`report_key`,`extra_key_2`),
  KEY `extra_key_3` (`report_key`,`extra_key_3`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_report_record`
--

LOCK TABLES `osc_report_record` WRITE;
/*!40000 ALTER TABLE `osc_report_record` DISABLE KEYS */;
INSERT INTO `osc_report_record` VALUES (1,'unique_visitor',NULL,NULL,NULL,1,'frontend_tpl2:dls','27.79.223.200','vn','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36','','desktop','','Chrome','77.0.3865.90','Windows','10.0',1569486307),(2,'visit',NULL,NULL,NULL,1,'frontend_tpl2:dls','27.79.223.200','vn','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36','','desktop','','Chrome','77.0.3865.90','Windows','10.0',1569486307),(3,'new_visitor',NULL,NULL,NULL,1,'frontend_tpl2:dls','27.79.223.200','vn','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36','','desktop','','Chrome','77.0.3865.90','Windows','10.0',1569486307),(4,'pageview',NULL,NULL,NULL,1,'frontend_tpl2:dls','27.79.223.200','vn','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36','','desktop','','Chrome','77.0.3865.90','Windows','10.0',1569486307),(5,'pageview',NULL,NULL,NULL,1,'frontend_tpl2:dls','27.79.223.200','vn','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36','','desktop','','Chrome','77.0.3865.90','Windows','10.0',1569487612),(6,'pageview',NULL,NULL,NULL,1,'frontend_tpl2:dls','27.79.223.200','vn','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36','','desktop','','Chrome','77.0.3865.90','Windows','10.0',1569487691),(7,'unique_visitor',NULL,NULL,NULL,1,'frontend_tpl2:default2','1.52.173.212','vn','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36','','desktop','','Chrome','76.0.3809.132','Windows','10.0',1569488473),(8,'visit',NULL,NULL,NULL,1,'frontend_tpl2:default2','1.52.173.212','vn','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36','','desktop','','Chrome','76.0.3809.132','Windows','10.0',1569488473),(9,'new_visitor',NULL,NULL,NULL,1,'frontend_tpl2:default2','1.52.173.212','vn','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36','','desktop','','Chrome','76.0.3809.132','Windows','10.0',1569488473),(10,'pageview',NULL,NULL,NULL,1,'frontend_tpl2:default2','1.52.173.212','vn','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36','','desktop','','Chrome','76.0.3809.132','Windows','10.0',1569488473);
/*!40000 ALTER TABLE `osc_report_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_shopify_migrate_product_map`
--

DROP TABLE IF EXISTS `osc_shopify_migrate_product_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_shopify_migrate_product_map` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `shopify_handle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`record_id`),
  KEY `shopify_handle` (`shopify_handle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_shopify_migrate_product_map`
--

LOCK TABLES `osc_shopify_migrate_product_map` WRITE;
/*!40000 ALTER TABLE `osc_shopify_migrate_product_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `osc_shopify_migrate_product_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_tracking`
--

DROP TABLE IF EXISTS `osc_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_tracking` (
  `track_id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(27) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_info` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `unique_timestamp` int(10) NOT NULL DEFAULT 0,
  `visit_timestamp` int(11) NOT NULL DEFAULT 0,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`track_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_tracking`
--

LOCK TABLES `osc_tracking` WRITE;
/*!40000 ALTER TABLE `osc_tracking` DISABLE KEYS */;
INSERT INTO `osc_tracking` VALUES (1,'5d8c75e2f39559OERVP15857689','{\"ip\":\"27.79.223.200\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/77.0.3865.90 Safari\\/537.36\",\"referer\":\"\",\"os\":\"Windows 10\",\"browser\":\"Chrome Dev 77.0.3865.90\",\"location\":{\"city\":\"\",\"region\":\"\",\"region_code\":\"\",\"country_code\":\"VN\",\"country_name\":\"Vietnam\",\"latitude\":\"16\",\"longitude\":\"106\"}}',1569486307,1569486307,1569486306,1569487691),(2,'5d8c7e59bc5756ACX2N88405489','{\"ip\":\"1.52.173.212\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/76.0.3809.132 Safari\\/537.36\",\"referer\":\"\",\"os\":\"Windows 10\",\"browser\":\"Chrome Dev 76.0.3809.132\",\"location\":{\"city\":\"\",\"region\":\"\",\"region_code\":\"\",\"country_code\":\"VN\",\"country_name\":\"Vietnam\",\"latitude\":\"16\",\"longitude\":\"106\"}}',1569488473,1569488473,1569488473,1569488473);
/*!40000 ALTER TABLE `osc_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osc_tracking_footprint`
--

DROP TABLE IF EXISTS `osc_tracking_footprint`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osc_tracking_footprint` (
  `footprint_id` int(11) NOT NULL AUTO_INCREMENT,
  `track_ukey` varchar(27) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(19) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`footprint_id`),
  KEY `track_ukey` (`track_ukey`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osc_tracking_footprint`
--

LOCK TABLES `osc_tracking_footprint` WRITE;
/*!40000 ALTER TABLE `osc_tracking_footprint` DISABLE KEYS */;
INSERT INTO `osc_tracking_footprint` VALUES (1,'5d8c75e2f39559OERVP15857689','https://authyshop.com/','27.79.223.200','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36','',1569486307),(2,'5d8c75e2f39559OERVP15857689','https://authyshop.com/','27.79.223.200','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36','',1569487612),(3,'5d8c75e2f39559OERVP15857689','https://authyshop.com/','27.79.223.200','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36','',1569487691),(4,'5d8c7e59bc5756ACX2N88405489','https://authyshop.com/','1.52.173.212','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36','',1569488473);
/*!40000 ALTER TABLE `osc_tracking_footprint` ENABLE KEYS */;
UNLOCK TABLES;


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-26  9:03:32
