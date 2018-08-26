<?php
set_time_limit(0);
include "config.php";
include "../wp-load.php";

require __DIR__ .'/wc-api-php/vendor/autoload.php';
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;


$opencart_image_uri = 'https://www.opencartsitesi.com/image/';

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

$TXTRead = getCounter();
$count = (int)$_GET["count"];

if($count == (int)$TXTRead){
	die("die");
}


$query = $db->query("SELECT * FROM oc_product as product INNER JOIN oc_product_description as pdesc ON product.product_id = pdesc.product_id ORDER BY product.product_id ASC LIMIT " . $TXTRead . ", 1", PDO::FETCH_ASSOC);
if ( $query->rowCount() ){
    foreach( $query as $row ){
        $product_id = $row['product_id'];
        $sku = $row['model'];
        $quantity = (int)$row['quantity'];
        $thumbImage = $row['image'];
        $price = $row['price'];
        $manufacturer_id = $row['manufacturer_id']; //MARKA
        $date_added = $row['date_added'];
        $date_modified = $row['date_modified'];
        $name = $row['name'];
        $description = $row['description'];
        $meta_description = $row['meta_description'];
        $meta_keyword = $row['meta_keyword'];
        $tag = $row['tag'];

       
        $eklenme_tarihi = date("Y-m-d H:i:s", strtotime($date_added));
        $guncellenme_tarihi = date("Y-m-d H:i:s", strtotime($date_modified));
    }
}







$productSearchID = null;
$args = array(
    'post_type' => 'product',
    'meta_query' => array(
        array(
            'key' => 'opencart_id',
            'value' => (string)$product_id,
            'compare' => '=',
        )
    )
);
$query = new WP_Query( $args );
if ( $query->have_posts() ) {
	$query->the_post();
	$productSearchID = get_the_ID();
}
wp_reset_postdata();




if($productSearchID == null){

	# Marka #
	$query = $db->query("SELECT * FROM oc_manufacturer WHERE manufacturer_id = ".$manufacturer_id, PDO::FETCH_ASSOC);
	if ( $query->rowCount() ){
	    foreach( $query as $row ){
	        $manufacturerName = $row['name'];

	        if($manufacturerName){
		        $attributes = array(
					array(
						'id' => 1,
					    'position' => 0,
					    'visible' => true,
					    'variation' => false,
					    'options' => $manufacturerName
					)
				);
	        } else {
	        	$attributes = array();
	        }

	        

	    }
	}
	# Marka #


	# Resim #

	$images = array();

	if(!empty($thumbImage)){

		array_push($images, array(
			"src" => $opencart_image_uri.$thumbImage,
		    "position" => 0
		));

		


		$query = $db->query("SELECT * FROM oc_product_image WHERE product_id = ".$product_id, PDO::FETCH_ASSOC);
		if ( $query->rowCount() ){
			$i = 1;
		    foreach( $query as $row ){
		    	if(!empty($row['image'])){
					array_push($images, array(
						"src" => $opencart_image_uri.$row['image'],
						"position" => $i
					));
					$i++;
				}
		    }
		}
	}

	
	# Resim #


	# Kategori #
	$catName = array();
	$query = $db->query("SELECT * FROM oc_product_to_category WHERE product_id = ".$product_id, PDO::FETCH_ASSOC);
	if ( $query->rowCount() ){
	    foreach( $query as $row ){
	        $category_id = $row['category_id'];

	        $query2 = $db->query("SELECT * FROM oc_category_description WHERE category_id = ".$category_id, PDO::FETCH_ASSOC);
			if ( $query2->rowCount() ){
			    foreach( $query2 as $row2 ){

			        array_push($catName, $row2['name']);
			    }
			}
	    }
	}


	$catIds = array();
	foreach ($catName as $cat) {

		try {
			$s = $woocommerce->get('products/categories', ['search' => $cat]);
		} catch(HttpClientException $e) {}

		foreach ($s as $data) {

			if($data['name'] == $cat){
				array_push($catIds, array( "id" => $data['id']));
			}
		}
	}
	# Kategori #


	# Etiket #
	$tagsBatch = array("create" => array());
	$tagsIds = array();
	$tags = explode(",", $tag);

	foreach ($tags as $t) {
		$t = trim($t);

		try {
			$s = $woocommerce->get('products/tags', ['search' => $t]);
		} catch(HttpClientException $e) {}

		if(count($s) > 0){
			array_push($tagsIds, array(
				"id" => $s[0]['id']
			));
		} else {
			array_push($tagsBatch["create"], array(
				"name" => $t
			));
		}
	}

	if(count($tagsBatch["create"]) > 0){
		try {
			$tag_posts = $woocommerce->post('products/tags/batch', $tagsBatch);
			foreach ($tag_posts["create"] as $data) {
				array_push($tagsIds, array(
					"id" => $data["id"]
				));
			}
		} catch(HttpClientException $e) {}
	}


	# Etiket #






	$data = array(
		"name" => $name,
		"sku" => $sku,
		"manage_stock" => true,
		"stock_quantity" => $quantity,
		"description" => html_entity_decode($description),
		"short_description" => $meta_description,
		"regular_price" => $price,
		"tags" => $tagsIds,
		"categories" => $catIds,
		"attributes" => $attributes,
	);

	if(count($images) > 0){
		$data['images'] = $images;
	}



	if($quantity > 0){
		$data['in_stock'] = true;
	} else {
		$data['in_stock'] = false;
	}


	
	try {
		$post = $woocommerce->post('products', $data);
		$post_id = $post["id"];
		add_post_meta($post_id, 'opencart_id', $product_id, true);

		wp_update_post(
		    array (
		        'ID'            => $post_id,
		        'post_date'     => $eklenme_tarihi,
		        'post_modified' => $guncellenme_tarihi,
		        'post_date_gmt' => get_gmt_from_date( $eklenme_tarihi ),
		        'post_modified_gmt' => get_gmt_from_date( $guncellenme_tarihi )
		    )
		);

		echo $post["name"]." eklendi. <br/><strong>Eklenen:</strong> ".$TXTRead. " | <strong>Toplam</strong>: ".$count."<hr/>";
	} catch(HttpClientException $e) {
		die("die Ürün Ekleme Hatası: ".$e->getMessage());
	}

} else {
	echo "Zaten ekli.".$name."<br/>";
}
counter();