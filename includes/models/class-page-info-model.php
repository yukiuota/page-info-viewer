<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Page_Info_Model {
    /**
     * 投稿の基本情報を取得
     */
    public function get_page_info( $post ) {
        if ( ! $post ) return null;

        return [
            'ID' => $post->ID,
            'タイトル (get_the_title)' => get_the_title( $post->ID ),
            'スラッグ (post_name)' => $post->post_name,
            '公開状態 (post_status)' => $post->post_status,
            '投稿タイプ (post_type)' => $post->post_type,
            'テンプレート (get_page_template_slug)' => get_page_template_slug( $post->ID ) ?: '（デフォルト）',
            'パーマリンク (get_permalink)' => get_permalink( $post->ID ),
            '投稿日 (get_the_date)' => get_the_date( 'Y-m-d', $post->ID ),
            '更新日 (get_the_modified_date)' => get_the_modified_date( 'Y-m-d', $post->ID ),
            '抜粋 (get_the_excerpt)' => get_the_excerpt( $post->ID ),
        ];
    }

    /**
     * カスタムフィールドを取得
     */
    public function get_custom_fields( $post_id ) {
        return get_post_meta( $post_id );
    }

    /**
     * 読み込まれているアセット情報を取得
     */
    public function get_loaded_assets( $args = array() ) {
        $defaults = array(
            'include_plugins' => false,
            'only_theme'      => true,
        );
        $args = wp_parse_args( $args, $defaults );
    
        // --- PHP ファイル ---
        $included = get_included_files();
        $theme_dir = get_template_directory();
        $child_dir = is_child_theme() ? get_stylesheet_directory() : $theme_dir;
    
        $php_files = array();
        foreach ( $included as $file ) {
            $short = $file;
            
            // テーマ内のファイルのみを対象とする場合
            if ( $args['only_theme'] ) {
                // 子テーマのファイルをチェック
                if ( strpos( $file, $child_dir ) === 0 ) {
                    $short = str_replace( $child_dir, '[child-theme]', $file );
                    $php_files[] = $short;
                    continue;
                }
                // 親テーマのファイルをチェック
                if ( $theme_dir && strpos( $file, $theme_dir ) === 0 ) {
                    $short = str_replace( $theme_dir, '[theme]', $file );
                    $php_files[] = $short;
                    continue;
                }
                // テーマ外のファイルはスキップ
                continue;
            }
            
            // 全てのファイルを対象とする場合
            if ( strpos( $file, $child_dir ) === 0 ) {
                $short = str_replace( $child_dir, '[child-theme]', $file );
                $php_files[] = $short;
                continue;
            }
            if ( $theme_dir && strpos( $file, $theme_dir ) === 0 ) {
                $short = str_replace( $theme_dir, '[theme]', $file );
                $php_files[] = $short;
                continue;
            }
    
            if ( $args['include_plugins'] ) {
                $wp_plugin_dir = WP_PLUGIN_DIR;
                if ( strpos( $file, $wp_plugin_dir ) === 0 ) {
                    $short = str_replace( $wp_plugin_dir, '[plugin]', $file );
                    $php_files[] = $short;
                    continue;
                }
            }
    
            $php_files[] = $short;
        }
    
        // CSS / JS
        global $wp_styles, $wp_scripts;
        $css = array();
        $js  = array();
    
        if ( isset( $wp_styles ) && is_object( $wp_styles ) ) {
            foreach ( $wp_styles->queue as $handle ) {
                if ( empty( $wp_styles->registered[ $handle ] ) ) continue;
                $obj = $wp_styles->registered[ $handle ];
                $src = $obj->src;
    
                if ( $src && strpos( $src, 'http' ) !== 0 && strpos( $src, '//' ) !== 0 ) {
                    $src = $wp_styles->base_url . $src;
                }
                $src = set_url_scheme( $src );
    
                $css[] = array(
                    'handle' => $handle,
                    'src'    => $src,
                    'deps'   => $obj->deps,
                    'ver'    => $obj->ver,
                    'args'   => $obj->args,
                );
            }
        }
    
        if ( isset( $wp_scripts ) && is_object( $wp_scripts ) ) {
            foreach ( $wp_scripts->queue as $handle ) {
                if ( empty( $wp_scripts->registered[ $handle ] ) ) continue;
                $obj = $wp_scripts->registered[ $handle ];
                $src = $obj->src;
    
                if ( $src && strpos( $src, 'http' ) !== 0 && strpos( $src, '//' ) !== 0 ) {
                    $src = $wp_scripts->base_url . $src;
                }
                $src = set_url_scheme( $src );
    
                $js[] = array(
                    'handle'    => $handle,
                    'src'       => $src,
                    'deps'      => $obj->deps,
                    'ver'       => $obj->ver,
                    'in_footer' => $obj->extra && ! empty( $obj->extra['group'] ) && $obj->extra['group'] === 1,
                );
            }
        }
    
        return array(
            'php' => array_values( array_unique( $php_files ) ),
            'css' => $css,
            'js'  => $js,
        );
    }
}