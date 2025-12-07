<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Page_Info_Controller {
    private $model;
    private $view;

    public function __construct() {
        $this->model = new Page_Info_Model();
        $this->view = new Page_Info_View();
    }

    public function init() {
        add_shortcode( 'show_loaded_assets', array( $this, 'shortcode_assets' ) );
    }

    /**
     * メインのページ情報表示
     */
    public function display_page_info() {
        if ( ! current_user_can('manage_options') ) return; // 管理者のみ表示
        global $post;

        if ( ! $post ) {
            $this->view->render_error( '現在の投稿情報を取得できません。' );
            return;
        }

        // 基本情報とカスタムフィールドを取得
        $info = $this->model->get_page_info( $post );
        $meta = $this->model->get_custom_fields( $post->ID );
        
        // 表示
        $this->view->render_page_info( $info, $meta );
        
        // アセット情報も表示
        $this->display_assets_info();
    }

    /**
     * アセット情報の表示
     */
    public function display_assets_info( $args = array() ) {
        if ( ! current_user_can( 'edit_theme_options' ) ) return;
        
        $assets = $this->model->get_loaded_assets( $args );
        $this->view->render_assets_info( $assets );
    }

    /**
     * ショートコード用コールバック
     */
    public function shortcode_assets( $atts ) {
        $atts = shortcode_atts( array(
            'include_plugins' => '0',
            'only_theme'      => '1',
        ), $atts, 'show_loaded_assets' );

        if ( ! current_user_can( 'edit_theme_options' ) ) {
            return '';
        }

        ob_start();
        $this->display_assets_info( array(
            'include_plugins' => (bool) intval( $atts['include_plugins'] ),
            'only_theme'      => (bool) intval( $atts['only_theme'] ),
        ) );
        return ob_get_clean();
    }
}