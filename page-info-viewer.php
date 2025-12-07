<?php
/**
 * Plugin Name: Page Info Viewer
 * Description: <?php PageInfoViewer(); ?> で現在のページ情報とカスタムフィールドを表示します。
* Version: 1.1
* Author: YukiUota
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// クラスファイルの読み込み
require_once plugin_dir_path( __FILE__ ) . 'includes/models/class-page-info-model.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/views/class-page-info-view.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/controllers/class-page-info-controller.php';

// コントローラーの初期化
function page_info_viewer_init() {
global $page_info_viewer_controller;
$page_info_viewer_controller = new Page_Info_Controller();
$page_info_viewer_controller->init();
}
add_action( 'plugins_loaded', 'page_info_viewer_init' );

/**
* メイン表示関数 (後方互換性のため維持)
*/
function PageInfoViewer() {
global $page_info_viewer_controller;
if ( isset( $page_info_viewer_controller ) ) {
$page_info_viewer_controller->display_page_info();
}
}

/**
* アセット取得関数 (後方互換性のため維持)
*/
function dev_get_loaded_assets( $args = array() ) {
// 簡易的にModelを直接使用
$model = new Page_Info_Model();
return $model->get_loaded_assets( $args );
}

/**
* アセット表示関数 (後方互換性のため維持)
*/
function dev_print_loaded_assets( $args = array() ) {
global $page_info_viewer_controller;
if ( isset( $page_info_viewer_controller ) ) {
$page_info_viewer_controller->display_assets_info( $args );
}
}