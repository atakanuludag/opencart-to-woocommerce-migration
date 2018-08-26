<?php
set_time_limit(0);
include "config.php";

require __DIR__ .'/wc-api-php/vendor/autoload.php';
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;


$woocommerce = new Client(
    $wpUrl, 
    $token, 
    $tokenSecret,
    [
        'wp_api' => true,
        'version' => 'wc/v1',
        'timeout' => '90000000',
        //'query_string_auth' => true
    ]
);



$categoriID = array();


$query = $db->query("SELECT * FROM oc_category where parent_id = 0", PDO::FETCH_ASSOC);
if ( $query->rowCount() ){
    foreach( $query as $row ){
        $category_id = $row['category_id'];
        $parent_id = $row['parent_id'];


        $query2 = $db->query("SELECT * FROM oc_category_description WHERE category_id = ".$category_id, PDO::FETCH_ASSOC);
        if ( $query2->rowCount() ){
            foreach( $query2 as $row2 ){

                if($parent_id == 0){
                    array_push($categoriID, array(
                        "id" =>$category_id,
                        "name" => $row2['name'],
                        "description" => $row2['meta_description']
                    ));
                }
            }
        }
    }
}




$categoryCreate = array("create" => array());

foreach ($categoriID as $data) {
    array_push($categoryCreate["create"], array(
        "name" => $data["name"],
        "description" => $data["description"]
    )); 
}


try {
    $batch = $woocommerce->post('products/categories/batch', $categoryCreate);
} catch(HttpClientException $e) {
    echo $e->getMessage().'<br/>';
}



$parentBatchCreateIds = array();
foreach ($batch["create"] as $data) {
    echo $data["name"]." main category created.<br/>";
}