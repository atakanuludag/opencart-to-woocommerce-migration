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




$categoryID = array();

$query = $db->query("SELECT * FROM oc_category Where parent_id != 0", PDO::FETCH_ASSOC);
if ( $query->rowCount() ){
    foreach( $query as $row ){
        $category_id = $row['category_id'];
        $parent_id = $row['parent_id'];


        if($parent_id != 0){

            $query2 = $db->query("SELECT * FROM oc_category_description WHERE category_id = ".$category_id, PDO::FETCH_ASSOC);
            if ( $query2->rowCount() ){
                foreach( $query2 as $row2 ){


                    $query3 = $db->query("SELECT * FROM oc_category_description WHERE category_id = ".$parent_id, PDO::FETCH_ASSOC);
                    if ( $query3->rowCount() ){
                        foreach( $query3 as $row3 ){
                            array_push($categoryID, array(
                                "id" =>$category_id,
                                "parentID" => $parent_id,
                                "parentName" => $row3['name'],
                                "name" => $row2['name'],
                                "description" => $row2['meta_description']
                            ));
                        }
                    }

                    
                }
            }

        }



    }
}







$categories = $woocommerce->get('products/categories', array(
    "per_page" => "100",
));
$wp_categories = array();
foreach ($categories as $category) {
   array_push($wp_categories, array(
        "id" => $category['id'],
        "name" => $category['name']
   ));
}


$categoryCreate = array();

foreach ($categoryID as $data) {
  foreach ($wp_categories as $wp_category) {
    if($wp_category['name'] == $data["parentName"]){

        array_push($categoryCreate, array(
            "parent" => $wp_category["id"],
            "description" => $data["description"],
            "name" => $data["name"]
        )); 

    }
  }  
}



$newArrays = array_chunk($categoryCreate, 50);
foreach ($newArrays as $arr) {

    $batch = array("create" => $arr);

    try {
        $batch = $woocommerce->post('products/categories/batch', $batch);
        foreach ($batch["create"] as $data) {
            echo $data["name"]." sub category created.<br/>";
        }

        
    } catch(HttpClientException $e) {
        echo $e->getMessage().'<br/>';
    }

    print_r($batch);


}