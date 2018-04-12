<?php


add_hook('ShoppingCartValidateCheckout',1,function($vars){

    set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
    include_once 'modules/addons/clientverification/libs/ClientVerification.class.php';
    include_once 'modules/addons/clientverification/common.php';


    $cv = new ClientVerification();
    $result=$cv->mysqlQuery('SELECT client.uuid,gw.gw,user.is_verified FROM tblclients AS client 
                                LEFT JOIN mod_clientverification_engw AS gw ON gw.gw=%s
                                LEFT JOIN mod_clientverification_user AS user ON user.uuid=client.uuid
                            WHERE client.id=%s',
                                $vars['paymentmethod'],
                                $vars['userid']
        );
    $row=mysqli_fetch_assoc($result);
    if($row['gw'] && $row['is_verified'] === 'false'){
        return ["Your account must be verified before use this payment method.",
                "Please navigate to Account -> Account Verification for start verification process.",
                "OR",
                "Select different payment method."];
    }



});



add_hook('ClientAreaNavbars', 1, function ()
{
    $secondaryNavbar = Menu::secondaryNavbar();

    if (!is_null($secondaryNavbar->getChild('Account'))){

        $contactUsLink = $secondaryNavbar->getChild('Account');
        $contactUsLink->addChild('accver', array(
            'label' => 'Account Verification',
            'uri' => '/index.php?m=clientverification',
            'order' => 1,
            'icon' => '',
        ));

        $contactUsLink->moveToFront();
    }});