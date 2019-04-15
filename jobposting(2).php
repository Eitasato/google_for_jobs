<?php
/*
Plugin Name: jobposting_Markup
Description:google for jobs 対応プラグイン
Author:miyadeek
Author URI: http://eitasatou.com/
Version:1.1
*/

//wp_headにフックをかける

add_action('wp_head','insert_json_ld');

function insert_json_ld (){			//構造化データの出力開始
if (is_singular('introduce')) {
	if (have_posts()) : while (have_posts()) : the_post();

	//arrayで連想配列を作成し、json_encodeで出力
	//投稿日、を取得
	$postdate = get_the_date('Y-n-j');

	//descriptionを取得
	//$description = get_the_content();
	$description = apply_filters( 'the_content', get_the_content() );
	//javascriptタグ削除処理
	//区切り文字（開始）
	$delimiter_start = "var loaded";
	//区切り文字（終了）
	$delimiter_end = "</script>";
	//開始位置
	$start_position = strpos($description, $delimiter_start);
	//切り出す部分の長さ
	$length = strpos($description, $delimiter_end);
	//切り出し
	//print substr($description, $start_position, $length );
	$delete = substr($description, $start_position, $length );
	//置換（空白に置き換え）
	$description = str_replace($delete , '' , $description);

	//HTMLタグ削除処理
	//$description = str_replace( ']]>', ']]&gt;', $description );
	$description = strip_tags($description);

	//$description = str_replace('&nbsp;', " ", $description);
	$description = str_replace(array("\r\n","\r","\n"), "<br>", $description);

	//投稿titleを取得
	$job = get_the_title();

	//カスタムタクソノミー（県名）を取得
	$category4 = get_the_terms($post->ID,'category4');
	$cat4_name = $category4[0]->name;

	//カスタムタクソノミー（市名）を取得
	$tag = get_the_terms($post->ID,'introduce_tag');
	$tag_name = $tag[0]->name;

	//カスタムタクソノミー（会社名）を取得
	$company = get_the_terms($post->ID,'company');
	$c_name = $company[0]->name;
	$c_url = $company[0]->description;

	if (empty($company)){

		$json = array(
		'@context' => 'https://schema.org/',
		'@type' => 'JobPosting',
		'datePosted' =>  $postdate,
		'description' => $description,
		'title' => $job,
		'hiringOrganization' =>
		array(
			'@type' => 'Organization',
			'name' => '渕上ファインズ',
			'sameAs' => 'https://www.ffines.jp/',
		),
	'jobLocation' => array(
			'@type' => 'place',
			'address' =>
			array(
				'@type' => 'PostalAddress',
				//'streetAddress' => '',
				'addressLocality' => $tag_name,
				'addressRegion' => $cat4_name,
				'addressCountry' => 'JP',
			),
		),
	);

	} else{

			$json = array(
			'@context' => 'https://schema.org/',
			'@type' => 'JobPosting',
			'datePosted' =>  $postdate,
			'description' => $description,
			'title' => $job,
			'hiringOrganization' =>
			array(
				'@type' => 'Organization',
				'name' => $c_name,
				'sameAs' => $c_url,
			),
			'jobLocation' => array(
					'@type' => 'place',
					'address' =>
					array(
						'@type' => 'PostalAddress',
						//'streetAddress' => '',
						'addressLocality' => $tag_name,
						'addressRegion' => $cat4_name,
						'addressCountry' => 'JP',
					),
				),
			);
	}

	echo '<script type="application/ld+json"> '.stripslashes(json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)).'</script>';

	//ここまで

		endwhile;
		endif;
	}
}

//カスタムタクソノミーを追加
add_action('init', 'register_blog_cat_custom_post');
function register_blog_cat_custom_post() {
    register_taxonomy(
		'company',
		'introduce',
		array(
			'hierarchical' => true,
			'label' => '会社名',
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => true,
			'singular_label' => 'company'
		)
	);
}

?>
